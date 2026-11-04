<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content"><div class="panel_s"><div class="panel-body">
    <h4>My Google Calendar</h4>
    <h5>Company team calendar</h5>
    <?php if ($team_enabled) { ?>
        <p><?= $team_ready ? 'Team Calendar is available. Use the Google Calendar option when creating an appointment; no individual Google connection is required.' : 'Team Calendar is enabled but the owner connection is unavailable, or your role does not permit appointment synchronization.' ?></p>
        <p>The calendar is shared through CRM with authorized staff, not published on the internet. Each staff member can change only their own synced events.</p>
    <?php } else { ?><p>Team Calendar is disabled. Individual connections continue to work.</p><?php } ?>
    <?php if (is_admin()) { ?>
        <?= form_open(site_url('appointly/google/auth/settings')); ?>
            <input type="hidden" name="action" value="team">
            <div class="checkbox"><input type="checkbox" id="team-calendar-enabled" name="team_enabled" value="1" <?= $team_enabled ? 'checked' : '' ?>><label for="team-calendar-enabled">Enable shared company calendar</label></div>
            <div class="form-group"><label for="team-owner-id">Company calendar owner staff ID</label><input class="form-control" type="number" min="1" id="team-owner-id" name="team_owner_id" value="<?= $team_owner_id ?: '' ?>"></div>
            <p>The owner saves a dedicated company Calendar ID below and connects Google once. Staff with appointment create/edit permissions then sync through that connection. Enabling this does not grant access to the owner's personal calendars.</p>
            <button class="btn btn-info" type="submit">Save team calendar settings</button>
        <?= form_close(); ?>
    <?php } ?>
    <hr><h5>My individual connection</h5>
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
