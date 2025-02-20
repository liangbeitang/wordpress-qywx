<?php
/**
 * Plugin Name: 企业微信登录插件
 * Plugin URI: 你的插件介绍页面地址
 * Description: 实现企业微信扫码登录 WordPress 功能
 * Version: 1.0
 * Author: 梁北棠
 * Author URI: www.liangbeitang.com
 * License: GPL2
 */

// 确保在 WordPress 环境中运行
if (!defined('ABSPATH')) {
    exit;
}

// 定义插件的基础路径和 URL
define('QYWX_LOGIN_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('QYWX_LOGIN_PLUGIN_URL', plugin_dir_url(__FILE__));

// 加载自动加载器（如果使用 Composer 或自定义自动加载机制）
// require_once QYWX_LOGIN_PLUGIN_DIR . 'vendor/autoload.php';

// 加载必要的类文件
require_once QYWX_LOGIN_PLUGIN_DIR . 'includes/core/class-qywx-auth.php';
require_once QYWX_LOGIN_PLUGIN_DIR . 'includes/core/class-qywx-user.php';
require_once QYWX_LOGIN_PLUGIN_DIR . 'includes/hooks/qywx-login-hooks.php';
require_once QYWX_LOGIN_PLUGIN_DIR . 'includes/hooks/qywx-password-hooks.php';
require_once QYWX_LOGIN_PLUGIN_DIR . 'includes/utils/qywx-api.php';
require_once QYWX_LOGIN_PLUGIN_DIR . 'includes/utils/qywx-password.php';

// 加载后台管理相关文件
require_once QYWX_LOGIN_PLUGIN_DIR . 'admin/qywx-admin.php';

// 加载语言文件
function qywx_login_load_textdomain() {
    load_plugin_textdomain('qywx-login', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'qywx_login_load_textdomain');

// 激活插件时的操作
function qywx_login_plugin_activate() {
    // 可以在这里进行一些初始化操作，如创建自定义表等
}
register_activation_hook(__FILE__, 'qywx_login_plugin_activate');

// 停用插件时的操作
function qywx_login_plugin_deactivate() {
    // 可以在这里进行一些清理操作，如删除自定义表等
}
register_deactivation_hook(__FILE__, 'qywx_login_plugin_deactivate');

// 注册 AJAX 处理函数
add_action('wp_ajax_qywx_get_login_qr_url', 'qywx_get_login_qr_url_callback');
add_action('wp_ajax_nopriv_qywx_get_login_qr_url', 'qywx_get_login_qr_url_callback');

function qywx_get_login_qr_url_callback() {
    $auth = new QYWX_Auth();
    $qr_url = $auth->generate_qr_url();

    if ($qr_url) {
        wp_send_json_success(array(
            'qr_url' => $qr_url
        ));
    } else {
        wp_send_json_error();
    }
}

// 前端脚本和样式的加载
function qywx_login_enqueue_scripts() {
    wp_enqueue_script('qywx-login-script', QYWX_LOGIN_PLUGIN_URL . 'assets/js/qywx-login.js', array('jquery'), '1.0', true);
    wp_enqueue_style('qywx-login-style', QYWX_LOGIN_PLUGIN_URL . 'assets/css/qywx-login.css', array(), '1.0');

    wp_localize_script('qywx-login-script', 'qywxLoginAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php')
    ));
}
add_action('wp_enqueue_scripts', 'qywx_login_enqueue_scripts');