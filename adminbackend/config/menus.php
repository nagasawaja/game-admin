<?php

return [
    ['title' => '帐号相关', 'icon' => 'document', 'children' =>
        [
            ['title' => 'f7帐号列表', 'path' => 'account/lists'],
            ['title' => 'f7帐号统计', 'path' => 'account/statistical'],
            // ['title' => 'f7今日统计', 'path' => 'account/todayStatistics'],
            ['title' => 'f7已卖出帐号', 'path' => 'account/soldOut'],
            ['title' => 'f7回收交易猫帐号', 'path' => 'account/recovery'],
            // ['title' => 'f7重置账号到签到14天', 'path' => 'account/backTo14'],
            ['title' => 'id5帐号列表', 'path' => 'id5Account/lists'],
            ['title' => 'id5帐号统计', 'path' => 'id5Account/statistical'],
            // ['title' => 'id5今日统计', 'path' => 'id5Account/todayStatistics'],
            ['title' => 'id5已卖出帐号', 'path' => 'id5Account/soldOut'],
            ['title' => 'football帐号列表', 'path' => 'footballAccount/lists'],
            ['title' => 'football帐号统计', 'path' => 'footballAccount/statistical'],
            ['title' => 'football已卖出帐号', 'path' => 'footballAccount/soldOut'],
            ['title' => 'dream帐号列表', 'path' => 'dreamAccount/lists'],
            ['title' => 'dream帐号统计', 'path' => 'dreamAccount/statistical'],
            ['title' => 'dream已卖出帐号', 'path' => 'dreamAccount/soldOut']
        ],
    ],
    ['title' => '交易猫', 'icon' => 'document', 'children' =>
        [
            ['title' => '单个商品销量变化', 'path' => 'mao/goodsChangeHistory', 'home' => true],
            ['title' => '商品总数量与销量比例', 'path' => 'mao/goodsScale'],
            ['title' => '数据报表', 'path' => 'mao/dataReport'],
            ['title' => '执行原生sql', 'path' => 'account/querySql'],
            ['title' => '执行原生redis', 'path' => 'account/queryRedis'],
            ['title' => '工具箱', 'path' => 'mao/tools'],
            ['title' => '脚本数据', 'path' => 'mao/scriptRecord']
        ]
    ],
    ['title' => '淘宝买号', 'icon' => 'document', 'children' =>
        [
            ['title' => '新增订单', 'path' => 'taobao/createOrder'],
            ['title' => '订单列表', 'path' => 'taobao/orderLists'],
            ['title' => '封号统计', 'path' => 'taobao/forbidStatistical'],
            ['title' => '封号已回收列表', 'path' => 'taobao/soldOut'],
        ]
    ],
    ['title' => '管理员设置', 'icon' => 'menu', 'children' => [
        ['title' => '管理员管理', 'path' => 'admin/lists'],
        ['title' => '角色管理', 'path' => 'role/lists']],
    ],
];
