<?php
// 确保在 WordPress 环境中运行
if (!defined('ABSPATH')) {
    exit;
}

// 包含企业微信 API 类文件
require_once plugin_dir_path(__FILE__) . 'qywx-api.php';

/**
 * 生成 24 位强密码
 *
 * @return string 生成的 24 位密码
 */
function qywx_generate_password() {
    return bin2hex(random_bytes(16));
}

/**
 * 为用户设置并发送企业微信登录密码
 *
 * @param int $user_id WordPress 用户 ID
 * @return bool 密码设置和发送成功返回 true，失败返回 false
 */
function qywx_set_and_send_password($user_id) {
    $password = qywx_generate_password();

    // 更新用户元数据，存储生成的密码
    if (!update_user_meta($user_id, 'qywx_password', $password)) {
        return false;
    }

    $user = get_user_by('ID', $user_id);
    if (!$user) {
        return false;
    }

    $message = "您的企业微信登录初始密码为：{$password}，请及时修改。";

    // 尝试通过企业微信 API 发送消息
    $api = new QYWX_API();
    $touser = $user->user_login; // 假设 user_login 对应企业微信用户 ID
    $msgtype = 'text';
    $text_message = array(
        'content' => $message
    );

    if ($api->send_message($touser, $msgtype, $text_message)) {
        return true;
    }

    // 如果企业微信 API 发送失败，尝试通过 WordPress 邮件发送
    $subject = '您的企业微信登录密码';
    return wp_mail($user->user_email, $subject, $message);
}