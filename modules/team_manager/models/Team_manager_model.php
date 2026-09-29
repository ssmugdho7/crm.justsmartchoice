<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Team_manager_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // TEAM FUNCTIONS

    public function get_teams()
    {
        $sql = "SELECT * FROM " . db_prefix() . "team_manager_teams ORDER BY id DESC";
        return $this->db->query($sql)->result_array();
    }

    public function get_team($id)
    {
        $sql = "SELECT * FROM " . db_prefix() . "team_manager_teams WHERE id = ?";
        return $this->db->query($sql, [$id])->row();
    }

    public function add_team($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'team_manager_teams', $data);
        return $this->db->insert_id();
    }

    public function update_team($data, $id)
    {
        $this->db->where('id', $id);
        return $this->db->update(db_prefix() . 'team_manager_teams', $data);
    }

    public function delete_team($id)
    {
        // Delete team members first
        $this->db->where('team_id', $id);
        $this->db->delete(db_prefix() . 'team_manager_members');

        // Then delete the team
        $this->db->where('id', $id);
        return $this->db->delete(db_prefix() . 'team_manager_teams');
    }

    // TEAM MEMBER FUNCTIONS

    public function get_members_by_team($team_id)
    {
        $sql = "SELECT * FROM " . db_prefix() . "team_manager_members WHERE team_id = ? ORDER BY id DESC";
        return $this->db->query($sql, [$team_id])->result_array();
    }

    public function get_member($id)
    {
        $sql = "SELECT * FROM " . db_prefix() . "team_manager_members WHERE id = ?";
        return $this->db->query($sql, [$id])->row();
    }

    public function add_member($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'team_manager_members', $data);
        return $this->db->insert_id();
    }

    public function update_member($data, $id)
    {
        $this->db->where('id', $id);
        return $this->db->update(db_prefix() . 'team_manager_members', $data);
    }

    public function delete_member($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete(db_prefix() . 'team_manager_members');
    }
}
