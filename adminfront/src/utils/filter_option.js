export const TYPE_SERVICE_PRODUCT = {'1' : '收费', '2' : '免费'};

export const STATUS = {'1' : '有效', '2' : '无效'}
export const STATUS_CSS_TYPE = {'1' : 'success', '2' : 'danger'}
export const STATUS_USED = {'1' : '未使用', '2' : '已使用'}
export const STATUS_SERVICE = {"1": "开发中", "2": "已上线", "3":"下线"}
export const STATUS_SERVICE_CSS_TYPE = {"1": "info", "2": "success", "3": "danger"}


export const OTHER_CODE_ORIGIN = {'1':"系统", '2' : '代理购买'};

export const DATE_FILTER_OPTION = {
    shortcuts: [{
        text: '最近一周',
        onClick(picker) {
            const end = new Date();
            const start = new Date();
            start.setTime(start.getTime() - 3600 * 1000 * 24 * 7);
            picker.$emit('pick', [start, end]);
        }
    }, {
        text: '最近一个月',
        onClick(picker) {
            const end = new Date();
            const start = new Date();
            start.setTime(start.getTime() - 3600 * 1000 * 24 * 30);
            picker.$emit('pick', [start, end]);
        }
    }, {
        text: '最近三个月',
        onClick(picker) {
            const end = new Date();
            const start = new Date();
            start.setTime(start.getTime() - 3600 * 1000 * 24 * 90);
            picker.$emit('pick', [start, end]);
        }
    }]
};
