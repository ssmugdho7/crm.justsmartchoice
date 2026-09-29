<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Ipam extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('ipam_model');
    }

    // Locations CRUD
    public function locations()
    {
       $data['clients'] = $this->ipam_model->get_clients();
        $data['locations'] = $this->ipam_model->get_locations();
        $this->load->view('admin/ipam/locations', $data);
    }

    public function add_location()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            $this->ipam_model->add_location($data);
            redirect(admin_url('ipam/locations'));
        }
       $data['clients'] = $this->ipam_model->get_clients();
        $this->load->view('admin/ipam/location_form',$data);
    }

    public function edit_location($id)
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            $this->ipam_model->update_location($id, $data);
            redirect(admin_url('ipam/locations'));
        }
          $data['clients'] = $this->ipam_model->get_clients();
     $data['location'] = $this->ipam_model->get_location($id);
        $this->load->view('admin/ipam/location_form', $data);
    }

    public function delete_location($id)
    {
        $this->ipam_model->delete_location($id);
        redirect(admin_url('ipam/locations'));
    }

    // Networks CRUD
    public function networks()
    {
        $data['networks'] = $this->ipam_model->get_networks();
        $this->load->view('admin/ipam/networks', $data);
    }

    public function add_network()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            $this->ipam_model->add_network($data);
            redirect(admin_url('ipam/networks'));
        }
        $data['clients'] = $this->ipam_model->get_clients();
        $data['locations'] = $this->ipam_model->get_locations();
        $data['vlans'] = $this->ipam_model->get_vlans();
        $this->load->view('admin/ipam/network_form', $data);
    }

    public function edit_network($id)
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            $this->ipam_model->update_network($id, $data);
            redirect(admin_url('ipam/networks'));
        }
        $data['network'] = $this->ipam_model->get_network($id);
        $data['clients'] = $this->ipam_model->get_clients();
        $data['locations'] = $this->ipam_model->get_locations();
        $data['vlans'] = $this->ipam_model->get_vlans();
        $this->load->view('admin/ipam/network_form', $data);
    }

    public function delete_network($id)
    {
        $this->ipam_model->delete_network($id);
        redirect(admin_url('ipam/networks'));
    }

    // VLANs CRUD
    public function vlans()
    {
        $data['vlans'] = $this->ipam_model->get_vlans();
        $this->load->view('admin/ipam/vlans', $data);
    }

    public function add_vlan()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            $this->ipam_model->add_vlan($data);
            redirect(admin_url('ipam/vlans'));
        }
        $this->load->view('admin/ipam/vlan_form');
    }

    public function edit_vlan($id)
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            $this->ipam_model->update_vlan($id, $data);
            redirect(admin_url('ipam/vlans'));
        }
        $data['vlan'] = $this->ipam_model->get_vlan($id);
        $this->load->view('admin/ipam/vlan_form', $data);
    }

    public function delete_vlan($id)
    {
        $this->ipam_model->delete_vlan($id);
        redirect(admin_url('ipam/vlans'));
    }

    // Interfaces CRUD
    public function interfaces()
    {
        $data['interfaces'] = $this->ipam_model->get_interfaces();
        $this->load->view('admin/ipam/interfaces', $data);
    }

    public function add_interface()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            $this->ipam_model->add_interface($data);
            redirect(admin_url('ipam/interfaces'));
        }
               $data['interfaces'] = $this->ipam_model->get_parent_interfaces();
 $data['networks'] = $this->ipam_model->get_networks();
        $data['vlans'] = $this->ipam_model->get_vlans();
        $this->load->view('admin/ipam/interface_form', $data);
    }

    public function edit_interface($id)
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            $this->ipam_model->update_interface($id, $data);
            redirect(admin_url('ipam/interfaces'));
        }
        $data['interfaces'] = $this->ipam_model->get_parent_interfaces();
         $data['interface'] = $this->ipam_model->get_interface($id);
        $data['networks'] = $this->ipam_model->get_networks();
        $data['vlans'] = $this->ipam_model->get_vlans();
        $this->load->view('admin/ipam/interface_form', $data);
    }

    public function delete_interface($id)
    {
        $this->ipam_model->delete_interface($id);
        redirect(admin_url('ipam/interfaces'));
    }

    // IP Addresses CRUD
    public function ipaddresses()
    {
        $data['ipaddresses'] = $this->ipam_model->get_ipaddresses();
        $this->load->view('admin/ipam/ipaddresses', $data);
    }

    public function add_ipaddress()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            $this->ipam_model->add_ipaddress($data);
            redirect(admin_url('ipam/ipaddresses'));
        }
        $data['clients'] = $this->ipam_model->get_clients();
        $data['locations'] = $this->ipam_model->get_locations();
        $data['networks'] = $this->ipam_model->get_networks();
        $data['vlans'] = $this->ipam_model->get_vlans();
        $data['interfaces'] = $this->ipam_model->get_interfaces();
        $this->load->view('admin/ipam/ipaddress_form', $data);
    }

    public function edit_ipaddress($id)
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            $this->ipam_model->update_ipaddress($id, $data);
            redirect(admin_url('ipam/ipaddresses'));
        }
        $data['ipaddress'] = $this->ipam_model->get_ipaddress($id);
        $data['clients'] = $this->ipam_model->get_clients();
        $data['locations'] = $this->ipam_model->get_locations();
        $data['networks'] = $this->ipam_model->get_networks();
        $data['vlans'] = $this->ipam_model->get_vlans();
        $data['interfaces'] = $this->ipam_model->get_interfaces();
        $this->load->view('admin/ipam/ipaddress_form', $data);
    }

    public function delete_ipaddress($id)
    {
        $this->ipam_model->delete_ipaddress($id);
        redirect(admin_url('ipam/ipaddresses'));
    }
}
