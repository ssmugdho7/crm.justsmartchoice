<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content"><div class="panel_s"><div class="panel-body">
    <h4>My Google Calendar</h4>
    <p>This connection belongs to your CRM staff account. Other staff connect their own Google accounts and calendars.</p>
    <p>Use a separate private calendar that you own in Google Calendar. In its Settings, open <strong>Integrate calendar</strong> and copy the Calendar ID ending in <code>@group.calendar.google.com</code>.</p>
    <p>Appointments sync one way from CRM to this calendar in New York time. Customer invitations, Google email updates, reminders, and imports from personal calendars are disabled.</p>
    <?= form_open(site_url('appointly/google/auth/settings')); ?>
        <div class="form-group">
            <label for="my-google-calendar-id">My private Calendar ID</label>
            <input class="form-control" type="text" id="my-google-calendar-id" name="calendar_id" value="<?= html_escape($calendar_id) ?>" maxlength="191" required <?= $connected ? 'readonly' : '' ?>>
        </div>
        <?php if (!$connected) { ?><button class="btn btn-info" type="submit">Save my calendar</button><?php } ?>
    <?= form_close(); ?>
    <hr>
    <?php if ($connected) { ?>
        <p><strong>Connected.</strong> Your Google authorization is saved privately. Disconnect before switching accounts or calendars; existing Google events are retained.</p>
        <?= form_open(site_url('appointly/google/auth/logout')); ?>
            <button class="btn btn-default" type="submit">Disconnect my Google Calendar</button>
        <?= form_close(); ?>
    <?php } elseif ($calendar_id !== '') { ?>
        <a class="btn btn-info" href="<?= site_url('appointly/google/auth/login') ?>">Connect my Google account</a>
    <?php } ?>
    <hr><a href="<?= admin_url('appointly/appointments') ?>">Back to Appointments</a>
</div></div></div></div>
<?php init_tail(); ?></body></html>
