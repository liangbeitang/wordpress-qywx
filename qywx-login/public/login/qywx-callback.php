<?php
// 确保在 WordPress 环境中运行
if (!defined('ABSPATH')) {
    exit;
}

// 包含必要的类文件
require_once plugin_dir_path(dirname(__FILE__, 3)) . 'includes/core/class-qywx-auth.php';
require_once plugin_dir_path(dirname(__FILE__, 3)) . 'includes/core/class-qywx-user.php';

// 创建 QYWX_Auth 和 QYWX_User 类的实例
$auth = new QYWX_Auth();
$user_handler = new QYWX_User();

// 处理企业微信回调
function qywx_handle_callback() {
    global $auth, $user_handler;

    // 检查是否有授权码和状态参数
    if (!isset($_GET['code']) ||!isset($_GET['state'])) {
        wp_redirect(home_url());
        exit;
    }

    $code = sanitize_text_field($_GET['code']);
    $state = sanitize_text_field($_GET['state']);

    // 验证状态参数，防止 CSRF 攻击
    if (!wp_verify_nonce($state, 'qywx_login')) {
        wp_die('无效的状态参数，请重试。');
    }

    // 获取访问令牌
    $access_token = $auth->get_access_token();
    if (!$access_token) {
        wp_die('无法获取访问令牌，请稍后重试。');
    }

    // 根据授权码获取用户信息
    $user_info = $auth->get_user_info($access_token, $code);
    if (!$user_info) {
        wp_die('无法获取用户信息，请稍后重试。');
    }

    // 创建或更新 WordPress 用户
    $user_id = $user_handler->create_or_update_user($user_info);
    if (!$user_id) {
        wp_die('用户创建或更新失败，请稍后重试。');
    }

    // 设置用户登录状态
    wp_set_auth_cookie($user_id);
    wp_redirect(home_url());
    exit;
}

// 执行回调处理函数
qywx_handle_callback();