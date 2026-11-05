<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<h4 class="customer-profile-group-heading">
    <?= e(_l('customer_statement_for', get_company_name($client->userid))); ?>
</h4>
<div class="row">
    <div class="col-md-4">
        <?php $this->load->view('admin/clients/groups/_statement_period_select', ['onChange' => 'render_customer_statement()']); ?>
    </div>
    <div class="col-md-8 col-xs-12">
        <div class="text-right _buttons pull-right tw-space-x-1">

            <a href="#" id="statement_print" target="_blank" class="btn btn-default btn-with-tooltip"
                data-toggle="tooltip"
                title="<?= _l('print'); ?>"
                data-placement="bottom">
                <i class="fa fa-print" aria-hidden="true"></i> <?= e(_l('print')); ?>
            </a>

            <a href="" id="statement_pdf" class="btn btn-default btn-with-tooltip" data-toggle="tooltip"
                title="<?= _l('view_pdf'); ?>"
                data-placement="bottom">
                <i class="fa-regular fa-file-pdf" aria-hidden="true"></i> <?= e(_l('view_pdf')); ?>
            </a>

            <a href="#" class="btn btn-default btn-with-tooltip" data-toggle="modal"
                data-target="#statement_send_to_client"><span data-toggle="tooltip"
                    data-title="<?= _l('send_to_email'); ?>"
                    data-placement="bottom"><i class="fa-regular fa-envelope" aria-hidden="true"></i> <?= e(_l('send_to_email')); ?></span></a>
        </div>
    </div>

    <div class="clearfix"></div>

    <div class="col-md-12 mtop15">
        <div class="row">
            <div class="col-md-12">
                <address class="text-right">
                    <?= format_organization_info(); ?>
                </address>
            </div>
            <div class="col-md-12">
                <hr />
            </div>
            <div class="col-md-7">
                <address>
                    <p class="tw-font-bold">
                        <?= _l('statement_bill_to'); ?>:
                    </p>
                    <?= format_customer_info($client, 'statement', 'billing'); ?>
                </address>
            </div>
            <div id="statement-html"></div>
        </div>
    </div>
</div>
<div class="modal fade email-template"
    data-editor-id=".<?= 'tinymce-' . $client->userid; ?>"
    id="statement_send_to_client" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <?= form_open('', ['id' => 'send_statement_form']); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">
                    <?= _l('account_summary'); ?>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <?php
                            if ($template_disabled) {
                                echo '<div class="alert alert-danger">';
                                echo 'The email template <b><a href="' . admin_url('emails/email_template/' . $template_id) . '" target="_blank" class="alert-link">' . $template_system_name . '</a></b> is disabled. Click <a href="' . admin_url('emails/email_template/' . $template_id) . '" class="alert-link" target="_blank">here</a> to enable the email template in order to be sent successfully.';
                                echo '</div>';
                            }
$selected = [];

foreach ($contacts as $contact) {
    if (has_contact_permission('invoices', $contact['id'])) {
        array_push($selected, $contact['id']);
    }
}

if (count($selected) == 0) {
    echo '<p class="text-danger">' . _l('sending_email_contact_permissions_warning', _l('customer_permission_invoice')) . '</p><hr />';
}

echo render_select('send_to[]', $contacts, ['id', 'email', 'firstname,lastname'], 'invoice_estimate_sent_to_email', $selected, ['multiple' => true], [], '', '', false);
?>
                        </div>
                        <?= render_input('cc', 'CC'); ?>
                        <hr />
                        <h5 class="bold">
                            <?= _l('invoice_send_to_client_preview_template'); ?>
                        </h5>
                        <hr />
                        <button type="button" class="btn btn-default" id="statement-preview-toggle" aria-expanded="false" aria-controls="statement-pdf-preview"><?= e(_l('view_pdf')); ?></button>
                        <iframe id="statement-pdf-preview" hidden title="<?= e(_l('view_pdf')); ?>" style="width:100%;height:420px;border:1px solid #e2e8f0;margin:12px 0"></iframe>
                        <?= render_textarea('email_template_custom', '', $template->message, [], [], '', 'tinymce-' . $client->userid); ?>
                        <?= form_hidden('template_name', $template_name); ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default"
                    data-dismiss="modal"><?= _l('close'); ?></button>
                <button type="submit" autocomplete="off"
                    data-loading-text="<?= _l('wait_text'); ?>"
                    class="btn btn-primary"><?= _l('send'); ?></button>
            </div>
        </div>
        <?= form_close(); ?>
    </div>
</div>
<?php hooks()->add_action('app_admin_footer', 'parse_customer_statement_html');
function parse_customer_statement_html()
{ ?>
<script>
    $(function() {
        render_customer_statement();
        $('#statement-preview-toggle').on('click', function () {
            var frame = $('#statement-pdf-preview');
            var expanded = $(this).attr('aria-expanded') === 'true';
            if (expanded) { frame.prop('hidden', true); $(this).attr('aria-expanded', 'false'); return; }
            var source;
            try { source = new URL($('#statement_print').attr('href'), window.location.href); } catch (_) { return; }
            if (source.origin !== window.location.origin || !source.pathname.endsWith('/clients/statement_pdf')) { return; }
            frame.attr('src', source.href).prop('hidden', false);
            $(this).attr('aria-expanded', 'true');
        });
        $('#statement_send_to_client').on('hidden.bs.modal', function () {
            $('#statement-pdf-preview').prop('hidden', true).removeAttr('src');
            $('#statement-preview-toggle').attr('aria-expanded', 'false');
        });
        $('#send_statement_form').on('submit', function (event) {
            var form = this;
            if (event.isDefaultPrevented() || (form.checkValidity && !form.checkValidity())) { return; }
            if ($(form).data('sending')) { event.preventDefault(); return; }
            $(form).data('sending', true);
            window.setTimeout(function () {
                if (event.isDefaultPrevented()) { $(form).removeData('sending'); }
                else {
                    $(form).attr('aria-busy', 'true');
                    $(form).find('button[type="submit"]').button('loading');
                }
            }, 0);
        });

    });

    function render_customer_statement() {
        var $statementPeriod = $('#range');
        var value = $statementPeriod.selectpicker('val');
        var period = new Array();
        if (value != 'period') {
            period = JSON.parse(value);
        } else {
            period[0] = $('input[name="period-from"]').val();
            period[1] = $('input[name="period-to"]').val();

            if (period[0] == '' || period[1] == '') {
                return false;
            }
        }

        var statementUrl = admin_url + 'clients/statement';
        var statementUrlParams = new Array();

        statementUrlParams['customer_id'] = customer_id;
        statementUrlParams['from'] = period[0];
        statementUrlParams['to'] = period[1];
        statementUrl = buildUrl(statementUrl, statementUrlParams);

        $.get(statementUrl, function(response) {
            $('#statement-html').html(response.html);

            $('#statement_pdf').attr('href', buildUrl(admin_url + 'clients/statement_pdf', statementUrlParams));
            $('#send_statement_form').attr('action', buildUrl(admin_url + 'clients/send_statement',
                statementUrlParams));

            statementUrlParams['print'] = true;
            $('#statement_print').attr('href', buildUrl(admin_url + 'clients/statement_pdf',
                statementUrlParams));
        }, 'json').fail(function(response) {
            alert_float('danger', response.responseText);
        });
    }
</script>
<?php } ?>