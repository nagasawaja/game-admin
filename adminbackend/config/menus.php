<?php

return [
    ['title' => '管理员设置', 'icon' => 'menu', 'children' => [
            ['title' => '管理员管理', 'path' => 'admin/lists', 'home' => true],
            ['title' => '角色管理', 'path' => 'role/lists'],
        ],
    ],
    ['title' => '帐号相关', 'icon' => 'document', 'children' => [
            ['title' => '帐号列表', 'path' => 'account/lists'],
            ['title' => '帐号统计', 'path' => 'account/statistical'],
            ['title' => '今日统计', 'path' => 'account/todayStatistics'],
            ['title' => '已卖出帐号', 'path' => 'account/soldOut'],
            ['title' => '回收交易猫帐号', 'path' => 'account/recovery'],

        ],
    ]
];
