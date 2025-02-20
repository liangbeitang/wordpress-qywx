<?php
/**
 * 企业微信同步类，用于处理部门架构同步和用户信息同步
 */
class QYWX_Sync {
    private $corpid;
    private $secret;
    private $access_token;

    /**
     * 构造函数，初始化企业微信的 CorpID 和 Secret
     */
    public function __construct() {
        $this->corpid = get_option('qywx_corpid');
        $this->secret = get_option('qywx_secret');
        $this->access_token = $this->get_cached_access_token();
    }

    /**
     * 获取缓存的访问令牌，如果没有则重新获取
     *
     * @return string|bool 访问令牌，如果获取失败返回 false
     */
    private function get_cached_access_token() {
        $cached_token = get_transient('qywx_access_token');
        if ($cached_token) {
            return $cached_token;
        }
        $token = $this->get_access_token();
        if ($token) {
            set_transient('qywx_access_token', $token, 7200); // 缓存 2 小时
        }
        return $token;
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
            // 记录错误日志
            error_log('获取企业微信访问令牌失败: '. $response->get_error_message());
            return false;
        }
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        if (isset($data['access_token'])) {
            return $data['access_token'];
        }
        // 记录错误日志
        error_log('获取企业微信访问令牌失败: '. json_encode($data));
        return false;
    }

    /**
     * 获取部门列表数据
     *
     * @param string $access_token 访问令牌
     * @return array|false 部门列表数据，失败返回 false
     */
    private function get_department_list($access_token) {
        $url = "https://qyapi.weixin.qq.com/cgi-bin/department/list?access_token={$access_token}";
        $response = wp_remote_get($url);
        if (is_wp_error($response)) {
            // 记录错误日志
            error_log('获取部门列表失败: '. $response->get_error_message());
            return false;
        }
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        if (isset($data['errcode']) && $data['errcode'] === 0 && isset($data['department'])) {
            return $data['department'];
        }
        // 记录错误日志
        error_log('获取部门列表失败: '. json_encode($data));
        return false;
    }

    /**
     * 同步企业微信的部门架构
     *
     * @return bool 同步成功返回 true，失败返回 false
     */
    public function sync_departments() {
        if (!$this->access_token) {
            return false;
        }
        $department_list = $this->get_department_list($this->access_token);
        if (!$department_list) {
            return false;
        }
        foreach ($department_list as $dept) {
            $dept_id = $dept['id'];
            $dept_name = $dept['name'];
            $parent_id = $dept['parentid'];
            // 这里可以添加将部门信息保存到 WordPress 自定义表或者用户元数据的逻辑
            // 例如：update_option("qywx_dept_{$dept_id}", $dept_name);
        }
        return true;
    }

    /**
     * 同步企业微信的用户信息
     *
     * @return bool 同步成功返回 true，失败返回 false
     */
    public function sync_users() {
        if (!$this->access_token) {
            return false;
        }
        // 先同步部门架构
        if (!$this->sync_departments()) {
            return false;
        }
        $dept_list = $this->get_all_departments();
        foreach ($dept_list as $dept_id) {
            $url = "https://qyapi.weixin.qq.com/cgi-bin/user/list?access_token={$this->access_token}&department_id={$dept_id}&fetch_child=1";
            $response = wp_remote_get($url);
            if (is_wp_error($response)) {
                // 记录错误日志
                error_log('获取部门用户列表失败: '. $response->get_error_message());
                continue;
            }
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);
            if (isset($data['errcode']) && $data['errcode'] === 0 && isset($data['userlist'])) {
                foreach ($data['userlist'] as $user) {
                    $this->create_or_update_user($user);
                }
            } else {
                // 记录错误日志
                error_log('获取部门用户列表失败: '. json_encode($data));
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
        if (!$this->access_token) {
            return $dept_ids;
        }
        $department_list = $this->get_department_list($this->access_token);
        if (!$department_list) {
            return $dept_ids;
        }
        foreach ($department_list as $dept) {
            $dept_ids[] = $dept['id'];
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
            update_user_meta($user_id, '