<?php

return [
    ['title' => '管理员设置', 'icon' => 'menu', 'children' => [
            ['title' => '管理员管理', 'path' => 'admin/lists', 'home' => true],
            ['title' => '角色管理', 'path' => 'role/lists'],
        ],
    ],
    ['title' => '系统配置', 'icon' => 'document', 'children' => [
            ['title' => '功能服务', 'path' => 'service/lists'],
            ['title' => '功能套餐', 'path' => 'service/package_list'],
        ],
    ],
    ['title' => '用户中心', 'icon' => 'news', 'children' => [
            ['title' => '用户列表', 'path' => 'user/lists'],
            ['title' => '微信号管理', 'path' => 'wx/lists'],
            ['title' => '功能续费', 'path' => 'user/service_recharge'],
        ],
    ],
    ['title' => '订单中心', 'icon' => 'date', 'children' => [
            ['title' => '开通服务订单', 'path' => 'order/wx_service_lists'],
        ],
    ],
    ['title' => '代理管理', 'icon' => 'document', 'children' => [
            ['title' => '代理用户列表', 'path' => 'agent/user_list'],
            ['title' => '激活码列表', 'path' => 'agent/activation_code_list']
        ],
    ],
];
