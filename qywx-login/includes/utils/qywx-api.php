<?php
// 确保在 WordPress 环境中运行
if (!defined('ABSPATH')) {
    exit;
}

class QYWX_API {
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
     * 发送企业微信消息
     *
     * @param string $touser 接收消息的用户 ID 列表，多个用 | 分隔
     * @param string $msgtype 消息类型，如 text
     * @param array $message 消息内容数组
     * @return bool 发送成功返回 true，失败返回 false
     */
    public function send_message($touser, $msgtype, $message) {
        $access_token = $this->get_access_token();
        if (!$access_token) {
            return false;
        }

        $url = "https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token={$access_token}";
        $post_data = array(
            'touser' => $touser,
            'msgtype' => $msgtype,
            $msgtype => $message,
            'agentid' => 1  // 这里假设使用的应用 ID 为 1，需要根据实际情况修改
        );
        $post_data = json_encode($post_data);

        $args = array(
            'method' => 'POST',
            'headers' => array(
                'Content-Type' => 'application/json'
            ),
            'body' => $post_data
        );

        $response = wp_remote_post($url, $args);

        if (is_wp_error($response)) {
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        return isset($data['errcode']) && $data['errcode'] === 0;
    }

    /**
     * 获取企业微信部门列表
     *
     * @return array|bool 部门列表数组，如果获取失败返回 false
     */
    public function get_department_list() {
        $access_token = $this->get_access_token();
        if (!$access_token) {
            return false;
        }

        $url = "https://qyapi.weixin.qq.com/cgi-bin/department/list?access_token={$access_token}";
        $response = wp_remote_get($url);

        if (is_wp_error($response)) {
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        return isset($data['errcode']) && $data['errcode'] === 0? $data['department'] : false;
    }

    /**
     * 获取企业微信部门下的用户列表
     *
     * @param int $department_id 部门 ID
     * @param bool $fetch_child 是否递归获取子部门用户，默认 false
     * @return array|bool 用户列表数组，如果获取失败返回 false
     */
    public function get_user_list($department_id, $fetch_child = false) {
        $access_token = $this->get_access_token();
        if (!$access_token) {
            return false;
        }

        $fetch_child = $fetch_child? 1 : 0;
        $url = "https://qyapi.weixin.qq.com/cgi-bin/user/list?access_token={$access_token}&department_id={$department_id}&fetch_child={$fetch_child}";
        $response = wp_remote_get($url);

        if (is_wp_error($response)) {
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        return isset($data['errcode']) && $data['errcode'] === 0? $data['userlist'] : false;
    }
}