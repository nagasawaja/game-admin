export const STATUS_RECHARGE_ORDERS = {'1' : '已支付', '2' : '待支付', '3' : '已取消'};
export const STATUS_VALID = 1; //开启
export const STATUS_INVALID = 2; //关闭

export const TYPE_SERVICE_PRODUCT_CHARGE = 1; //收费
export const TYPE_SERVICE_PRODUCT_FREE = 2; //免费

export const gameIdMap = {6378:"f7", 7266:"mz", 6587:"id5", 7744:"pes", "6586":"id5Android", "10311":"yysygw"};

export const statusAccount = [
    {value:'4', label:'封禁4'}, {value:'6', label:'超时6'}, {value:'8', label:'未知8'},
    {value:'1', label:'新号1'}, {value:'2', label:'流程2'},
    {value:'3', label:'已卖3'}, {value:'5', label:'密错5'},{value:'7', label:'停用7'},
];

export const orderByOption = [{value:"game_update_time", label:"game_update_time"}, {value:"update_time desc", label:"update_time desc"}];
export const id5ServerList = [{value:"163master", label:"163master"}, {value:"163masterAndroid", label:"163masterAndroid"}];
export const id5ExtraFieldList = [{ "value": "jiusaiji"}];

export const yesOrNoCssType = {
    1: 'success',
    0: 'danger'
};

export const yesOrNoShowText = {
    0: "否",
    1: "是",
};

export const tradeStatusShowText = {0: '待支付', 1: '交易中', 2: '已成功', 3: '已失败', 4: '已取消'};