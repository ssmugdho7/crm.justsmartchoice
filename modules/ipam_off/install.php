<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
 * Installation script for IPAM Module
 * Description: This script creates necessary database tables and registers the IPAM module in Perfex CRM.
 * Author: Your Name
 * Author URI: Your Website
 * Version: 1.0.0
 */

$CI = &get_instance();

// Create ipam_locations table
if (!$CI->db->table_exists(db_prefix() . 'ipam_locations')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "ipam_locations` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `client` INT NOT NULL,
        `location` VARCHAR(255) NOT NULL,
        PRIMARY KEY (`id`),
        CONSTRAINT `fk_ipam_locations_client` FOREIGN KEY (`client`) REFERENCES " . db_prefix() . "clients(`userid`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
}

// Create ipam_vlans table
if (!$CI->db->table_exists(db_prefix() . 'ipam_vlans')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "ipam_vlans` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `vlan_name` VARCHAR(255) NOT NULL,
        `vlan_id` INT NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
}

// Create ipam_networks table
if (!$CI->db->table_exists(db_prefix() . 'ipam_networks')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "ipam_networks` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `client` INT NOT NULL,
        `location` INT NOT NULL,
        `subnet_ip` VARCHAR(255) NOT NULL,
        `netmask` VARCHAR(255) NOT NULL,
        `network_name` VARCHAR(255) NULL,
        `vlan_id` INT DEFAULT NULL,
        PRIMARY KEY (`id`),
        CONSTRAINT `fk_ipam_networks_client` FOREIGN KEY (`client`) REFERENCES " . db_prefix() . "clients(`userid`) ON DELETE CASCADE,
        CONSTRAINT `fk_ipam_networks_location` FOREIGN KEY (`location`) REFERENCES " . db_prefix() . "ipam_locations(`id`) ON DELETE CASCADE,
        CONSTRAINT `fk_ipam_networks_vlan` FOREIGN KEY (`vlan_id`) REFERENCES " . db_prefix() . "ipam_vlans(`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
}

// Create ipam_interfaces table
if (!$CI->db->table_exists(db_prefix() . 'ipam_interfaces')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "ipam_interfaces` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `interface_name` VARCHAR(255) NOT NULL,
        `mac_address` VARCHAR(255) DEFAULT NULL,
        `network` INT NOT NULL,
        `vlan_id` INT DEFAULT NULL,
        PRIMARY KEY (`id`),
        CONSTRAINT `fk_ipam_interfaces_network` FOREIGN KEY (`network`) REFERENCES " . db_prefix() . "ipam_networks(`id`) ON DELETE CASCADE,
        CONSTRAINT `fk_ipam_interfaces_vlan` FOREIGN KEY (`vlan_id`) REFERENCES " . db_prefix() . "ipam_vlans(`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
}

// Create ipam_ipaddresses table
if (!$CI->db->table_exists(db_prefix() . 'ipam_ipaddresses')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "ipam_ipaddresses` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `client` INT NOT NULL,
        `location` INT NOT NULL,
        `network` INT NOT NULL,
        `vlan_id` INT DEFAULT NULL,
        `ip_address` VARCHAR(255) NOT NULL,
        `interface` INT DEFAULT NULL,
        PRIMARY KEY (`id`),
        CONSTRAINT `fk_ipam_ipaddresses_client` FOREIGN KEY (`client`) REFERENCES " . db_prefix() . "clients(`userid`) ON DELETE CASCADE,
        CONSTRAINT `fk_ipam_ipaddresses_location` FOREIGN KEY (`location`) REFERENCES " . db_prefix() . "ipam_locations(`id`) ON DELETE CASCADE,
        CONSTRAINT `fk_ipam_ipaddresses_network` FOREIGN KEY (`network`) REFERENCES " . db_prefix() . "ipam_networks(`id`) ON DELETE CASCADE,
        CONSTRAINT `fk_ipam_ipaddresses_vlan` FOREIGN KEY (`vlan_id`) REFERENCES " . db_prefix() . "ipam_vlans(`id`) ON DELETE SET NULL,
        CONSTRAINT `fk_ipam_ipaddresses_interface` FOREIGN KEY (`interface`) REFERENCES " . db_prefix() . "ipam_interfaces(`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
}

// Add DNS Name column to ipam_ipaddresses table
if (!$CI->db->field_exists('dns_name', db_prefix() . 'ipam_ipaddresses')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . "ipam_ipaddresses`
    ADD COLUMN `dns_name` VARCHAR(255) NULL;");
}

// Add parent_interface_id and virtual columns to ipam_interfaces table
if (!$CI->db->field_exists('parent_interface_id', db_prefix() . 'ipam_interfaces')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . "ipam_interfaces`
    ADD COLUMN `parent_interface_id` INT(11) NULL,
    ADD COLUMN `virtual` TINYINT(1) NOT NULL DEFAULT 0;");
}

// Add public_private column to ipam_networks table
if (!$CI->db->field_exists('public_private', db_prefix() . 'ipam_networks')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . "ipam_networks`
    ADD COLUMN `public_private` VARCHAR(7) NOT NULL DEFAULT 'Private';");
}

