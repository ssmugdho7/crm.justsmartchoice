<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Ipam_model extends App_Model
{
    // Get Clients
    public function get_clients()
    {
        return $this->db->get(db_prefix() . 'clients')->result_array();
    }

    // Locations CRUD
    public function get_locations()
    {
        $this->db->select('locations.*, clients.company as client_name');
        $this->db->from(db_prefix() . 'ipam_locations as locations');
        $this->db->join(db_prefix() . 'clients as clients', 'locations.client = clients.userid', 'left');
        return $this->db->get()->result_array();
    }

    public function get_location($id)
    {
        return $this->db->get_where(db_prefix() . 'ipam_locations', ['id' => $id])->row_array();
    }

    public function add_location($data)
    {
        $this->db->insert(db_prefix() . 'ipam_locations', $data);
        return $this->db->insert_id();
    }

    public function update_location($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'ipam_locations', $data);
    }

    public function delete_location($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'ipam_locations');
    }

    // Networks CRUD
    public function get_networks()
    {
        $this->db->select('networks.*, clients.company as client_name, locations.location as location_name, vlans.vlan_name as vlan_name');
        $this->db->from(db_prefix() . 'ipam_networks as networks');
        $this->db->join(db_prefix() . 'clients as clients', 'networks.client = clients.userid', 'left');
        $this->db->join(db_prefix() . 'ipam_locations as locations', 'networks.location = locations.id', 'left');
        $this->db->join(db_prefix() . 'ipam_vlans as vlans', 'networks.vlan_id = vlans.id', 'left');
        return $this->db->get()->result_array();
    }

    public function get_network($id)
    {
        return $this->db->get_where(db_prefix() . 'ipam_networks', ['id' => $id])->row_array();
    }

    public function add_network($data)
    {
        $this->db->insert(db_prefix() . 'ipam_networks', $data);
        return $this->db->insert_id();
    }

    public function update_network($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'ipam_networks', $data);
    }

    public function delete_network($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'ipam_networks');
    }

    // VLANs CRUD
    public function get_vlans()
    {
        return $this->db->get(db_prefix() . 'ipam_vlans')->result_array();
    }

    public function get_vlan($id)
    {
        return $this->db->get_where(db_prefix() . 'ipam_vlans', ['id' => $id])->row_array();
    }

    public function add_vlan($data)
    {
        $this->db->insert(db_prefix() . 'ipam_vlans', $data);
        return $this->db->insert_id();
    }

    public function update_vlan($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'ipam_vlans', $data);
    }

    public function delete_vlan($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'ipam_vlans');
    }

    // Interfaces CRUD
    public function get_interfaces()
    {
        $this->db->select('interfaces.*, networks.network_name as network_name, vlans.vlan_name as vlan_name, parent.interface_name as parent_interface_name');
        $this->db->from(db_prefix() . 'ipam_interfaces as interfaces');
        $this->db->join(db_prefix() . 'ipam_networks as networks', 'interfaces.network = networks.id', 'left');
        $this->db->join(db_prefix() . 'ipam_vlans as vlans', 'interfaces.vlan_id = vlans.id', 'left');
        $this->db->join(db_prefix() . 'ipam_interfaces as parent', 'interfaces.parent_interface_id = parent.id', 'left');
        return $this->db->get()->result_array();
    }
public function get_parent_interfaces()
{
    $this->db->where('parent_interface_id IS NULL');
    return $this->db->get(db_prefix() . 'ipam_interfaces')->result_array();
}

    public function get_interface($id)
    {
        return $this->db->get_where(db_prefix() . 'ipam_interfaces', ['id' => $id])->row_array();
    }

    public function add_interface($data)
    {
        $this->db->insert(db_prefix() . 'ipam_interfaces', $data);
        return $this->db->insert_id();
    }

    public function update_interface($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'ipam_interfaces', $data);
    }

    public function delete_interface($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'ipam_interfaces');
    }

    // IP Addresses CRUD
    public function get_ipaddresses()
    {
        $this->db->select('ipaddresses.*, clients.company as client_name, locations.location as location_name, networks.subnet_ip as network_name, vlans.vlan_name as vlan_name, interfaces.interface_name as interface_name, ipaddresses.dns_name as dns_name');
        $this->db->from(db_prefix() . 'ipam_ipaddresses as ipaddresses');
        $this->db->join(db_prefix() . 'clients as clients', 'ipaddresses.client = clients.userid', 'left');
        $this->db->join(db_prefix() . 'ipam_locations as locations', 'ipaddresses.location = locations.id', 'left');
        $this->db->join(db_prefix() . 'ipam_networks as networks', 'ipaddresses.network = networks.id', 'left');
        $this->db->join(db_prefix() . 'ipam_vlans as vlans', 'ipaddresses.vlan_id = vlans.id', 'left');
        $this->db->join(db_prefix() . 'ipam_interfaces as interfaces', 'ipaddresses.interface = interfaces.id', 'left');
        return $this->db->get()->result_array();
    }

    public function get_ipaddress($id)
    {
        return $this->db->get_where(db_prefix() . 'ipam_ipaddresses', ['id' => $id])->row_array();
    }

    public function add_ipaddress($data)
    {
        $this->db->insert(db_prefix() . 'ipam_ipaddresses', $data);
        return $this->db->insert_id();
    }

    public function update_ipaddress($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'ipam_ipaddresses', $data);
    }

    public function delete_ipaddress($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'ipam_ipaddresses');
    }
}
