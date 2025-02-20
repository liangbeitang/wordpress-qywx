<?php
// 确保在 WordPress 环境中运行
if (!defined('ABSPATH')) {
    exit;
}

// 包含必要的类文件
require_once plugin_dir_path(dirname(__FILE__, 3)) . 'includes/core/class-qywx-user.php';

// 创建 QYWX_User 类的实例
$user_handler = new QYWX_User();

// 处理同步请求
if (isset($_POST['sync'])) {
    $result = $user_handler->sync_users();
    if ($result) {
        $message = '<div class="notice notice-success is-dismissible"><p>企业微信部门架构和用户信息同步成功！</p></div>';
    } else {
        $message = '<div class="notice notice-error is-dismissible"><p>企业微信部门架构和用户信息同步失败，请检查配置或网络连接。</p></div>';
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>企业微信数据同步</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@wordpress/components@latest/build-style/style.css">
</head>
<body>
    <div class="wrap">
        <h1>企业微信部门架构和用户信息同步</h1>
        <?php if (isset($message)) {
            echo $message;
        } ?>
        <p>点击下面的按钮可以手动同步企业微信的部门架构和用户信息到 WordPress。</p>
        <form method="post">
            <input type="submit" name="sync" class="button button-primary" value="立即同步">
        </form>
        <p class="description">注意：同步操作可能会消耗一定的时间和系统资源，尤其是在企业微信部门架构较大的情况下。建议通过 WP - Cron 每日自动执行此操作以保持数据最新。</p>
    </div>
</body>
</html>