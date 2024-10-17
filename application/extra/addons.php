<?php

return [
    'autoload' => false,
    'hooks' => [
        'baidupush' => [
            'baidupush',
        ],
        'view_filter' => [
            'betterform',
            'clicaptcha',
            'darktheme',
            'third',
        ],
        'config_init' => [
            'betterform',
            'cropper',
            'darktheme',
            'markdown',
            'simditor',
            'summernote',
            'third',
        ],
        'app_init' => [
            'captcha',
            'crontab',
            'qrcode',
            'xunsearch',
        ],
        'action_begin' => [
            'clicaptcha',
            'epay',
            'third',
        ],
        'captcha_mode' => [
            'clicaptcha',
        ],
        'response_send' => [
            'csp',
            'loginvideo',
        ],
        'epay_config_init' => [
            'epay',
        ],
        'addon_action_begin' => [
            'epay',
        ],
        'sms_send' => [
            'hwsms',
        ],
        'sms_notice' => [
            'hwsms',
        ],
        'sms_check' => [
            'hwsms',
        ],
        'user_sidenav_after' => [
            'invite',
            'recharge',
            'signin',
            'withdraw',
        ],
        'user_register_successed' => [
            'invite',
        ],
        'admin_login_init' => [
            'loginbg',
        ],
        'upgrade' => [
            'simditor',
        ],
        'user_delete_successed' => [
            'third',
        ],
        'user_logout_successed' => [
            'third',
        ],
        'module_init' => [
            'third',
        ],
    ],
    'route' => [
        '/example$' => 'example/index/index',
        '/example/d/[:name]' => 'example/demo/index',
        '/example/d1/[:name]' => 'example/demo/demo1',
        '/example/d2/[:name]' => 'example/demo/demo2',
        '/invite/[:id]$' => 'invite/index/index',
        '/qrcode$' => 'qrcode/index/index',
        '/qrcode/build$' => 'qrcode/index/build',
        '/third$' => 'third/index/index',
        '/third/connect/[:platform]' => 'third/index/connect',
        '/third/callback/[:platform]' => 'third/index/callback',
        '/third/bind/[:platform]' => 'third/index/bind',
        '/third/unbind/[:platform]' => 'third/index/unbind',
        '/xunsearch$' => 'xunsearch/index/index',
        '/xunsearch/[:name]' => 'xunsearch/index/search',
    ],
    'priority' => [],
    'domain' => '',
];
