<?php
// 确保在 WordPress 环境中运行
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 注册企业微信登录插件的后台菜单
 */
function qywx_admin_menu() {
    // 添加主菜单页面
    add_menu_page(
        __('企业微信登录设置', 'qywx-login'), // 页面标题
        __('企业微信登录', 'qywx-login'), // 菜单标题
        'manage_options', // 访问该菜单所需的权限
        'qywx-settings', // 菜单的唯一标识符
        'qywx_settings_page', // 显示设置页面的回调函数
        'dashicons-admin-generic', // 菜单图标
        6 // 菜单在后台菜单中的位置
    );

    // 添加子菜单页面 - 部门架构同步
    add_submenu_page(
        'qywx-settings', // 父菜单的唯一标识符
        __('部门架构同步', 'qywx-login'), // 页面标题
        __('同步部门', 'qywx-login'), // 菜单标题
        'manage_options', // 访问该菜单所需的权限
        'qywx-sync', // 子菜单的唯一标识符
        'qywx_sync_page' // 显示同步页面的回调函数
    );
}
add_action('admin_menu', 'qywx_admin_menu');

/**
 * 显示企业微信登录设置页面
 */
function qywx_settings_page() {
    require_once plugin_dir_path(__FILE__) . 'templates/settings.php';
}

/**
 * 显示企业微信部门架构同步页面
 */
function qywx_sync_page() {
    require_once plugin_dir_path(__FILE__) . 'templates/sync-department.php';
}