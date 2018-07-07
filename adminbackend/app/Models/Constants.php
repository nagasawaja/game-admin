<?php
/**
 * 常量定义
 */

namespace App\Models;

class Constants {
    //go 服务器地址
    const URL_HOST = 'http://192.168.42.172:7081';

    //go 接口地址
    const URL_NEW_QR_CODE = Constants::URL_HOST .  '/newQrcode'; //创建二维码(添加微信号时)
    const URL_CHECK_QR_CODE = Constants::URL_HOST .  '/checkQrcode'; //检测二维码状态
    const URL_CHECK_CLIENT_STATE = Constants::URL_HOST .  '/clientState'; //检测设备状态
    const URL_REFRESH_QR_CODE = Constants::URL_HOST .  '/refreshQrcode'; //刷新二维码
    const URL_WX_LOGIN = Constants::URL_HOST .  '/login'; //登录
    const URL_WX_LOGOUT = Constants::URL_HOST .  '/logout'; //退出登录
    const URL_TIMEOUT = 7; //curl 超时时间

    //Redis Key
    const RK_LOGIN_TOKEN = 'login_token:'; //用户登录token
    const RK_VERIFY_CODE = 'verify_code:'; //手机注册验证码
    const RK_USER_COIN_LOCK = 'user_coin_lock:'; //用户金币锁
    const RK_USER_IDFA = 'user_idfa:'; //微信idfa(绑定的设备标识)
    const RK_AUTO_INCREMENT = 'auto_increment:'; //自增
    const RK_CHAT_TASKS = 'chat:tasks'; //自增

    //各种ttl 单位s
    const TTL_VERIFY_CODE = 60; //验证码超时时间
    const TTL_LOGIN = 60000; //登录超时
    const TTL_USER_IDFA = 600; //获取IDFA超时时间

    //各种密钥
    const KEY_LOGIN_TOKEN = ''; //登录token 密钥

    //各种type
    const TYPE_COIN_BILL_RECHARGE = 1; //充值
    const TYPE_COIN_BILL_PURCHASE_SERVICE = 2; //购买服务消费
    const TYPE_SERVICE_PRODUCT_CHARGE = 1; //收费
    const TYPE_SERVICE_PRODUCT_FREE = 2; //免费
    const TYPE_SYNC_SEND_TARGET_GROUP = 1; //发送目标类型，1=group_id
    const TYPE_SYNC_SEND_TARGET_WX_ID = 2; //发送目标类型，1=wxid
    const TYPE_RECHARGE_ORDER_APPLE = 1; //苹果支付
    const TYPE_RECHARGE_ORDER_WX_OFFICIAL_ACCOUNT = 8; //微信公众号支付
    const TYPE_VERIFY_CODE_REGISTER = 1; //注册验证码
    const TYPE_VERIFY_CODE_FORGET_PASSWORD = 2; //忘记密码验证码
    const TYPE_WX_ACCOUNT_UNDEFINED = 0; //未知联系人类型
    const TYPE_WX_ACCOUNT_FRIEND = 1; //普通好友
    const TYPE_WX_ACCOUNT_GROUP = 5; //群聊


    //各种status
    const STATUS_VALID_0 = 0; //有效的
    const STATUS_VALID = 1; //有效的
    const STATUS_INVALID = 2; //无效的
    const STATUS_DELETE = 3; //删除的
    const STATUS_RECHARGE_ORDER_PAID = 1; //已支付
    const STATUS_RECHARGE_ORDER_NOT_PAID = 2; //未支付
    const STATUS_RECHARGE_ORDER_CANCEL = 3; //已取消
    const STATUS_WX_CONTACTS_NORMAL = 1; //好友状态正常

    //杂项
    const OTHER_SYMBOL_ADD = '+'; //加号
    const OTHER_SYMBOL_SUBTRACT = '-'; //减号
    const OTHER_EXPIRE = 1; //过期
    const OTHER_NOT_EXPIRE = 2; //未过期
    const OTHER_SERVICE_ID_SYNC_SEND = 1; //service id sync send
    const OTHER_SERVICE_ID_MOMENTS = 2; //service id moment
    const OTHER_SERVICE_ID_OTHER_MOMENTS = 3; //service id other moments

    //ipad设备状态
    const IPAD_STATE_CYCLE = 6;

    //网站各种登记信息
    const APP_WX_ID = 'wx236e74a543ebc7fb';
    const APP_WX_APP_SECRET = '473d39767600fa0353f21509accbf38c';

    const WX_APP_ID = 'wx236e74a543ebc7fb';
    const WX_KEY = '2EC3A62B3D9B8DE65DD51E82852B8B18';
    const WX_MCH_ID = '1500020512';
    const WX_APP_SECRET = '473d39767600fa0353f21509accbf38c';
    const WX_PAY_NOTIFY = 'http://112.74.48.33:7061/pay-notify/wx-pay-order';
    const WX_TRADE_TYPE_JS = 'JSAPI'; //公众号支付
}
