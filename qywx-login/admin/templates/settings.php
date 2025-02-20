<?php
// 确保在 WordPress 环境中运行
if (!defined('ABSPATH')) {
    exit;
}

// 显示设置页面的标题
?>
<div class="wrap">
    <h1><?php echo esc_html__('企业微信登录设置', 'qywx-login'); ?></h1>
    <?php
    // 显示设置保存成功或失败的提示信息
    settings_errors();
    ?>
    <form method="post" action="options.php">
        <?php
        // 输出设置字段的隐藏表单元素，用于处理设置的保存
        settings_fields('qywx_settings_group');
        // 显示设置页面的内容
        do_settings_sections('qywx_settings_group');
        ?>
        <table class="form-table">
            <!-- 企业微信 CorpID 设置项 -->
            <tr>
                <th scope="row">
                    <label for="qywx_corpid"><?php echo esc_html__('企业微信 CorpID', 'qywx-login'); ?></label>
                </th>
                <td>
                    <input type="text" id="qywx_corpid" name="qywx_corpid"
                           value="<?php echo esc_attr(get_option('qywx_corpid')); ?>" class="regular-text" />
                    <p class="description">
                        <?php echo esc_html__('请输入您企业微信的 CorpID，可在企业微信管理后台获取。', 'qywx-login'); ?>
                    </p>
                </td>
            </tr>
            <!-- 企业微信 Secret 设置项 -->
            <tr>
                <th scope="row">
                    <label for="qywx_secret"><?php echo esc_html__('企业微信 Secret', 'qywx-login'); ?></label>
                </th>
                <td>
                    <input type="text" id="qywx_secret" name="qywx_secret"
                           value="<?php echo esc_attr(get_option('qywx_secret')); ?>" class="regular-text" />
                    <p class="description">
                        <?php echo esc_html__('请输入您企业微信的 Secret，可在企业微信管理后台获取。', 'qywx-login'); ?>
                    </p>
                </td>
            </tr>
            <!-- 默认用户组设置项 -->
            <tr>
                <th scope="row">
                    <label for="default_role"><?php echo esc_html__('默认用户组', 'qywx-login'); ?></label>
                </th>
                <td>
                    <select id="default_role" name="default_role">
                        <?php
                        global $wp_roles;
                        $roles = $wp_roles->get_names();
                        foreach ($roles as $role => $name) {
                            $selected = selected(get_option('default_role'), $role, false);
                            echo "<option value='{$role}' {$selected}>{$name}</option>";
                        }
                        ?>
                    </select>
                    <p class="description">
                        <?php echo esc_html__('选择新用户注册时默认分配的用户组。', 'qywx-login'); ?>
                    </p>
                </td>
            </tr>
            <!-- 禁用密码修改设置项 -->
            <tr>
                <th scope="row">
                    <label for="disable_pw"><?php echo esc_html__('禁用密码修改', 'qywx-login'); ?></label>
                </th>
                <td>
                    <input type="checkbox" id="disable_pw" name="disable_pw"
                           value="1" <?php checked(1, get_option('disable_pw')); ?> />
                    <p class="description">
                        <?php echo esc_html__('勾选此项将禁止用户在 WordPress 后台修改密码。', 'qywx-login'); ?>
                    </p>
                </td>
            </tr>
        </table>
        <?php submit_button(); ?>
    </form>

    <!-- 配置单表格 -->
    <h2><?php echo esc_html__('企业微信登录用户创建规则', 'qywx-login'); ?></h2>
    <table class="widefat">
        <thead>
            <tr>
                <th><?php echo esc_html__('企业微信字段', 'qywx-login'); ?></th>
                <th><?php echo esc_html__('WP 字段', 'qywx-login'); ?></th>
                <th><?php echo esc_html__('匹配是否成功', 'qywx-login'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            // 获取真实的映射规则，这里假设从选项中获取
            $mapping_rules = get_option('qywx_user_field_map', array());
            if (!empty($mapping_rules)) {
                foreach ($mapping_rules as $qywx_field => $wp_field) {
                    // 这里可以根据实际逻辑判断是否匹配，暂时简单判断字段是否存在
                    $is_matched = !empty($qywx_field) && !empty($wp_field);
                    ?>
                    <tr>
                        <td><?php echo esc_html($qywx_field); ?></td>
                        <td><?php echo esc_html($wp_field); ?></td>
                        <td><?php echo $is_matched ? esc_html__('是', 'qywx-login') : esc_html__('否', 'qywx-login'); ?></td>
                    </tr>
                    <?php
                }
            } else {
                ?>
                <tr>
                    <td colspan="3"><?php echo esc_html__('暂无映射规则，请配置。', 'qywx-login'); ?></td>
                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>
</div>