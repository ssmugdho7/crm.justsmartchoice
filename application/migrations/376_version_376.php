<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_376 extends CI_Migration
{
    public function up()
    {
        $raw = get_option('aside_menu_active');
        $decoded = json_decode((string)$raw, true);
        if (is_array($decoded)) {
            $wrapped = isset($decoded['aside_menu_active']) && is_array($decoded['aside_menu_active']);
            $menu = $wrapped ? $decoded['aside_menu_active'] : $decoded;
            foreach ($menu as &$top) {
                if (!is_array($top) || (($top['id'] ?? '') !== 'sales')) { continue; }
                foreach (($top['children'] ?? []) as &$child) {
                    $url = strtolower(trim((string)($child['url'] ?? ''), '/'));
                    $id = strtolower((string)($child['id'] ?? ''));
                    $name = strtolower((string)($child['name'] ?? ''));
                    if (strpos($url, 'invoice') === 0 || strpos($id, 'invoice') !== false || strpos($name, 'account payable') !== false) {
                        $child['name'] = 'Invoices';
                        $child['icon'] = 'fa-solid fa-file-invoice-dollar';
                        $child['url'] = 'invoices';
                    }
                }
                unset($child);
            }
            unset($top);
            if ($wrapped) { $decoded['aside_menu_active'] = $menu; }
            else { $decoded = $menu; }
            update_option('aside_menu_active', json_encode($decoded));
        }
        update_option('smart_choice_contract_initials_pdf_fix', '376');
        update_option('smart_choice_customer_books_theme_fix', '376');
        update_option('smart_choice_sale_attachment_pdf_fix', '376');
    }

    public function down() { }
}
