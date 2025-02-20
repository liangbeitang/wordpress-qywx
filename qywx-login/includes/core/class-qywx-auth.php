<?php
// 确保在 WordPress 环境中运行
if (!defined('ABSPATH')) {
    exit;
}

class QYWX_Auth {
    private $corpid;
    private $secret;

    /**
     * 构造函数，初始化企业微信的 CorpID 和 Secret
     */
    public function __construct() {
        $this->corpid = get_option('qywx_corpid');
        $this->secret = get_option('qywx_secret');
    }

    /**
     * 获取企业微信的访问令牌
     *
     * @return string|bool 访问令牌，如果获取失败返回 false
     */
    public function get_access_token() {
        $transient_name = 'qywx_access_token';
        $access_token = get_transient($transient_name);

        if ($access_token) {
            return $access_token;
        }

        $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid={$this->corpid}&corpsecret={$this->secret}";
        $response = wp_remote_get($url);

        if (is_wp_error($response)) {
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (isset($data['access_token']) && isset($data['expires_in'])) {
            set_transient($transient_name, $data['access_token'], $data['expires_in'] - 60);
            return $data['access_token'];
        }

        return false;
    }

    /**
     * 根据授权码获取用户信息
     *
     * @param string $access_token 访问令牌
     * @param string $code 授权码
     * @return array|bool 用户信息数组，如果获取失败返回 false
     */
    public function get_user_info($access_token, $code) {
        $url = "https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo?access_token={$access_token}&code={$code}";
        $response = wp_remote_get($url);

        if (is_wp_error($response)) {
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (isset($data['errcode']) && $data['errcode'] === 0) {
            return $data;
        }

        return false;
    }

    /**
     * 生成企业微信扫码登录二维码的 URL
     *
     * @return string 扫码登录二维码的 URL
     */
    public function generate_qr_url() {
        $redirect_uri = urlencode(admin_url('admin-ajax.php?action=qywx_callback'));
        $state = wp_create_nonce('qywx_login');

        return "https://open.work.weixin.qq.com/wwopen/sso/qrConnect?appid={$this->corpid}&redirect_uri={$redirect_uri}&state={$state}";
    }
}