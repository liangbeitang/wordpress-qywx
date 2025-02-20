<?php
// 确保在 WordPress 环境中运行
if (!defined('ABSPATH')) {
    exit;
}

// 包含必要的类文件
require_once plugin_dir_path(dirname(__FILE__, 3)) . 'includes/core/class-qywx-auth.php';

// 创建 QYWX_Auth 类的实例
$auth = new QYWX_Auth();

// 生成企业微信扫码登录二维码的 URL
$qr_url = $auth->generate_qr_url();

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>企业微信扫码登录</title>
    <!-- 可以在这里引入自定义的 CSS 文件，用于美化页面 -->
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding-top: 50px;
        }
        h1 {
            color: #333;
        }
        .qr-code-container {
            margin-top: 20px;
        }
        .qr-code-container img {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
    <h1>请使用企业微信扫码登录</h1>
    <div class="qr-code-container">
        <!-- 这里显示企业微信扫码登录的二维码 -->
        <img src="<?php echo esc_url($qr_url); ?>" alt="企业微信扫码登录二维码">
    </div>
    <p>扫码后，您将使用企业微信账号登录本系统。</p>
</body>
</html>