<template>
    <div class="app-container calendar-list-container">
        <div class="content-left">
            <div>
                football
                <div>
                    <el-button size="medium" style="" type="primary" @click="resetFootballEmail">获取邮件</el-button>
                </div>
            </div>
            <div>
                id5
                <div>
                    <el-button size="medium" style="" type="primary" @click="resetFootballEmail">获取邮件</el-button>
                </div>
            </div>

        </div>
        <div class="content-right">
            <json-viewer
                    :value= jsonViewData
                    :expand-depth=50
                    copyable
                    boxed
                    sort></json-viewer>
        </div>
    </div>
</template>

<script>
    import request from '@/utils/request'
    export default {
        data () {
            return {
                jsonViewData: {}
            }
        },
        methods: {
            resetFootballEmail() {
                let url = 'footballAccount/resetEmail';
                let params = Object.assign({}, this.listQuery);
                request({ url: url, method: 'post', data: params }).then(response => {
                    const ret = response.data;
                    if (ret.code) {
                        this.$message.error(ret.msg || '系统错误')
                        return
                    }
                    this.jsonViewData = ret;
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
