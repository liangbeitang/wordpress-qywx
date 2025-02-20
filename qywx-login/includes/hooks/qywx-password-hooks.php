<?php
// 确保在 WordPress 环境中运行
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 禁用密码修改功能
 */
function qywx_disable_password_change() {
    // 检查是否开启了禁用密码修改的选项
    if (get_option('disable_pw')) {
        // 移除在用户个人资料页面显示密码字段的动作
        remove_action('show_user_profile', 'show_password_fields');
        // 移除在编辑其他用户资料页面显示密码字段的动作
        remove_action('edit_user_profile', 'show_password_fields');
        // 移除在个人资料更新时处理密码更新的动作
        remove_action('personal_options_update', 'wp_update_user_password');
        // 移除在编辑其他用户资料更新时处理密码更新的动作
        remove_action('edit_user_profile_update', 'wp_update_user_password');
    }
}
add_action('admin_init', 'qywx_disable_password_change');

/**
 * 在前端移除密码修改表单字段
 * 此函数通过 JavaScript 隐藏前端密码修改表单字段
 */
function qywx_remove_password_fields_frontend() {
    if (get_option('disable_pw')) {
        // 输出 JavaScript 代码来隐藏前端密码修改表单字段
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                // 查找包含密码字段的表单元素并隐藏
                $('form#your-profile input[name="pass1"]').closest('tr').hide();
                $('form#your-profile input[name="pass2"]').closest('tr').hide();
            });
        </script>
        <?php
    }
}
add_action('admin_footer', 'qywx_remove_password_fields_frontend');
add_action('wp_footer', 'qywx_remove_password_fields_frontend');