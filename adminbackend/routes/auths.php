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

    'recharge/lists' => [ 'method' => 'get', 'ctrl' => 'RechargeController@lists', 'title' => '充值套餐'],
    'recharge/edit' => [ 'method' => 'post', 'ctrl' => 'RechargeController@edit', 'title' => '编辑充值套餐'],
    'recharge/add' => [ 'method' => 'post', 'ctrl' => 'RechargeController@add', 'title' => '添加充值套餐'],
    //'recharge/del' => [ 'method' => 'post', 'ctrl' => 'RechargeController@del', 'title' => '删除充值套餐'],

    'service/lists' => [ 'method' => 'get', 'ctrl' => 'ServiceController@lists', 'title' => '功能服务'],
    'service/edit' => [ 'method' => 'post', 'ctrl' => 'ServiceController@edit', 'title' => '编辑功能服务'],
    'service/add' => [ 'method' => 'post', 'ctrl' => 'ServiceController@add', 'title' => '添加功能服务'],
    'service/del' => [ 'method' => 'post', 'ctrl' => 'ServiceController@del', 'title' => '删除功能服务'],
    'service/package-add' => [ 'method' => 'post', 'ctrl' => 'ServiceController@packageAdd', 'title' => '套餐添加'],
    'service/package-list' => [ 'method' => 'get', 'ctrl' => 'ServiceController@packageList', 'title' => '礼包列表'],
    'service/package-del' => [ 'method' => 'post', 'ctrl' => 'ServiceController@packageDel', 'title' => '删除礼包'],
    'service/package-edit' => [ 'method' => 'post', 'ctrl' => 'ServiceController@packageEdit', 'title' => '编辑礼包'],

    //用户中心
    'user/lists' => [ 'method' => 'get', 'ctrl' => 'UserController@lists', 'title' => '用户列表'],
    'user/service_recharge' => [ 'method' => 'get', 'ctrl' => 'UserController@serviceRecharge', 'title' => '功能续费'],
    'wx/lists' => [ 'method' => 'get', 'ctrl' => 'WxController@lists', 'title' => '微信列表'],
    'user/edit-user' => [ 'method' => 'post', 'ctrl' => 'UserController@editUser', 'title' => '用户信息修改'],

    //订单中心
    'order/recharge-order' => [ 'method' => 'get', 'ctrl' => 'OrderController@rechargeOrder', 'title' => '充值订单'],
    'order/service-order' => [ 'method' => 'get', 'ctrl' => 'OrderController@serviceOrder', 'title' => '开通服务订单'],

    //代理
    'agent/user-list' => [ 'method' => 'get', 'ctrl' => 'AgentController@userList', 'title' => '代理用户列表'],
    'agent/be-a-agent' => [ 'method' => 'post', 'ctrl' => 'AgentController@beAAgent', 'title' => '成为代理'],
    'agent/activation-code-list' => [ 'method' => 'get', 'ctrl' => 'AgentController@activationCodeList', 'title' => '激活码列表'],
    'agent/save-agent-data' => [ 'method' => 'post', 'ctrl' => 'AgentController@saveAgentData', 'title' => '保存代理信息'],
];
