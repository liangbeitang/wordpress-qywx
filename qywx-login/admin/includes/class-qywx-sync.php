<?php
/**
 * 企业微信同步类，用于处理部门架构同步和用户信息同步
 */
class QYWX_Sync {
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
    private function get_access_token() {
        $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid={$this->corpid}&corpsecret={$this->secret}";
        $response = wp_remote_get($url);
        if (is_wp_error($response)) {
            return false;
        }
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        if (isset($data['access_token'])) {
            return $data['access_token'];
        }
        return false;
    }

    /**
     * 同步企业微信的部门架构
     *
     * @return bool 同步成功返回 true，失败返回 false
     */
    public function sync_departments() {
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
        if (isset($data['errcode']) && $data['errcode'] === 0 && isset($data['department'])) {
            foreach ($data['department'] as $dept) {
                $dept_id = $dept['id'];
                $dept_name = $dept['name'];
                $parent_id = $dept['parentid'];
                // 这里可以添加将部门信息保存到 WordPress 自定义表或者用户元数据的逻辑
                // 例如：update_option("qywx_dept_{$dept_id}", $dept_name);
            }
            return true;
        }
        return false;
    }

    /**
     * 同步企业微信的用户信息
     *
     * @return bool 同步成功返回 true，失败返回 false
     */
    public function sync_users() {
        $access_token = $this->get_access_token();
        if (!$access_token) {
            return false;
        }
        // 先同步部门架构
        if (!$this->sync_departments()) {
            return false;
        }
        $dept_list = $this->get_all_departments();
        foreach ($dept_list as $dept_id) {
            $url = "https://qyapi.weixin.qq.com/cgi-bin/user/list?access_token={$access_token}&department_id={$dept_id}&fetch_child=1";
            $response = wp_remote_get($url);
            if (is_wp_error($response)) {
                continue;
            }
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);
            if (isset($data['errcode']) && $data['errcode'] === 0 && isset($data['userlist'])) {
                foreach ($data['userlist'] as $user) {
                    $this->create_or_update_user($user);
                }
            }
        }
        return true;
    }

    /**
     * 获取所有部门 ID
     *
     * @return array 部门 ID 数组
     */
    private function get_all_departments() {
        $dept_ids = array();
        $access_token = $this->get_access_token();
        if (!$access_token) {
            return $dept_ids;
        }
        $url = "https://qyapi.weixin.qq.com/cgi-bin/department/list?access_token={$access_token}";
        $response = wp_remote_get($url);
        if (is_wp_error($response)) {
            return $dept_ids;
        }
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        if (isset($data['errcode']) && $data['errcode'] === 0 && isset($data['department'])) {
            foreach ($data['department'] as $dept) {
                $dept_ids[] = $dept['id'];
            }
        }
        return $dept_ids;
    }

    /**
     * 创建或更新 WordPress 用户
     *
     * @param array $user 企业微信用户信息数组
     */
    private function create_or_update_user($user) {
        $username = $this->map_user_field($user);
        $user_id = username_exists($username);
        if (!$user_id) {
            $password = $this->generate_password();
            $email = isset($user['email']) ? $user['email'] : '';
            $user_id = wp_create_user($username, $password, $email);
            if ($user_id) {
                $this->send_password($user_id, $password);
            }
        }
        // 更新用户的部门信息
        if (isset($user['department'])) {
            update_user_meta($user_id, 'qywx_dept', $user['department']);
        }
        // 可以添加更多更新用户信息的逻辑，如昵称、手机号等
    }

    /**
     * 根据配置映射用户名字段
     *
     * @param array $user 企业微信用户信息数组
     * @return string 映射后的用户名
     */
    private function map_user_field($user) {
        $field_map = get_option('user_field_map');
        return isset($user[$field_map]) ? $user[$field_map] : $user['userid'];
    }

    /**
     * 生成 24 位强密码
     *
     * @return string 生成的密码
     */
    private function generate_password() {
        return bin2hex(random_bytes(16));
    }

    /**
     * 发送密码给用户
     *
     * @param int $user_id 用户 ID
     * @param string $password 密码
     */
    private function send_password($user_id, $password) {
        $user = get_user_by('ID', $user_id);
        $message = "您的首次登录密码为：{$password}，请及时修改。";
        // 调用企业微信 API 发送消息（需实现具体接口）
        // qywx_send_wechat_message($user->user_email, $message);
        // 这里可以添加具体的发送消息逻辑，如使用 wp_mail 发送邮件
        wp_mail($user->user_email, '您的登录密码', $message);
    }
}