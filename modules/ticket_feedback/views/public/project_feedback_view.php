<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title); ?></title>

    <!-- Bootstrap & FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Meta Tags for Social Sharing -->
    <meta property="og:title" content="Project Feedback - <?= htmlspecialchars($project_name); ?>">
    <meta property="og:description" content="<?= htmlspecialchars($project_description); ?>">
    <meta property="og:url" content="<?= current_url(); ?>">
    <meta property="og:type" content="website">

    <style>
        body {
            background-color: #f8f9fa;
        }

        .rating-stars {
            font-size: 24px;
            color: #FFD700;
        }

        .share-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .share-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 12px;
            font-size: 14px;
            font-weight: bold;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            transition: 0.3s;
        }

        .facebook { background: #3b5998; }
        .twitter { background: #1DA1F2; }
        .linkedin { background: #0077b5; }
        .whatsapp { background: #25D366; }

        .share-button i { margin-right: 5px; }
    </style>
</head>

<body>
    <div class="container mt-5">
        <div class="card shadow-lg border-0">
            <div class="card-header bg-primary text-white text-center">
                <h3 class="m-0"><i class="fa fa-comments"></i> Project Feedback</h3>
            </div>
            <div class="card-body text-center">
                <!-- Project Details -->
                <h4 class="text-center"><?= htmlspecialchars($project_name); ?></h4>
                <p class="text-muted"><?= htmlspecialchars($project_description); ?></p>
                <p><strong>Customer:</strong> <?= htmlspecialchars($customer_name); ?></p>

                <!-- Star Rating -->
                <p>
                    <strong>Rating:</strong>
                    <span class="rating-stars">
                        <?= str_repeat('<i class="fas fa-star"></i>', $feedback['rating'] ?? 0); ?>
                        <?= str_repeat('<i class="far fa-star"></i>', 5 - ($feedback['rating'] ?? 0)); ?>
                    </span>
                    (<?= $feedback['rating'] ?? 'No rating'; ?> ⭐)
                </p>

                <!-- Feedback Details -->
                <p><strong>Satisfaction Level:</strong> <?= ($feedback['satisfaction_level'] ?? 0) ? '😊 Satisfied' : '😞 Not Satisfied'; ?></p>
                <p><strong>Communication Quality:</strong> <?= ($feedback['communication_quality'] ?? 0) ? '📞 Good' : '❌ Poor'; ?></p>
                <p><strong>Comments:</strong> <?= !empty($feedback['comments']) ? htmlspecialchars($feedback['comments']) : 'No comments provided.'; ?></p>

                <!-- Social Share Buttons -->
                <div class="share-buttons">
                    <?php
                    $shareText = urlencode("📢 Project Feedback!\n\n💼 Project: {$project_name}\n⭐ Rating: {$feedback['rating']} Stars\n💬 Comments: {$feedback['comments']}\n👉 Read more: " . current_url());
                    $encodedUrl = urlencode(current_url());
                    ?>

                    <!-- Facebook -->
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $encodedUrl; ?>" target="_blank" class="share-button facebook">
                        <i class="fab fa-facebook"></i> Share
                    </a>

                    <!-- Twitter -->
                    <a href="https://twitter.com/intent/tweet?text=<?= $shareText; ?>" target="_blank" class="share-button twitter">
                        <i class="fab fa-twitter"></i> Tweet
                    </a>

                    <!-- LinkedIn -->
                    <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?= $encodedUrl; ?>&title=<?= urlencode('Project Feedback'); ?>&summary=<?= $shareText; ?>"
                        target="_blank" class="share-button linkedin">
                        <i class="fab fa-linkedin"></i> Share
                    </a>

                    <!-- WhatsApp -->
                    <a href="https://api.whatsapp.com/send?text=<?= $shareText; ?>" target="_blank" class="share-button whatsapp">
                        <i class="fab fa-whatsapp"></i> Share
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
