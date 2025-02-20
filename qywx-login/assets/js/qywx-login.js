jQuery(document).ready(function ($) {
    // 当页面加载完成后执行以下代码

    // 选择登录按钮元素，根据实际 HTML 结构修改选择器
    const loginButton = $('.qywx-login-button');

    if (loginButton.length > 0) {
        // 如果登录按钮存在，则绑定点击事件
        loginButton.on('click', function (e) {
            e.preventDefault();

            // 显示加载提示
            const loadingMessage = $('<div class="qywx-login-loading">正在加载企业微信登录二维码，请稍候...</div>');
            $(this).after(loadingMessage);

            // 假设这里通过 AJAX 请求获取企业微信登录二维码的 URL
            $.ajax({
                url: ajaxurl, // WordPress 的 AJAX 处理 URL
                type: 'POST',
                data: {
                    action: 'qywx_get_login_qr_url', // 自定义的 AJAX 动作名称
                    // 可以添加其他需要传递的参数
                },
                success: function (response) {
                    if (response.success) {
                        // 移除加载提示
                        loadingMessage.remove();

                        // 创建一个 img 元素来显示二维码
                        const qrCodeImg = $('<img class="qywx-login-qr-code" src="' + response.data.qr_url + '" alt="企业微信登录二维码">');
                        $(loginButton).after(qrCodeImg);

                        // 可以添加二维码刷新、过期处理等逻辑
                    } else {
                        // 移除加载提示
                        loadingMessage.remove();

                        // 显示错误提示
                        const errorMessage = $('<div class="qywx-login-error">获取二维码失败，请稍后重试。</div>');
                        $(loginButton).after(errorMessage);
                    }
                },
                error: function () {
                    // 移除加载提示
                    loadingMessage.remove();

                    // 显示网络错误提示
                    const networkErrorMessage = $('<div class="qywx-login-error">网络错误，请检查网络连接后重试。</div>');
                    $(loginButton).after(networkErrorMessage);
                }
            });
        });
    }

    // 可以添加更多的交互逻辑，例如处理登录成功后的跳转等
    // 假设登录成功后会返回一个带有登录状态的 URL 参数
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('qywx_login_success')) {
        // 登录成功，显示欢迎信息并跳转到指定页面
        const welcomeMessage = $('<div class="qywx-login-success">欢迎登录，即将为您跳转...</div>');
        $('body').append(welcomeMessage);

        setTimeout(function () {
            window.location.href = urlParams.get('redirect_to') || '/';
        }, 2000);
    }
});