<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_373 extends CI_Migration
{
    public function up()
    {
        $this->normalizeSalesMenu();
        update_option('sc_contract_portal_repair_version', '3.7.3');
    }

    private function normalizeSalesMenu(): void
    {
        $raw = get_option('aside_menu_active');
        if (!$raw) { return; }
        $menu = json_decode($raw, true);
        if (!is_array($menu)) { return; }
        foreach ($menu as &$top) {
            if (($top['id'] ?? '') !== 'sales') { continue; }
            $top['icon'] = 'fa-solid fa-chart-line';
            $children = $top['children'] ?? [];
            $map = [
                'proposals' => ['Proposals','fa-regular fa-file-lines'],
                'estimates/list_estimates' => ['Estimates','fa-solid fa-calculator'],
                'estimates' => ['Estimates','fa-solid fa-calculator'],
                'invoices/list_invoices' => ['Invoices','fa-solid fa-file-invoice-dollar'],
                'invoices' => ['Invoices','fa-solid fa-file-invoice-dollar'],
                'contracts' => ['Contracts','fa-solid fa-file-signature'],
                'payments' => ['Payments','fa-solid fa-credit-card'],
                'invoice_items' => ['Items','fa-solid fa-boxes-stacked'],
            ];
            $hasContracts = false;
            foreach ($children as &$child) {
                $url = trim((string)($child['url'] ?? ''), '/');
                if (isset($map[$url])) {
                    $child['name'] = $map[$url][0];
                    $child['icon'] = $map[$url][1];
                }
                if ($url === 'contracts') { $hasContracts = true; }
            }
            unset($child);
            if (!$hasContracts) {
                $children[] = ['id'=>'child-contracts','name'=>'Contracts','url'=>'contracts','permission'=>'contracts','icon'=>'fa-solid fa-file-signature'];
            }
            $top['children'] = $children;
        }
        unset($top);
        update_option('aside_menu_active', json_encode($menu));
    }

    public function down() {}
}
