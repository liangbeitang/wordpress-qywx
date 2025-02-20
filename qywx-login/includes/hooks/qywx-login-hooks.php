<?php
// 确保在 WordPress 环境中运行
if (!defined('ABSPATH')) {
    exit;
}

// 包含必要的类文件
require_once plugin_dir_path(dirname(__FILE__, 2)) . 'core/class-qywx-auth.php';

/**
 * 重定向未登录用户到企业微信登录页面
 */
function qywx_redirect_unauthenticated_to_login() {
    if (!is_user_logged_in() &&!is_admin() &&!is_custom_login_page()) {
        $auth = new QYWX_Auth();
        $qr_url = $auth->generate_qr_url();
        wp_redirect($qr_url);
        exit;
    }
}
add_action('template_redirect', 'qywx_redirect_unauthenticated_to_login');

/**
 * 检查当前页面是否为自定义登录页面
 * 这里可以根据实际情况修改自定义登录页面的判断逻辑
 * 
 * @return bool 如果是自定义登录页面返回 true，否则返回 false
 */
function is_custom_login_page() {
    global $pagenow;
    // 示例：如果当前页面是登录页面（如 wp-login.php），返回 true
    return in_array($pagenow, array('wp-login.php'));
}