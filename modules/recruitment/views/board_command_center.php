<?php defined('BASEPATH') or exit('No direct script access allowed');
$counts = $command_center['counts'];
$stages = [1 => 'application', 2 => 'potential', 3 => 'interview', 4 => 'won_interview', 5 => 'send_offer', 6 => 'elect', 7 => 'non_elect', 8 => 'unanswer', 9 => 'transferred', 10 => 'freedom'];
?>
<section class="rec-command-center" aria-label="Talent command center">
    <div class="rec-command-heading"><div><p>RECRUITMENT &amp; TALENT ACQUISITION</p><h2>Talent Command Center</h2></div><nav aria-label="Recruitment workspace"><a class="btn btn-default" href="<?= admin_url('recruitment/candidate_profile'); ?>">Candidate Pipeline</a><a class="btn btn-default" href="<?= admin_url('recruitment/recruitment_campaign'); ?>">Job Requisitions</a><a class="btn btn-default" href="<?= admin_url('recruitment/evaluation_form'); ?>">Evaluations</a></nav></div>
    <div class="rec-talent-ribbon">
        <div><strong><?= array_sum(array_slice($counts, 0, 5)); ?></strong><span>Active Candidates · stages 1–5</span></div>
        <div><strong><?= (int) ($cp_count['upcomming_intv'] ?? 0); ?></strong><span>Upcoming Interviews</span></div>
        <div><strong><?= (int) $counts[6]; ?></strong><span><?= html_escape(_l('elect')); ?></span></div>
        <div><strong><?= (int) $counts[9]; ?></strong><span><?= html_escape(_l('transferred')); ?></span></div>
    </div>
    <h3>Candidate Progression</h3><p class="text-muted">Current totals in your configured recruitment workflow.</p>
    <div class="rec-stage-deck">
    <?php $total = array_sum($counts); foreach ($stages as $status => $label): $percent = $total ? round($counts[$status] * 100 / $total) : 0; ?>
        <div class="rec-stage-card"><span><?= html_escape(_l($label)); ?></span><strong><?= (int) $counts[$status]; ?></strong><div class="rec-stage-track" role="progressbar" aria-label="<?= html_escape(_l($label)); ?>" aria-valuenow="<?= $percent; ?>" aria-valuemin="0" aria-valuemax="100"><i style="width:<?= $percent; ?>%"></i></div></div>
    <?php endforeach; ?>
    </div>
    <div class="rec-recent-panel"><h3>Recent Applicants</h3><div class="table-responsive"><table class="table table-hover"><thead><tr><th>Candidate</th><th>Status</th><th>Profile</th></tr></thead><tbody>
    <?php foreach ($command_center['recent'] as $applicant): ?>
        <tr><td><strong><?= html_escape(trim($applicant['candidate_name'] . ' ' . $applicant['last_name'])); ?></strong><small class="display-block text-muted"><?= html_escape($applicant['candidate_code']); ?></small></td><td><?= get_status_candidate((int) $applicant['status']); ?></td><td><a class="btn btn-default btn-sm" href="<?= admin_url('recruitment/candidate/' . (int) $applicant['id']); ?>">Open Candidate</a></td></tr>
    <?php endforeach; ?>
    <?php if (!$command_center['recent']): ?><tr><td colspan="3">No applicants found.</td></tr><?php endif; ?>
    </tbody></table></div></div>
</section>
