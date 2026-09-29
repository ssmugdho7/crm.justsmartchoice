<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_129 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $booksTable = db_prefix() . 'wiki_books';
        $articlesTable = db_prefix() . 'wiki_articles';

        if (!$CI->db->table_exists($booksTable) || !$CI->db->table_exists($articlesTable)) {
            return;
        }

        if (!$CI->db->field_exists('cover_image', $booksTable)) {
            $CI->db->query("ALTER TABLE `{$booksTable}` ADD `cover_image` VARCHAR(500) NULL DEFAULT NULL AFTER `short_description`");
        }

        $staffId = 0;
        if ($CI->db->table_exists(db_prefix() . 'staff')) {
            $staff = $CI->db->select('staffid')->where('active', 1)->order_by('admin', 'DESC')->order_by('staffid', 'ASC')->get(db_prefix() . 'staff')->row();
            if ($staff) { $staffId = (int) $staff->staffid; }
        }

        $guides = [];
        $guides[] = [
            'name' => 'What to Expect From Our Services',
            'description' => 'A complete guide to communication, planning, construction coordination, quality control, inspections, and project closeout.',
            'cover' => 'modules/training_manual/assets/img/customer-guides/service-experience.svg',
            'slug' => 'smart-choice-service-experience',
            'title' => 'Your Smart Choice Service Experience',
            'content' => <<<'HTML_0'
<h2>Welcome to a Professional Construction Experience</h2><p>Smart Choice Contractors USA approaches every project as a managed construction process, not simply a collection of individual tasks. From the first conversation through final closeout, our objective is to provide clear expectations, responsible coordination, organized documentation, and professional communication. This guide explains what you can expect when working with our team and how you can help keep your project moving efficiently.</p><h3>1. Discovery and Project Definition</h3><p>The process begins with understanding your goals, the existing property conditions, the desired finish level, and the practical requirements of the project. Depending on the scope, this may include photographs, measurements, site observations, existing plans, product selections, budget priorities, and target timing. Complex projects may also require engineering, architectural drawings, energy calculations, surveys, permits, or specialty trade coordination.</p><h3>2. Estimate, Proposal, and Scope Review</h3><p>Your estimate or proposal should be reviewed carefully. The written scope controls what is included. Pay particular attention to materials, allowances, exclusions, owner-supplied items, permit responsibility, access requirements, and work that depends on concealed conditions. Ask questions before approval so that expectations are aligned.</p><h3>3. Preconstruction Planning</h3><p>After approval, our team organizes the work sequence, verifies required documents, confirms major selections, coordinates labor and suppliers, and identifies dependencies. Scheduling is affected by permitting, inspections, material availability, weather, site access, design changes, and the sequence of multiple trades. A professional schedule is a coordinated plan and may be adjusted as actual field conditions develop.</p><h3>4. Site Preparation and Protection</h3><p>Before work begins, remove fragile or valuable personal items from the active work area. Provide reasonable access to the property and identify any special restrictions. Construction creates vibration, dust, noise, temporary interruptions, and material staging. Our team uses reasonable protection and cleanup practices appropriate to the scope, but a work area remains an active construction environment until completion.</p><h3>5. Communication During Construction</h3><p>Use the customer portal, support tickets, email, and approved project contacts for important communication. Written communication helps preserve decisions and reduces misunderstandings. Questions involving scope, price, scheduling, selections, or changes should be documented rather than handled only through informal conversations.</p><h3>6. Changes and Unforeseen Conditions</h3><p>Existing buildings can contain concealed damage, noncompliant prior work, deteriorated materials, structural conflicts, plumbing or electrical conditions, moisture intrusion, or other issues that cannot be fully identified before demolition. When a condition changes the required work, the team will document it and explain the available options. Additional work should be authorized through the proper change process before proceeding.</p><h3>7. Quality Control and Inspections</h3><p>Quality is verified throughout the process, not only at the end. This may include measurements, installation checks, trade coordination, manufacturer requirements, photographs, and required governmental inspections. An inspection approval confirms compliance with the inspected stage; it does not replace final project review or manufacturer maintenance requirements.</p><h3>8. Project Completion and Closeout</h3><p>Near completion, remaining visible corrections may be organized into a punch list. Final cleanup, document delivery, final payment, inspection completion, and customer acknowledgment are part of closeout. Keep your approved documents, product information, receipts, and maintenance instructions for future reference.</p><h3>How Customers Help the Project Succeed</h3><ul><li>Respond promptly to selection and approval requests.</li><li>Keep access routes clear and communicate property restrictions.</li><li>Use written channels for decisions and changes.</li><li>Review documents before signing or paying.</li><li>Do not direct field personnel to perform unapproved extra work.</li><li>Report concerns early so they can be addressed efficiently.</li></ul><p>Our goal is to provide a dependable, organized, and respectful experience while delivering work that reflects professional construction standards.</p>
HTML_0
        ];
        $guides[] = [
            'name' => 'How to Use the Client Portal',
            'description' => 'Learn how to access documents, monitor projects, review financial activity, update your profile, and communicate securely.',
            'cover' => 'modules/training_manual/assets/img/customer-guides/client-portal.svg',
            'slug' => 'smart-choice-client-portal-guide',
            'title' => 'Using Your Smart Choice Client Portal',
            'content' => <<<'HTML_1'
<h2>Your Secure Project Information Center</h2><p>The Smart Choice client portal gives you one organized location for project records and communication. Instead of searching through separate email chains, you can use the portal to review documents, track activity, submit support requests, and maintain your contact information.</p><h3>Signing In Safely</h3><p>Use the email address associated with your customer account. Keep your password private and avoid sharing one login among several people. If multiple authorized contacts need access, request separate contact accounts when appropriate. Always sign out on shared computers.</p><h3>Understanding the Dashboard</h3><p>The dashboard summarizes the items connected to your account, such as estimates, proposals, contracts, invoices, projects, tickets, announcements, and shared training resources. A zero count means that no item of that type is currently assigned to your contact; it does not necessarily mean the overall company record is empty.</p><h3>Reviewing Documents</h3><p>Open each document from its corresponding menu. Read the entire scope, notes, line items, totals, terms, and attachments. Download a PDF for your records when needed. Documents may change status as they are drafted, sent, accepted, signed, paid, or completed.</p><h3>Profile and Contact Information</h3><p>Keep your phone number, email, and address current. Incorrect information can delay notifications, document delivery, or project coordination. Contact our office when company names, billing contacts, or authorized decision-makers must be changed.</p><h3>Notifications</h3><p>The portal may display notifications for new documents, project updates, ticket replies, payments, or administrative activity. Open notifications promptly. Email and SMS notices are helpful, but the portal record should be used to confirm the current status.</p><h3>Uploading and Downloading Files</h3><p>When an upload option is available, use clear filenames and provide a brief explanation. Suitable uploads may include reference photographs, product selections, signed forms, or requested documents. Do not upload passwords, complete payment-card information, or unrelated sensitive records.</p><h3>Mobile Use</h3><p>The portal is responsive and can be used from a phone or tablet. For long contracts, detailed estimates, or signatures, a larger screen may provide a better review experience. Keep your browser updated and allow pop-ups only when required for a trusted document preview.</p><h3>Best Practices</h3><ul><li>Review the dashboard regularly during an active project.</li><li>Download important executed documents for your own records.</li><li>Use support tickets for issues requiring tracked follow-up.</li><li>Do not approve a document until you understand its contents.</li><li>Report incorrect account information promptly.</li></ul><p>The client portal is designed to improve transparency, preserve project history, and make communication more efficient for both the customer and the Smart Choice team.</p>
HTML_1
        ];
        $guides[] = [
            'name' => 'Support Tickets and Communication',
            'description' => 'A practical guide to requesting assistance, documenting concerns, following responses, and reaching the right department.',
            'cover' => 'modules/training_manual/assets/img/customer-guides/support-system.svg',
            'slug' => 'smart-choice-support-ticket-guide',
            'title' => 'Support Tickets and Project Communication',
            'content' => <<<'HTML_2'
<h2>Clear Communication Produces Better Results</h2><p>The support ticket system creates a documented conversation that can be assigned, reviewed, and followed through completion. It is the preferred method for questions or concerns that require research, coordination, photographs, scheduling, or a formal response.</p><h3>When to Create a Ticket</h3><p>Create a ticket for portal assistance, document questions, scheduling concerns, service requests, warranty-related inquiries, billing clarification, or a project issue that should be tracked. Emergencies involving immediate danger, active flooding, fire, electrical hazards, or threats to personal safety should be reported to the appropriate emergency service first.</p><h3>Writing an Effective Request</h3><p>Use a specific subject and explain what happened, where it occurred, when you noticed it, and what outcome you are requesting. Include the project name or property address when helpful. A message such as “problem” does not provide enough information for efficient assignment.</p><h3>Adding Photographs and Documents</h3><p>Attach clear photographs from both close and wide angles. Add a reference object when scale is important. For documents, use readable PDF or image files and avoid password-protected attachments unless previously coordinated. Never upload sensitive payment credentials.</p><h3>Priorities and Response Times</h3><p>Priority should reflect actual impact. A cosmetic question and an active water intrusion do not have the same urgency. Response timing can depend on office hours, trade availability, required site review, manufacturer input, engineering questions, or access to project records. Repeated duplicate tickets can slow assignment by dividing the same issue across several records.</p><h3>Following the Conversation</h3><p>Reply inside the existing ticket whenever the subject is the same. This keeps photographs, decisions, and staff responses together. Create a new ticket only for a separate issue. Check the portal for replies even when you expect an email or text notification.</p><h3>Status Meanings</h3><ul><li><strong>Open:</strong> the request has been received and remains active.</li><li><strong>In Progress:</strong> staff are reviewing, coordinating, or taking action.</li><li><strong>Waiting:</strong> additional information, customer access, supplier input, or another dependency is required.</li><li><strong>Resolved or Closed:</strong> the documented request has been addressed or completed.</li></ul><h3>Respectful and Productive Communication</h3><p>Provide facts, dates, photographs, and the requested resolution. Avoid directing unrelated employees or subcontractors to make scope or payment decisions. The assigned office or project representative will coordinate the proper response.</p><p>A well-documented ticket protects both the customer and the company, reduces confusion, and creates a reliable record of the issue and its resolution.</p>
HTML_2
        ];
        $guides[] = [
            'name' => 'Estimates, Proposals, Contracts and Invoices',
            'description' => 'Understand the purpose of each document, how to review attachments, approve work, sign agreements, and verify payments.',
            'cover' => 'modules/training_manual/assets/img/customer-guides/documents-payments.svg',
            'slug' => 'smart-choice-documents-payments-guide',
            'title' => 'Understanding Your Project Documents',
            'content' => <<<'HTML_3'
<h2>Each Document Has a Different Purpose</h2><p>Construction projects involve several document types. Understanding their roles helps you make informed decisions and maintain accurate records.</p><h3>Estimates</h3><p>An estimate communicates anticipated pricing and scope based on available information. Review quantities, allowances, exclusions, taxes, notes, and expiration dates. An estimate may require revision when site conditions, selections, engineering, permitting, or requested work changes.</p><h3>Proposals</h3><p>A proposal presents a more formal offer and may include alternatives, assumptions, attachments, images, schedules, or acceptance terms. Compare the written scope rather than relying only on the total price. Confirm that the proposal reflects the option you intend to purchase.</p><h3>Contracts</h3><p>A contract is the binding agreement governing the work. Read every page, including incorporated exhibits, payment schedules, notices, cancellation language, responsibilities, and change procedures. Digital signatures and initials should be completed only after review. Keep a copy of the fully executed contract.</p><h3>Invoices</h3><p>An invoice requests payment for a deposit, progress milestone, approved change, product, service, or balance. Verify the invoice number, related project, line items, credits, previous payments, amount due, and due date. Questions should be raised before payment rather than after the account becomes overdue.</p><h3>Attachments</h3><p>Attachments may include photographs, drawings, product information, schedules, specifications, receipts, or supplemental terms. Open and review every attachment displayed with a document. When downloading a PDF, confirm whether the attachment is embedded or provided as a separate file.</p><h3>Approvals and Signatures</h3><p>Approval confirms that you accept the document as presented. Do not approve based on a verbal summary if the written document is different. Initial fields may acknowledge specific clauses, while the signature completes the agreement. Contact the office immediately if a signature image, initial, date, or printed name is incorrect.</p><h3>Payments and Receipts</h3><p>Use approved payment methods and verify that the payment is applied to the correct invoice. Retain receipts and confirmation numbers. A payment status may take time to update when a transaction is pending, manually recorded, or processed through an external provider.</p><h3>Changes After Approval</h3><p>Any change to scope, material, price, schedule, or responsibility should be documented. A revised estimate, change order, proposal, or invoice may be issued. Field conversations do not automatically modify the written agreement.</p><h3>Document Review Checklist</h3><ul><li>Correct customer and project information</li><li>Clear scope and exclusions</li><li>Correct quantities, rates, taxes, and totals</li><li>Required attachments reviewed</li><li>Payment timing understood</li><li>Signatures and initials placed correctly</li><li>Downloaded copy retained</li></ul><p>Careful document review protects your investment and helps the project proceed with fewer misunderstandings.</p>
HTML_3
        ];
        $guides[] = [
            'name' => 'Our Vision, Values and Quality Standards',
            'description' => 'Learn the principles behind Smart Choice Contractors USA and the standards used to guide every customer relationship and project.',
            'cover' => 'modules/training_manual/assets/img/customer-guides/vision-standards.svg',
            'slug' => 'smart-choice-vision-values',
            'title' => 'Our Vision and Commitment to Customers',
            'content' => <<<'HTML_4'
<h2>Building More Than Projects</h2><p>Smart Choice Contractors USA exists to provide professional construction services supported by organization, accountability, skilled coordination, and respect for the customer’s property and investment. Our long-term vision is to build lasting relationships by delivering consistent service and continuously improving how projects are planned, communicated, and completed.</p><h3>Our Mission</h3><p>Our mission is to help homeowners, property owners, and business clients improve their properties through responsible construction management, qualified trade coordination, clear documentation, and professional service.</p><h3>Professionalism</h3><p>Professionalism means arriving prepared, communicating respectfully, maintaining organized records, following approved procedures, and taking responsibility for assigned work. It also means being honest when a condition requires additional investigation rather than making unsupported promises.</p><h3>Quality</h3><p>Quality begins with the correct scope and continues through material selection, preparation, installation, inspection, and closeout. A beautiful finish cannot compensate for poor preparation. Our standards emphasize appropriate methods, manufacturer requirements, code considerations, and verification throughout the work.</p><h3>Safety and Compliance</h3><p>Construction activities must be performed with attention to safety, property protection, permits, inspections, and applicable requirements. Customers should not enter restricted work zones or request unsafe shortcuts. Compliance protects people, property, and the long-term value of the improvement.</p><h3>Transparency</h3><p>We use written estimates, proposals, contracts, invoices, change documentation, portal records, and support tickets to make project information easier to review. Transparency does not mean that every condition can be predicted; it means that new information is communicated and documented responsibly.</p><h3>Respect</h3><p>Every property is important to its owner. Our team is expected to communicate professionally, protect the work area reasonably, and respect customer privacy. We also ask customers to treat employees, trade partners, suppliers, inspectors, and office staff respectfully.</p><h3>Continuous Improvement</h3><p>We evaluate processes, technology, training, customer feedback, and project outcomes to improve future performance. The customer portal and training library are part of that effort, giving clients better access to information and clearer expectations.</p><h3>Our Commitment</h3><ul><li>Organized project documentation</li><li>Clear communication and defined responsibilities</li><li>Responsible coordination of labor and materials</li><li>Professional review of concerns</li><li>Respect for approved scope, safety, and compliance</li><li>Continuous improvement in service and technology</li></ul><p>Our goal is to be recognized as a dependable, high-standard construction company that customers can confidently recommend and return to for future projects. Smart Choice Contractors USA is committed to doing the work through professional service, disciplined processes, and respect for every customer relationship.</p>
HTML_4
        ];

        foreach ($guides as $guide) {
            $existingBook = $CI->db->where('name', $guide['name'])->get($booksTable)->row();
            if ($existingBook) {
                $bookId = (int) $existingBook->id;
                $CI->db->where('id', $bookId)->update($booksTable, ['customer_visible' => 1, 'cover_image' => $guide['cover']]);
            } else {
                $bookData = [
                    'name' => $guide['name'],
                    'short_description' => $guide['description'],
                    'customer_visible' => 1,
                    'cover_image' => $guide['cover'],
                    'assign_type' => 'all_staff',
                    'assign_ids' => '',
                    'author_id' => $staffId,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
                if ($CI->db->field_exists('created_by', $booksTable)) { $bookData['created_by'] = $staffId; }
                if ($CI->db->field_exists('updated_by', $booksTable)) { $bookData['updated_by'] = $staffId; }
                $CI->db->insert($booksTable, $bookData);
                $bookId = (int) $CI->db->insert_id();
            }

            $existingArticle = $CI->db->where('slug', $guide['slug'])->get($articlesTable)->row();
            if ($existingArticle) { continue; }

            $articleData = [
                'title' => $guide['title'],
                'description' => $guide['description'],
                'content' => $guide['content'],
                'is_bookmark' => 0,
                'view_counter' => 0,
                'author_id' => $staffId,
                'book_id' => $bookId,
                'is_publish' => 1,
                'slug' => $guide['slug'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            $optional = [
                'created_by' => $staffId,
                'updated_by' => $staffId,
                'thumbnail' => $guide['cover'],
                'short_code' => $guide['slug'],
                'visibility' => 'public',
                'language_code' => 'en',
                'style_preset' => 'smart_choice',
                'audience' => 'customer_portal',
                'content_kind' => 'article',
                'type' => 'article',
            ];
            foreach ($optional as $field => $value) {
                if ($CI->db->field_exists($field, $articlesTable)) { $articleData[$field] = $value; }
            }
            $CI->db->insert($articlesTable, $articleData);
        }

        update_option('training_manual_customer_library_seeded', '1');
    }
}
