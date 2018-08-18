<?php

return [
    ['title' => '帐号相关', 'icon' => 'document', 'children' => [
            ['title' => '帐号列表', 'path' => 'account/lists'],
            ['title' => '帐号统计', 'path' => 'account/statistical', 'home' => true],
            ['title' => '今日统计', 'path' => 'account/todayStatistics'],
            ['title' => '已卖出帐号', 'path' => 'account/soldOut'],
            ['title' => '回收交易猫帐号', 'path' => 'account/recovery'],
            ['title' => '重置账号到签到14天', 'path' => 'account/backTo14']
        ],
    ]
];
