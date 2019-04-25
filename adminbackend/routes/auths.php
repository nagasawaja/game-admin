<?php

return [
    //guanliyuan
    'admin/info' => [ 'method' => 'get', 'ctrl' => 'AdminController@info', 'title' => '管理员信息', 'allrole' => true],
    'admin/lists' => [ 'method' => 'get', 'ctrl' => 'AdminController@lists', 'title' => '管理员列表'],
    'admin/add' => [ 'method' => 'post', 'ctrl' => 'AdminController@add', 'title' => '添加管理员'],
    'admin/del' => ['method' => 'post', 'ctrl' => 'AdminController@del', 'title' => '删除管理员'],
    'admin/edit' => ['method' => 'post', 'ctrl' => 'AdminController@edit', 'title' => '编辑管理员'],
    'admin/sideMenus' => ['method' => 'get', 'ctrl' => 'AdminController@sideMenus', 'title' => '左侧导航菜单', 'allrole' => true],
    'role/lists' => [ 'method' => 'get', 'ctrl' => 'RoleController@lists', 'title' => '角色列表'],
    'role/add' => [ 'method' => 'post', 'ctrl' => 'RoleController@add', 'title' => '添加角色'],
    'role/del' => [ 'method' => 'post', 'ctrl' => 'RoleController@del', 'title' => '删除角色'],
    'role/edit' => [ 'method' => 'post', 'ctrl' => 'RoleController@edit', 'title' => '编辑角色'],
    'role/editAuth' => [ 'method' => 'post', 'ctrl' => 'RoleController@editAuth', 'title' => '编辑角色权限'],
    'role/listAuth' => [ 'method' => 'get', 'ctrl' => 'RoleController@listAuth', 'title' => '角色权限列表', 'allrole' => true],

    //f7
    'account/lists' => [ 'method' => 'post', 'ctrl' => 'AccountController@lists', 'title' => '帐号列表'],
    'account/statistical' => [ 'method' => 'post', 'ctrl' => 'AccountController@statistical', 'title' => 'f7帐号统计'],
    'account/mark-account-sold-out' => [ 'method' => 'post', 'ctrl' => 'AccountController@markAccountSoldOut', 'title' => 'f7将未卖出的帐号置成已卖出'],
    'account/sold-out-account-detail' => [ 'method' => 'post', 'ctrl' => 'AccountController@soldOutAccountDetail', 'title' => 'f7已卖出帐号详细'],
    'account/soldOut' => [ 'method' => 'post', 'ctrl' => 'AccountController@soldOutAccountList', 'title' => 'f7已卖出帐号列表'],
    'account/todayStatistics' => [ 'method' => 'post', 'ctrl' => 'AccountController@todayStatistics', 'title' => 'f7今日帐号统计'],
    'account/recovery' => [ 'method' => 'post', 'ctrl' => 'AccountController@recovery', 'title' => 'f7回收交易猫帐号'],
    'account/backTo14' => [ 'method' => 'post', 'ctrl' => 'AccountController@backTo14', 'title' => 'f7回收15天完成的账号到14天签到'],

    //sql
    'account/query-sql-save' => [ 'method' => 'post', 'ctrl' => 'AccountController@querySqlSave', 'title' => '执行原生sql保存'],
    'account/querySql' => [ 'method' => 'post', 'ctrl' => 'AccountController@querySqlMenu', 'title' => '执行原生sql菜单'],

    //id5
    'id5Account/statistical' => [ 'method' => 'post', 'ctrl' => 'Id5AccountController@statistical', 'title' => 'id5帐号统计'],
    'id5Account/todayStatistics' => [ 'method' => 'post', 'ctrl' => 'Id5AccountController@todayStatistics', 'title' => 'id5今日帐号统计'],
    'id5Account/mark-account-sold-out' => [ 'method' => 'post', 'ctrl' => 'Id5AccountController@markAccountSoldOut', 'title' => 'id5将未卖出的帐号置成已卖出'],
    'id5Account/sold-out-account-detail' => [ 'method' => 'post', 'ctrl' => 'Id5AccountController@soldOutAccountDetail', 'title' => 'id5已卖出帐号详细'],
    'id5Account/soldOut' => [ 'method' => 'post', 'ctrl' => 'Id5AccountController@soldOutAccountList', 'title' => 'id5已卖出帐号列表'],

    //jiaoyimao
    'mao/goodsChangeHistory' => [ 'method' => 'post', 'ctrl' => 'MaoController@goodsChangeHistory', 'title' => '单个商品销量变化'],
    'mao/goodsScale' => [ 'method' => 'post', 'ctrl' => 'MaoController@goodsScale', 'title' => '商品总数量与销量比例'],
    'mao/dataReport' => [ 'method' => 'post', 'ctrl' => 'MaoController@dateReport', 'title' => '数据报表'],

    //通用设置
    'systemConfig/sys-config-menu' => [ 'method' => 'get', 'ctrl' => 'SystemConfigController@sysConfigMenu', 'title' => '系统基础信息'],
    'systemConfig/sys-config-save' => [ 'method' => 'post', 'ctrl' => 'SystemConfigController@sysConfigSave', 'title' => '系统基础信息保存'],

];
