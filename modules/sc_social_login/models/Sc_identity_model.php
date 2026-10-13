<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Sc_identity_model extends App_Model
{
    public function transaction($provider, $audience, $intent, $actor, $browser)
    {
        $ip = hash('sha256',$this->input->ip_address());
        $this->db->where('created_at <',time()-600)->delete(db_prefix().'scsl_transactions');
        if ($this->db->where('ip_hash',$ip)->where('created_at >',time()-600)
            ->count_all_results(db_prefix().'scsl_transactions') >= 20) { throw new RuntimeException('scsl_rate_limited'); }
        $tx=['state'=>bin2hex(random_bytes(32)), 'nonce'=>bin2hex(random_bytes(32)),
            'verifier'=>Sc_oauth::b64(random_bytes(48))];
        $this->db->insert(db_prefix().'scsl_transactions', [
            'state_hash'=>hash('sha256',$tx['state']), 'browser_hash'=>hash('sha256',$browser),
            'ip_hash'=>$ip, 'provider'=>$provider,'audience'=>$audience,'intent'=>$intent,
            'actor_id'=>$actor,'nonce'=>$tx['nonce'],'verifier'=>$tx['verifier'],'created_at'=>time()]);
        return $tx;
    }
    public function consume($state, $browser, $provider)
    {
        if (!is_string($state) || !preg_match('/^[a-f0-9]{64}$/D',$state)
            || !is_string($browser) || !preg_match('/^[a-f0-9]{64}$/D',$browser)) { throw new RuntimeException('scsl_failed'); }
        $table=db_prefix().'scsl_transactions';
        $this->db->trans_begin();
        $row=$this->db->query('SELECT * FROM `'.$table.'` WHERE state_hash=? AND browser_hash=? AND provider=? AND created_at>? FOR UPDATE',
            [hash('sha256',$state),hash('sha256',$browser),$provider,time()-600])->row_array();
        if (!$row) { $this->db->trans_rollback(); throw new RuntimeException('scsl_failed'); }
        $this->db->where('state_hash',$row['state_hash'])->delete($table);
        if (!$this->db->trans_status()) { $this->db->trans_rollback(); throw new RuntimeException('scsl_failed'); }
        $this->db->trans_commit();
        return $row;
    }
    public function account($audience, $id)
    {
        $table=$audience==='staff'?'staff':'contacts'; $field=$audience==='staff'?'staffid':'id';
        $user=$this->db->where($field,(int)$id)->get(db_prefix().$table)->row();
        if (!$user || !(int)$user->active) { return null; }
        if ($audience==='client') {
            $client=$this->db->where('userid',$user->userid)->get(db_prefix().'clients')->row();
            if (!$client || !(int)$client->active || (isset($client->registration_confirmed) && !(int)$client->registration_confirmed)) { return null; }
            if (is_email_verification_enabled() && empty($user->email_verified_at)) { return null; }
        }
        return $user;
    }
    public function identity($provider, $audience, $subject)
    {
        return $this->db->where(['provider'=>$provider,'audience'=>$audience,'subject_hash'=>$this->subjectHash($provider,$subject),'enabled'=>1])
            ->get(db_prefix().'scsl_identities')->row_array();
    }
    public function attach($provider, $audience, $subject, $id)
    {
        if (!in_array($audience,['staff','client'],true) || !in_array($provider,['google','facebook','apple'],true)
            || !is_string($subject) || $subject==='' || strlen($subject)>255 || $id<1) { return false; }
        // Unique keys protect against two requests linking the same provider identity to different accounts.
        $old=$this->db->where(['audience'=>$audience,'provider'=>$provider,'subject_hash'=>$this->subjectHash($provider,$subject)])
            ->get(db_prefix().'scsl_identities')->row_array();
        if ($old) { return (int)$old['user_id']===(int)$id && (int)$old['enabled']===1; }
        $owner=$this->db->where(['audience'=>$audience,'provider'=>$provider,'user_id'=>(int)$id])
            ->get(db_prefix().'scsl_identities')->row_array();
        if ($owner) { return false; }
        $debug=$this->db->db_debug; $this->db->db_debug=false;
        $ok=$this->db->insert(db_prefix().'scsl_identities',['audience'=>$audience,'provider'=>$provider,
            'subject_hash'=>$this->subjectHash($provider,$subject),'user_id'=>(int)$id,'enabled'=>1,'created_at'=>time()]);
        $this->db->db_debug=$debug;
        return $ok;
    }
    public function attachPending($audience, $id)
    {
        $p=$this->session->userdata('scsl_pending');
        if (is_array($p) && ($p['expires']??0)<=time()) { $this->session->unset_userdata('scsl_pending'); return false; }
        if (!is_array($p) || ($p['audience']??'')!==$audience
            || ($p['client_id']??'')!==get_option('scsl_'.($p['provider']??'').'_client_id')) { return false; }
        $user=$this->db->where($audience==='staff'?'staffid':'id',$id)
            ->get(db_prefix().($audience==='staff'?'staff':'contacts'))->row();
        $expectedActor=(int)($p['actor_id']??0);
        if (!$user || ($expectedActor>0 ? $expectedActor!==(int)$id : (empty($p['email']) || strcasecmp($p['email'],$user->email)!==0))
            || ($audience==='staff' && !scsl_can('create'))) { return false; }
        $this->session->unset_userdata('scsl_pending');
        $ok=$this->attach($p['provider'],$audience,$p['subject'],$id);
        set_alert($ok?'success':'danger',_l($ok?'scsl_connected':'scsl_conflict'));
        return $ok;
    }
    private function subjectHash($provider,$subject)
    {
        return hash('sha256',get_option('scsl_'.$provider.'_client_id')."\0".$subject);
    }
    public function login($audience, $id)
    {
        $user=$this->account($audience,$id);
        if (!$user) { throw new RuntimeException('scsl_failed'); }
        $this->load->model('authentication_model');
        $this->session->sess_regenerate(true);
        $this->session->unset_userdata(['staff_user_id','staff_logged_in','client_user_id','contact_user_id','client_logged_in',
            'tfa_staffid','tfa_remember','_two_factor_auth_established','_two_factor_auth_staff_email']);
        if ($audience==='staff' && (int)$user->two_factor_auth_enabled>0) {
            $this->session->set_userdata(['tfa_staffid'=>$id,'_two_factor_auth_established'=>true]);
            if ((int)$user->two_factor_auth_enabled===1) {
                $this->authentication_model->set_two_factor_auth_code($id);
                if (!send_mail_template('staff_two_factor_auth_key',$user)) { throw new RuntimeException('scsl_failed'); }
                $this->session->set_userdata('_two_factor_auth_staff_email',$user->email);
                return admin_url('authentication/two_factor');
            }
            return admin_url('authentication/two_factor/app');
        }
        hooks()->do_action($audience==='staff'?'before_staff_login':'before_client_login',
            ['email'=>$user->email,'userid'=>$audience==='staff'?$id:$user->userid,'contact_user_id'=>$id]);
        $this->session->set_userdata($audience==='staff'?['staff_user_id'=>$id,'staff_logged_in'=>true]:
            ['client_user_id'=>$user->userid,'contact_user_id'=>$id,'client_logged_in'=>true]);
        $this->session->set_userdata('auth_started_at',time());
        $this->db->where($audience==='staff'?'staffid':'id',$id)->update(db_prefix().($audience==='staff'?'staff':'contacts'),
            ['last_ip'=>$this->input->ip_address(),'last_login'=>date('Y-m-d H:i:s')]);
        $this->load->model('announcements_model');
        $this->announcements_model->set_announcements_as_read_except_last_one($id,$audience==='staff');
        hooks()->do_action($audience==='staff'?'after_staff_login':'after_contact_login');
        return $audience==='staff'?admin_url():site_url();
    }
    public function rows($audience, $id, $global=false, $before=0)
    {
        if (!$global) { $this->db->where(['audience'=>$audience,'user_id'=>$id]); }
        if ($before>0) { $this->db->where('id <',(int)$before); }
        return $this->db->select('id,audience,user_id,provider,enabled,created_at')->order_by('id','desc')
            ->limit(100)->get(db_prefix().'scsl_identities')->result_array();
    }
    public function remove($id, $audience, $actor, $global=false, $disable=false)
    {
        $candidate=$this->db->where('id',(int)$id)->get(db_prefix().'scsl_identities')->row_array();
        if (!$candidate || (!$global && ($candidate['audience']!==$audience || (int)$candidate['user_id']!==(int)$actor))) {
            throw new RuntimeException('scsl_denied');
        }
        $this->db->trans_begin();
        // Serialize all disconnects for the same account, even when they target different providers.
        $table=db_prefix().($candidate['audience']==='staff'?'staff':'contacts');
        $field=$candidate['audience']==='staff'?'staffid':'id';
        $owner=$this->db->query('SELECT * FROM `'.$table.'` WHERE `'.$field.'`=? FOR UPDATE',[(int)$candidate['user_id']])->row();
        $row=$this->db->query('SELECT * FROM `'.db_prefix().'scsl_identities` WHERE id=? FOR UPDATE',[(int)$id])->row_array();
        if (!$row || (!$global && ($row['audience']!==$audience || (int)$row['user_id']!==(int)$actor))) {
            $this->db->trans_rollback(); throw new RuntimeException('scsl_denied');
        }
        if ($owner && empty($owner->password)) {
            $others=$this->db->where(['audience'=>$row['audience'],'user_id'=>$row['user_id'],'enabled'=>1])
                ->where('id !=',(int)$id)->count_all_results(db_prefix().'scsl_identities');
            if (!$others) { $this->db->trans_rollback(); throw new RuntimeException('scsl_last_method'); }
        }
        $this->db->where('id',(int)$id);
        if ($disable) { $this->db->update(db_prefix().'scsl_identities',['enabled'=>0]); }
        else { $this->db->delete(db_prefix().'scsl_identities'); }
        if (!$this->db->trans_status()) { $this->db->trans_rollback(); throw new RuntimeException('scsl_change_failed'); }
        $this->db->trans_commit();
    }
}
