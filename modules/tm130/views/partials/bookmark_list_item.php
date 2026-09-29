<?php defined('BASEPATH') or exit('No direct script access allowed');
$article_id = isset($article['id']) ? (int) $article['id'] : 0;
$title = isset($article['title']) ? $article['title'] : 'Untitled Article';
$description = isset($article['description']) ? trim(strip_tags($article['description'])) : '';
if ($description === '') { $description = 'No description available.'; }
$book_name = isset($article['book_name']) ? $article['book_name'] : 'Unassigned Manual';
$creator_name = trim((string)($article['creator_name'] ?? ''));
if ($creator_name === '') { $creator_name = trim((string)($article['author_fullname'] ?? ($article['author_name'] ?? ''))); }
if ($creator_name === '') { $creator_name = 'Unknown Creator'; }
$language_code = isset($article['language_code']) ? strtoupper($article['language_code']) : 'EN';
$content_kind_raw = isset($article['content_kind']) && $article['content_kind'] !== '' ? $article['content_kind'] : (isset($article['type']) && $article['type'] !== '' ? $article['type'] : 'Article');
$content_kind = ucwords(str_replace(['_', '-'], ' ', $content_kind_raw));
$is_publish = isset($article['is_publish']) && (int) $article['is_publish'] === 1;
?>
<div class="training-manual-bookmark-item">
    <div class="training-manual-bookmark-main">
        <div class="training-manual-bookmark-icon"><i class="fa fa-bookmark"></i></div>
        <div class="training-manual-bookmark-content">
            <h4><a href="<?php echo admin_url('training_manual/articles/show/' . $article_id); ?>"><?php echo html_escape($title); ?></a></h4>
            <p><?php echo html_escape(mb_strimwidth($description, 0, 220, '...')); ?></p>
            <div class="training-manual-bookmark-meta">
                <span><i class="fa fa-folder-open"></i> <?php echo html_escape($book_name); ?></span>
                <span><i class="fa fa-user"></i> <?php echo html_escape($creator_name); ?></span>
                <span><i class="fa fa-language"></i> <?php echo html_escape($language_code); ?></span>
                <span><i class="fa fa-video-camera"></i> <?php echo html_escape($content_kind); ?></span>
                <span class="<?php echo $is_publish ? 'text-success' : 'text-muted'; ?>"><i class="fa fa-circle"></i> <?php echo $is_publish ? 'Published' : 'Draft'; ?></span>
            </div>
        </div>
    </div>
    <div class="training-manual-bookmark-actions">
        <a class="btn btn-default btn-xs" href="<?php echo admin_url('training_manual/articles/show/' . $article_id); ?>"><i class="fa fa-eye"></i> View</a>
        <?php if(has_permission('training_manual_articles','','edit')){ ?>
            <a class="btn btn-info btn-xs" href="<?php echo admin_url('training_manual/articles/article/' . $article_id); ?>"><i class="fa fa-pencil"></i> Edit</a>
            <a class="btn btn-default btn-xs" href="<?php echo admin_url('training_manual/articles/clone_article/' . $article_id); ?>" onclick="return confirm('Clone this training article?');"><i class="fa fa-copy"></i> Clone</a>
        <?php } ?>
        <button type="button" class="btn btn-warning btn-xs training_manual-btn-bookmark training-manual-bookmark-btn training_manual-bookmark-on" data-id="<?php echo $article_id; ?>" title="Remove Bookmark"><i class="fa fa-bookmark"></i></button>
    </div>
</div>
