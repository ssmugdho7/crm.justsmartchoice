<?php


/**
 * Scanning received mails
 */

hooks()->add_action("after_cron_run", function (){


    $CI = &get_instance();

    $imap_servers = $CI->db->select('*')
                        ->from(db_prefix().'email_template_imap_settings')
                        ->where('status',1)
                        ->get()
                        ->result();


    if ( !empty( $imap_servers ) )
    {

        require_once module_dir_path( EMAIL_TEMPLATE_MANAGE_MODULE_NAME , 'third_party/php-imap/Imap_etm.php' );

        include_once APPPATH.'third_party/simple_html_dom.php';


        foreach ( $imap_servers as $imap_server )
        {

            $last_sequence_id = $imap_server->last_sequence_id;
            if ( empty( $last_sequence_id )  )
                $last_sequence_id = 1;

            //$last_sequence_id = total_rows( db_prefix()."email_template_manage_inbox" , [ 'imap_id' => $imap_server->id ] );

            $server = $imap_server->imap_server;
            $imap_port = $imap_server->imap_port;
            $encryption = $imap_server->encryption;
            $user_name = $imap_server->user_name;
            $password = $imap_server->password;
            $last_search_days = !empty( $imap_server->last_search_days ) ? $imap_server->last_search_days : 0 ;

            // open connection
            $imap = new Imap_etm($server, $user_name, $password, $encryption , $last_search_days);
            if ( false === $imap->isConnected() )
            {

                log_activity('Failed to connect to IMAP from email: '.$user_name );
                continue;

            }


            $imap->selectFolder('Inbox');

            $emails = $imap->getMessages( true , $last_sequence_id );


            if ( !empty( $emails ) )
            {

                $email_index = 0 ;

                foreach ( $emails as $email )
                {

                    $email_index++;

                    if ( $email_index > 199 )
                    {

                        echo "<br/> $last_sequence_id last_sequence_id  - $email_index - ".$email['uid'];
                        continue;

                    }

                    $mail_info = $CI->db->select('id')
                        ->from(db_prefix().'email_template_manage_inbox')
                        ->where('imap_id',$imap_server->id)
                        ->where('sequence_id',$email['uid'])
                        ->get()->row();

                    if ( !empty( $mail_info ) )
                    {

                        echo "<br/> Email already exist -   ".$email['uid'];
                        continue;

                    }

                    $plainTextBody = $imap->getPlainTextBody($email['uid']);

                    if ( !empty( $plainTextBody ) )
                        $plainTextBody = trim($plainTextBody);

                    if (!empty($plainTextBody))
                    {
                        $email['body'] = $plainTextBody;
                    }

                    $email['body']       = email_template_handle_google_drive_links_in_text($email['body']);
                    $email['body']       = email_template_prepare_imap_email_body_html($email['body']);

                    $data                = [];
                    $data['attachments'] = [];


                    if ( !empty( $email['attachments'] ) )
                    {

                        foreach ($email['attachments'] as $key => $at)
                        {
                            $_at_name = $email['attachments'][$key]['name'];
                            // Rename the name to filename the model expects filename not name
                            unset($email['attachments'][$key]['name']);
                            $email['attachments'][$key]['filename'] = $_at_name;
                            $_attachment                            = $imap->getAttachment($email['uid'], $key);
                            $email['attachments'][$key]['data']     = $_attachment['content'];
                        }

                        // Add the attchments to data
                        $data['attachments'] = $email['attachments'];

                    }



                    // Check for To
                    $data['to'] = [];
                    if ( !empty($email['to']))
                    {
                        foreach ($email['to'] as $to)
                        {
                            $data['to'][] = trim(preg_replace('/(.*)<(.*)>/', '\\2', $to));
                        }
                    }

                    // Check for CC
                    $data['cc'] = [];
                    if ( !empty($email['cc']) )
                    {
                        foreach ($email['cc'] as $cc)
                        {
                            $data['cc'][] = trim(preg_replace('/(.*)<(.*)>/', '\\2', $cc));
                        }
                    }


                    if ( hooks()->apply_filters('imap_fetch_from_email_by_reply_to_header', 'true') )
                    {

                        $replyTo = $imap->getReplyToAddresses($email['uid']);

                        if ( !empty( $replyTo ) && count($replyTo) == 1 )
                        {
                            $email['from'] = $replyTo[0];
                        }

                    }


                    $data['fromname'] = preg_replace('/(.*)<(.*)>/', '\\1', $email['from']);
                    $data['fromname'] = trim(str_replace('"', '', $data['fromname']));



                    $inbox                      = [];
                    $inbox['from_email']        = $email['from_email'];
                    $inbox['to']                = implode(',', $data['to']);
                    $inbox['cc']                = implode(',', $data['cc']);
                    $inbox['from_name']         = email_template_decode_mime_header( $data['fromname'] );
                    $inbox['subject']           = $email['subject'];
                    $inbox['message']           = $email['body'];
                    $inbox['unread']            = $email['unread'] ;
                    $inbox['is_trush']          = $email['deleted'] ;
                    //$inbox['unread']            = $last_sequence_id == 1 ? $email['unread'] : 1;
                    $inbox['sequence_id']       = $email['uid'];
                    $inbox['imap_id']           = $imap_server->id;
                    $inbox['message_id']        = $email['message_id'];
                    $inbox['mail_date']         = date( 'Y-m-d H:i:s' , strtotime( $email['date'] ) );
                    $inbox['date_received']     = date('Y-m-d H:i:s');

                    $CI->db->insert(db_prefix().'email_template_manage_inbox', $inbox);

                    $inbox_id = $CI->db->insert_id();



                    $CI->db->where('id',$imap_server->id)->update(db_prefix().'email_template_imap_settings', ['last_sequence_id' => $email['uid'] ] );


                    $path = get_upload_path_by_type('email_template');
                    _maybe_create_upload_path($path);

                    $path .= "inbox/";
                    _maybe_create_upload_path($path);

                    $path .= "$inbox_id/";
                    _maybe_create_upload_path($path);

                    foreach ($data['attachments'] as $attachment)
                    {

                        $filename = urldecode($attachment['filename']);
                        //$filename      = $attachment['filename'];

                        $filenameparts = explode('.', $filename);
                        $extension     = end($filenameparts);
                        $extension     = strtolower($extension);
                        $filename      = implode('', array_slice($filenameparts, 0, 0 - 1));
                        $filename      = trim(preg_replace('/[^a-zA-Z0-9-_ ]/', '', $filename));

                        if ( !$filename )
                        {
                            $filename = 'attachment';
                        }

                        $filename = unique_filename($path, $filename.'.'.$extension);

                        try {

                            $fp       = fopen($path.$filename, 'w');
                            fwrite($fp, $attachment['data']);
                            fclose($fp);

                            $attac                  = [];
                            $attac['inbox_id']      = $inbox_id;
                            $attac['file_path']     = "uploads/email_template/inbox/$inbox_id/";
                            $attac['file_name']     = $filename;
                            $attac['file_type']     = get_mime_by_extension($filename);

                            $CI->db->insert(db_prefix().'email_template_inbox_attachments', $attac);

                        }
                        catch (Exception $e)
                        {
                            echo "Error Attach: " . $e->getMessage();
                        }



                    }


                    if ( count($data['attachments']) > 0 )
                    {
                        $CI->db->where('id', $inbox_id);
                        $CI->db->update(db_prefix().'email_template_manage_inbox', [ 'is_attachment' => 1 ]);
                    }




                    if ($inbox_id) {
                        $imap->setUnseenMessage($email['uid']);
                    }



                } // end foreach emails

            } // end if emails

        }


    }


} , 1 );

function email_template_prepare_imap_email_body_html( $body )
{

    $CI = &get_instance();
    $body = trim($body);
    $body = str_replace('&nbsp;', ' ', $body);
    // Remove html tags - strips inline styles also
    $body = trim(strip_html_tags($body, '<br/>, <br>, <a>'));
    // Once again do security
    $body = $CI->security->xss_clean($body);
    // Remove duplicate new lines
    $body = preg_replace("/[\r\n]+/", "\n", $body);
    // new lines with <br />
    $body = preg_replace('/\n(\s*\n)+/', '<br />', $body);
    $body = preg_replace('/\n/', '<br>', $body);
    return $body;

}



function email_template_handle_google_drive_links_in_text($text = '')

{


    if ( empty($text) )
        return '';



    $pattern = '#\bhttps?://drive.google.com[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#';

    preg_match_all($pattern, $text, $matchGoogleDriveLinks);



    if (isset($matchGoogleDriveLinks[0]) && is_array($matchGoogleDriveLinks[0])) {

        foreach ($matchGoogleDriveLinks[0] as $driveLink) {

            $link = '<a href="' . $driveLink . '">' . $driveLink . '</a>';

            $text = str_replace($driveLink, $link, $text);

            $text = str_replace('<' . $link . '>', $link, $text);

        }

    }



    return $text;

}
