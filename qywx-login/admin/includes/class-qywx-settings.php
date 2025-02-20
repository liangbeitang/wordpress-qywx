<?php
/**
 * 企业微信设置类，用于管理插件的后台设置
 */
class QYWX_Settings {

    /**
     * 构造函数，在类实例化时执行必要的初始化操作
     */
    public function __construct() {
        // 注册设置项，以便将设置保存到 WordPress 选项表
        add_action('admin_init', array($this, 'register_settings'));
        // 添加后台菜单页面，方便用户访问设置页面
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }

    /**
     * 注册设置项，将设置存储在 WordPress 选项表中
     */
    public function register_settings() {
        $settings = array(
            'qywx_corpid' => array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => ''
            ),
            'qywx_secret' => array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => ''
            ),
            'user_field_map' => array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => ''
            ),
            'default_role' => array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => 'subscriber'
            )
        );

        foreach ($settings as $setting => $args) {
            register_setting(
                'qywx_settings_group',
                $setting,
                $args
            );
        }

        // 注册整个设置组
        register_setting(
            'qywx_settings_group',
            'qywx_settings_group',
            array(
                'type' => 'array',
                'sanitize_callback' => array($this, 'sanitize_settings_group'),
                'default' => array()
            )
        );
    }

    /**
     * 自定义清理回调函数，用于处理整个设置组的清理
     *
     * @param array $input 输入的设置数组
     * @return array 清理后的设置数组
     */
    public function sanitize_settings_group($input) {
        // 在这里可以添加对整个设置组的清理逻辑
        return $input;
    }

    /**
     * 添加后台菜单页面，使用户可以在 WordPress 后台访问设置页面
     */
    public function add_admin_menu() {
        add_menu_page(
            '企业微信登录设置', // 页面的标题
            '企业微信登录', // 菜单的标题
            'manage_options', // 访问该菜单所需的权限
            'qywx-settings', // 菜单的唯一标识符
            array($this, 'settings_page'), // 显示设置页面的回调函数
            'dashicons-admin-generic', // 菜单的图标
            6 // 菜单在后台菜单中的位置
        );
    }

    /**
     * 显示设置页面的内容，包含表单和输入字段
     */
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form method="post" action="options.php">
                <?php
                // 输出设置字段的隐藏表单元素，用于处理设置的保存
                settings_fields('qywx_settings_group');
                // 显示设置页面的内容
                do_settings_sections('qywx_settings_group');
                ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="qywx_corpid">企业微信 CorpID</label></th>
                        <td>
                            <input type="text" id="qywx_corpid" name="qywx_corpid"
                                   value="<?php echo esc_attr(get_option('qywx_corpid')); ?>" class="regular-text" />
                            <p class="description">请输入企业微信的 CorpID。</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="qywx_secret">企业微信 Secret</label></th>
                        <td>
                            <input type="text" id="qywx_secret" name="qywx_secret"
                                   value="<?php echo esc_attr(get_option('qywx_secret')); ?>" class="regular-text" />
                            <p class="description">请输入企业微信的 Secret。</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="user_field_map">用户名字段映射</label></th>
                        <td>
                            <input type="text" id="user_field_map" name="user_field_map"
                                   value="<?php echo esc_attr(get_option('user_field_map')); ?>" class="regular-text" />
                            <p class="description">请输入企业微信字段与 WordPress 用户名的映射规则。</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="default_role">默认用户组</label></th>
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
                            <p class="description">请选择新用户默认分配的用户组。</p>
                        </td>
                    </tr>
                </table>
                <?php
                // 输出保存设置的提交按钮
                submit_button('保存设置');
                ?>
            </form>
        </div>
        <?php
    }
}

// 创建 QYWX_Settings 类的实例，触发构造函数中的动作钩子
new QYWX_Settings();