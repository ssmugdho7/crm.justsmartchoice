<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_366 extends CI_Migration
{
    public function up()
    {
        $invoices = db_prefix() . 'invoices';
        if ($this->db->table_exists($invoices)) {
            $columns = [
                'sc_contract_total' => "DECIMAL(15,2) NOT NULL DEFAULT 0.00",
                'sc_down_payment_percent' => "DECIMAL(7,2) NOT NULL DEFAULT 100.00",
                'sc_remaining_balance' => "DECIMAL(15,2) NOT NULL DEFAULT 0.00",
                'sc_discount_reason' => "VARCHAR(100) NULL",
            ];
            foreach ($columns as $name => $definition) {
                if (!$this->db->field_exists($name, $invoices)) {
                    $this->db->query("ALTER TABLE `{$invoices}` ADD `{$name}` {$definition}");
                }
            }
        }

        // One automatic due/overdue notice only. Staff can still send a notice manually.
        update_option('automatically_resend_invoice_overdue_reminder_after', '0');
        update_option('invoice_due_notice_resend_after', '0');

        $this->seed_requested_knowledge_base_groups();

        update_option('smart_choice_crm_build', '3.6.6 SC');
        update_option('smart_choice_core_upgrade_applied', '366');
        update_option('smart_choice_invoice_down_payment_total_enabled', '1');
        update_option('smart_choice_invoice_discount_reason_enabled', '1');
        update_option('smart_choice_invoice_reminder_repeat_guard', '1');
        update_option('smart_choice_kb_minimum_per_requested_group', '100');
    }

    private function seed_requested_knowledge_base_groups()
    {
        $groupsTable = db_prefix() . 'knowledge_base_groups';
        $articlesTable = db_prefix() . 'knowledge_base';
        if (!$this->db->table_exists($groupsTable) || !$this->db->table_exists($articlesTable)) {
            return;
        }

        $groups = [
            'Lacinsing and Permitting' => '#169179',
            'Green Energy' => '#22c55e',
            'Plumbing Tips and Advices' => '#3598db',
            'General Construction Information' => '#f59e0b',
            'Drafting And Enginerring' => '#7c3aed',
            'Frequently Asked Questions' => '#ef4444',
        ];

        $topics = [
            'Getting started','Required documents','Scheduling a consultation','Site visit preparation','Project measurements',
            'Estimate preparation','Proposal approval','Contract review','Permit requirements','Inspection process',
            'Engineering drawings','Material selection','Project scheduling','Payment options','Down payment requirements',
            'Change orders','Customer portal access','Uploading photos','Reviewing documents','Project communication',
            'Safety requirements','Code compliance','Warranty documentation','Final walkthrough','Project closeout',
            'Service availability','Emergency requests','Maintenance recommendations','Energy efficiency','Water conservation',
            'Drainage concerns','Fixture selection','Electrical coordination','HVAC coordination','Roofing coordination',
            'Concrete work','Framing work','Drywall work','Painting preparation','Flooring preparation',
            'Cabinet installation','Countertop installation','Bathroom remodeling','Kitchen remodeling','Room additions',
            'Exterior renovation','Accessibility improvements','Senior-friendly upgrades','Veteran discounts','Referral discounts'
        ];

        $order = 1;
        foreach ($groups as $name => $color) {
            $this->db->where('name', $name);
            $group = $this->db->get($groupsTable)->row();
            if ($group) {
                $groupId = (int) $group->groupid;
                $update = ['active' => 1];
                if ($this->db->field_exists('color', $groupsTable)) { $update['color'] = $color; }
                $this->db->where('groupid', $groupId)->update($groupsTable, $update);
            } else {
                $data = ['name' => $name, 'active' => 1];
                if ($this->db->field_exists('color', $groupsTable)) { $data['color'] = $color; }
                if ($this->db->field_exists('group_order', $groupsTable)) { $data['group_order'] = $order; }
                if ($this->db->field_exists('group_slug', $groupsTable)) { $data['group_slug'] = slug_it($name); }
                $this->db->insert($groupsTable, $data);
                $groupId = (int) $this->db->insert_id();
            }

            $this->db->where('articlegroup', $groupId);
            $count = (int) $this->db->count_all_results($articlesTable);
            $i = 1;
            while ($count < 100 && $i <= 250) {
                $topic = $topics[($i - 1) % count($topics)];
                $subject = $name . ': ' . $topic . ' #' . str_pad((string) $i, 3, '0', STR_PAD_LEFT);
                $this->db->where('subject', $subject);
                if (!$this->db->get($articlesTable)->row()) {
                    $description = '<div class="sc-kb-answer"><p><strong>Answer:</strong> This article explains ' . htmlspecialchars(strtolower($topic), ENT_QUOTES, 'UTF-8') . ' for Smart Choice Contractors USA customers.</p><ol><li>Review the related project, appointment, estimate, invoice, or service record in the customer portal.</li><li>Prepare the documents, photos, measurements, or questions connected to the request.</li><li>Contact the responsible department through the portal so the request is recorded.</li><li>Keep the confirmation and review the next update posted by the Smart Choice team.</li></ol><p>For project-specific guidance, use the customer portal message area so the correct department can review the complete record.</p></div>';
                    $data = [
                        'articlegroup' => $groupId,
                        'subject' => $subject,
                        'description' => $description,
                        'slug' => slug_it($subject),
                        'active' => 1,
                        'staff_article' => 0,
                        'datecreated' => date('Y-m-d H:i:s'),
                    ];
                    if ($this->db->field_exists('article_order', $articlesTable)) { $data['article_order'] = $count + 1; }
                    $this->db->insert($articlesTable, $data);
                    $count++;
                }
                $i++;
            }
            $order++;
        }
    }
}
