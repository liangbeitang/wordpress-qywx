<?php
// 确保在 WordPress 环境中运行
if (!defined('ABSPATH')) {
    exit;
}

// 获取同步状态消息（如果有）
$sync_message = get_transient('qywx_sync_message');
delete_transient('qywx_sync_message');
?>
<div class="wrap">
    <h1><?php echo esc_html__('企业微信部门架构同步', 'qywx-login'); ?></h1>
    <?php if ($sync_message) : ?>
        <div id="message" class="<?php echo strpos($sync_message, '成功')!== false? 'updated' : 'error';?> notice is-dismissible">
            <p><?php echo esc_html($sync_message); ?></p>
            <button type="button" class="notice-dismiss">
                <span class="screen-reader-text"><?php echo esc_html__('忽略此通知。', 'qywx-login'); ?></span>
            </button>
        </div>
    <?php endif; ?>
    <p>
        <?php echo esc_html__('点击下面的按钮可以手动同步企业微信的部门架构到 WordPress。', 'qywx-login');?>
        <?php echo esc_html__('建议通过 WP - Cron 每日自动执行此操作以保持数据最新。', 'qywx-login');?>
    </p>
    <form method="post" action="<?php echo esc_url(admin_url('admin - ajax.php'));?>">
        <input type="hidden" name="action" value="qywx_sync_departments">
        <?php wp_nonce_field('qywx_sync_nonce', 'qywx_sync_nonce_field');?>
        <?php submit_button(esc_html__('立即同步部门架构', 'qywx-login'), 'primary', 'qywx_sync_submit');?>
    </form>
    <p class="description">
        <?php echo esc_html__('注意：同步操作可能会消耗一定的时间和系统资源，尤其是在企业微信部门架构较大的情况下。', 'qywx-login');?>
    </p>
</div>