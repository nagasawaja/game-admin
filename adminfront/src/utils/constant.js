export const STATUS_RECHARGE_ORDERS = {'1' : '已支付', '2' : '待支付', '3' : '已取消'};
export const STATUS_VALID = 1; //开启
export const STATUS_INVALID = 2; //关闭

export const TYPE_SERVICE_PRODUCT_CHARGE = 1; //收费
export const TYPE_SERVICE_PRODUCT_FREE = 2; //免费

export const gameIdMap = {6378:"f7", 7266:"mz", 6587:"id5", 7744:"pes", 6586:"id5_android", 10311:"yysygw", 7539:"pes_android"};

export const statusAccount = [
    {value:'1', label:'新号1'}, {value:'2', label:'流程2'}, {value:'3', label:'已卖3'},
    {value:'4', label:'封禁4'}, {value:'5', label:'密码错误5'},{value:'6', label:'超时6'},
    {value:'7', label:'疑似有密保7'},{value:'8', label:'未知8'},{value:'9', label:'身份证错误9'},
  {value:'10', label:'退回去的封禁帐号10'},{value:'11', label:'密码错误11'},{value:'12', label:'未知12'},
    {value:'900', label:'神器使错误900'}, {value:'901', label:'密错5'},{value:'902', label:'高欧怀疑被封902'},
  {value:'903', label:'帐号安全验证903'},{value:'904', label:'帐号锁定904'},{value:'905', label:'帐号冻结905'},
  {value:'906', label:'实名未通过906'},{value:'907', label:'认定未成年907'},{value:'908', label:'帐号不存在908'},
  {value:'910', label:'人脸识别910'},{value:'911', label:'实名信息无效911'},
];

export const orderByOption = [{value:"game_update_time", label:"game_update_time"}, {value:"update_time desc", label:"update_time desc"}];
export const id5ServerList = [{value:"id5_ios", label:"id5_ios"}, {value:"id5_android", label:"id5_android"}];
export const pesServerList = [{value:"pes_ios", label:"pes_ios"}, {value:"pes_android", label:"pes_android"}];
export const id5ExtraFieldList = [{ "value": "jiusaiji"}, {"value" : "123"}, {"value":"over"}, {"value":"id5AndroidNewAccount"}];

export const yesOrNoCssType = {
    1: 'success',
    0: 'danger'
};

export const yesOrNoShowText = {
    0: "否",
    1: "是",
};

export const tradeStatusShowText = {0: '待支付', 1: '交易中', 2: '已成功', 3: '已失败', 4: '已取消'};
