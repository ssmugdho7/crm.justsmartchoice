<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_375 extends CI_Migration
{
    public function up()
    {
        $this->repairSalesMenuOption();
        update_option('smart_choice_core_recovery_version', '375');
    }

    private function repairSalesMenuOption()
    {
        $raw = get_option('aside_menu_active');
        if (!$raw) {
            return;
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            // Invalid menu JSON can make the admin shell fail. Restore dynamic default menus.
            update_option('aside_menu_active', '[]');
            return;
        }

        $wrapped = isset($decoded['aside_menu_active']) && is_array($decoded['aside_menu_active']);
        $menu = $wrapped ? $decoded['aside_menu_active'] : $decoded;

        foreach ($menu as &$top) {
            if (!is_array($top) || ($top['id'] ?? '') !== 'sales') {
                continue;
            }

            $top['icon'] = 'fa-solid fa-chart-line';
            if (!isset($top['children']) || !is_array($top['children'])) {
                $top['children'] = [];
            }

            $hasContracts = false;
            foreach ($top['children'] as &$child) {
                if (!is_array($child)) {
                    continue;
                }

                $url  = trim((string) ($child['url'] ?? ''), '/');
                $name = strtolower((string) ($child['name'] ?? ''));

                if ($url === 'proposals') {
                    $child['name'] = 'Proposals';
                    $child['icon'] = 'fa-regular fa-file-lines';
                } elseif ($url === 'estimates' || $url === 'estimates/list_estimates') {
                    $child['name'] = 'Estimates';
                    $child['icon'] = 'fa-solid fa-calculator';
                } elseif ($url === 'invoices' || $url === 'invoices/list_invoices' || strpos($name, 'account payable') !== false) {
                    $child['name'] = 'Invoices';
                    $child['icon'] = 'fa-solid fa-file-invoice-dollar';
                } elseif ($url === 'contracts') {
                    $child['name'] = 'Contracts';
                    $child['icon'] = 'fa-solid fa-file-signature';
                    $hasContracts = true;
                } elseif ($url === 'payments') {
                    $child['name'] = 'Payments';
                    $child['icon'] = 'fa-solid fa-credit-card';
                } elseif ($url === 'invoice_items') {
                    $child['name'] = 'Items';
                    $child['icon'] = 'fa-solid fa-boxes-stacked';
                }
            }
            unset($child);

            if (!$hasContracts) {
                $top['children'][] = [
                    'id' => 'child-contracts',
                    'name' => 'Contracts',
                    'url' => 'contracts',
                    'permission' => 'contracts',
                    'icon' => 'fa-solid fa-file-signature',
                ];
            }
        }
        unset($top);

        if ($wrapped) {
            $decoded['aside_menu_active'] = $menu;
            update_option('aside_menu_active', json_encode($decoded));
        } else {
            update_option('aside_menu_active', json_encode($menu));
        }
    }

    public function down()
    {
        // Upgrade-only recovery migration.
    }
}
