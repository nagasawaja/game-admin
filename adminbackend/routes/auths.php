<?php

return [
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

    'account/lists' => [ 'method' => 'post', 'ctrl' => 'AccountController@lists', 'title' => '帐号列表'],
    'account/statistical' => [ 'method' => 'post', 'ctrl' => 'AccountController@statistical', 'title' => '帐号统计'],
    'account/mark-account-sold-out' => [ 'method' => 'post', 'ctrl' => 'AccountController@markAccountSoldOut', 'title' => '将未卖出的帐号置成已卖出'],
    'account/sold-out-account-detail' => [ 'method' => 'post', 'ctrl' => 'AccountController@soldOutAccountDetail', 'title' => '已卖出帐号详细'],

    //通用设置
    'systemConfig/sys-config-menu' => [ 'method' => 'get', 'ctrl' => 'SystemConfigController@sysConfigMenu', 'title' => '系统基础信息'],
    'systemConfig/sys-config-save' => [ 'method' => 'post', 'ctrl' => 'SystemConfigController@sysConfigSave', 'title' => '系统基础信息保存'],

];
