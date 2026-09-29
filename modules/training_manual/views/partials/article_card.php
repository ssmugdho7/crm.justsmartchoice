<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$article_id = isset($article['id']) ? (int) $article['id'] : 0;
$title = isset($article['title']) ? $article['title'] : 'Untitled Article';
$description = isset($article['description']) ? $article['description'] : '';
$book_name = isset($article['book_name']) ? $article['book_name'] : 'Unassigned Manual';
$creator_name = trim((string)($article['creator_name'] ?? ($article['author_fullname'] ?? ($article['author_name'] ?? ''))));
$creator_staff_id = (int)($article['creator_staff_id'] ?? ($article['author_id'] ?? 0));
if ($creator_name === '') { $creator_name = 'Unknown Creator'; }
$language_code = isset($article['language_code']) ? strtoupper($article['language_code']) : 'EN';
$style_preset = isset($article['style_preset']) ? ucwords(str_replace('_', ' ', $article['style_preset'])) : 'Smart Choice';
$is_publish = isset($article['is_publish']) && (int) $article['is_publish'] === 1;
$thumbnail = isset($article['thumbnail']) ? $article['thumbnail'] : '';
$thumb_url = $thumbnail !== '' ? base_url('uploads/training_manual/' . $thumbnail) : '';
?>
<div class="training-manual-article-card panel_s">
    <div class="panel-body training-manual-article-card-body">
        <div class="training-manual-card-topline">
            <label class="checkbox-inline training-manual-card-check">
                <input type="checkbox" name="ids[]" value="<?php echo $article_id; ?>">
            </label>
            <span class="training-manual-status-badge <?php echo $is_publish ? 'published' : 'draft'; ?>">
                <?php echo $is_publish ? 'Published' : 'Draft'; ?>
            </span>
        </div>

        <div class="training-manual-card-media">
            <?php if($thumb_url !== ''){ ?>
                <img src="<?php echo $thumb_url; ?>" alt="<?php echo html_escape($title); ?>">
            <?php } else { ?>
                <div class="training-manual-card-placeholder"><i class="fa fa-book"></i></div>
            <?php } ?>
        </div>

        <h4 class="training-manual-card-title">
            <a href="<?php echo admin_url('training_manual/articles/article/' . $article_id); ?>"><?php echo html_escape($title); ?></a>
        </h4>
        <p class="training-manual-card-description"><?php echo html_escape(mb_strimwidth(strip_tags($description), 0, 145, '...')); ?></p>

        <div class="training-manual-card-meta">
            <div><i class="fa fa-folder-open"></i> <?php echo html_escape($book_name); ?></div>
            <div class="training-manual-creator-meta">
                <?php if ($creator_staff_id > 0 && function_exists('staff_profile_image')) { echo staff_profile_image($creator_staff_id, ['img-circle', 'training-manual-creator-avatar'], 'small'); } else { ?><i class="fa fa-user"></i><?php } ?>
                <span><?php echo html_escape($creator_name); ?></span>
            </div>
            <div><i class="fa fa-language"></i> <?php echo html_escape($language_code); ?> · <?php echo html_escape($style_preset); ?></div>
            <div><i class="fa fa-users"></i> <?php echo html_escape($audience); ?></div>
        </div>

        <div class="training-manual-card-actions">
            <a class="btn btn-default btn-xs" href="<?php echo admin_url('training_manual/articles/show/' . $article_id); ?>"><i class="fa fa-eye"></i> View</a>
            <?php if(has_permission('training_manual_articles','','edit')){ ?>
                <a class="btn btn-info btn-xs" href="<?php echo admin_url('training_manual/articles/article/' . $article_id); ?>"><i class="fa fa-pencil"></i> Edit</a>
                <a class="btn btn-default btn-xs" href="<?php echo admin_url('training_manual/articles/clone_article/' . $article_id); ?>"><i class="fa fa-copy"></i> Clone</a>
                <button type="button" class="btn btn-default btn-xs training-manual-change-creator" data-article-id="<?php echo $article_id; ?>" data-current-creator-id="<?php echo $creator_staff_id; ?>"><i class="fa fa-user-plus"></i> Creator</button>
            <?php } ?>
            <button type="button" class="btn btn-warning btn-xs training-manual-bookmark-btn" data-article-id="<?php echo $article_id; ?>" data-current-creator-id="<?php echo $creator_staff_id; ?>"><i class="fa fa-bookmark"></i></button>
        </div>
    </div>
</div>
