<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>Download Page</title>
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        body {
            background: #60a7f1;
            background: linear-gradient(to right, #8db9f3, #d1e2ee);
        }
    </style>
</head>
<body>
<div class="text-center">
    <i class="fa fa-download fa-5x"></i>
    <?php
    if (isset($is_file_destroyed) && $is_file_destroyed == 1){
    ?>
        <div class="alert alert-info text-center mt-3" role="alert">
            <?php echo _l('qs_file_destroyed_message'); ?>
        </div>
    <?php
        die;
    }
    ?>
    <h2>Your download is ready</h2>
    <?php if ((!empty($file_data->password) && !isset($password_verified)) || $password_verified == '0') { ?>
        <form action="<?php echo base_url('quick_share/download/file/'.$file_data->file_key); ?>" method="get" class="mt-3">
            <div class="form-group">
                <label for="password">Enter Password:</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    <?php } else { ?>
        <p>This link contains the following files:</p>
        <?php if (!is_null($file_data->file_message) && !empty($file_data->file_message)) { ?>
            <div class="alert alert-info text-center mt-3" role="alert">
                <?php echo $file_data->file_message; ?>
            </div>
        <?php } ?>
        <ul class="list-group">
            <li class="list-group-item"><?php echo $file_data->file_path; ?> - <?php echo  bytesToSize('', $file_data->file_size); ?></li>
        </ul>
        <?php
        $password = '';
        if (!empty($file_data->password)) {
            $password = '?password='.$_GET['password'];
        }
        ?>
        <a onclick="refreshPage()" href="<?php echo base_url('quick_share/download/downloadFile/'.$file_data->file_key.$password) ?>" class="btn btn-primary mt-3">Download</a>
    <?php } ?>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js"></script>
<script>
    function refreshPage() {
        setTimeout(function() {
            location.reload();
        }, 2000); // 2000 milliseconds = 2 seconds
    }
</script>
</body>
</html>