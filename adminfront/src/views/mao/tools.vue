<template>
    <div>
        <div>
            football
        </div>
        <el-button size="medium" style="" type="primary" @click="resetFootballEmail">获取邮件</el-button>
        <div>
            <div>
                dream
            </div>
            <el-button size="medium" style="" type="danger" slot="reference">重置账号2</el-button>
        </div>
        <div>
            <div>
                f7
            </div>
            <el-button size="medium" style="" type="danger" slot="reference">重置账号3</el-button>
        </div>
    </div>
</template>

<script>
    import request from '@/utils/request'
    export default {
        name: 'admin-lists',
        methods: {
            //
            resetFootballEmail() {
                let url = 'footballAccount/resetEmail';
                let params = Object.assign({}, this.listQuery);
                request({ url: url, method: 'post', data: params }).then(response => {
                    const ret = response.data;
                    if (ret.code) {
                        this.$message.error(ret.msg || '系统错误')
                        return
                    }
                    alert(ret.data.effectRowCount);
                    this.$notify({
                        title: '成功',
                        message: '提交成功',
                        type: 'success',
                        duration: 2000
                    })
                }).catch(error => {
                    this.$message.error(error.message)
                })
            },
        },
        filters: {}
    }
</script>
