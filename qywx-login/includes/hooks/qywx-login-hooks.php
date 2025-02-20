<?php
// 确保在 WordPress 环境中运行
if (!defined('ABSPATH')) {
    exit;
}

// 包含必要的类文件
require_once plugin_dir_path(dirname(__FILE__, 2)) . '/includes/core/class-qywx-auth.php';

/**
 * 重定向未登录用户到企业微信登录页面
 * 这里不再进行自动重定向，仅在用户点击链接时才会跳转
 */
function qywx_redirect_unauthenticated_to_login() {
    // 此函数不再执行重定向逻辑
}
add_action('template_redirect', 'qywx_redirect_unauthenticated_to_login');

// 添加企业微信登录链接到 WordPress 登录页面
function add_qywx_login_link() {
    // 包含必要的类文件
    require_once plugin_dir_path(dirname(__FILE__, 2)) . '/includes/core/class-qywx-auth.php';

    $auth = new QYWX_Auth();
    $qr_url = $auth->generate_qr_url();

    // 输出企业微信登录链接
    echo '&nbsp;&nbsp;&nbsp;<a href="' . esc_url($qr_url) . '" style="margin-right: 10px;">企业微信登录</a>';
}
add_action('login_form', 'add_qywx_login_link');
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