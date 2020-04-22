<template>
    <div class="app-container calendar-list-container">
        <div class="content-left">
            <div>
                football
                <div>
                    <el-button size="medium" style="" type="primary" @click="resetFootballEmail">获取邮件</el-button>
                    <el-button size="medium" style="" type="info" @click="revertGameStatus('football')">帐号{{(GameStatus.football == 1) ? "true":"false"}}</el-button>
                    <el-button size="medium" style="" type="success" @click="clearRedisAccountCache('football')">清理redisCache</el-button>
                </div>
            </div>
            <div>
                id5
                <div>
                    <el-button size="medium" style="" type="info" @click="revertGameStatus('id5')">帐号{{(GameStatus.id5 == 1) ? "true":"false"}}</el-button>
                    <el-button size="medium" style="" type="success" @click="clearRedisAccountCache('id5')">清理redisCache</el-button>
                </div>
            </div>
            <div>
                dream
                <div>
                    <el-button size="medium" style="" type="info" @click="revertGameStatus('dream')">帐号{{(GameStatus.dream == 1) ? "true":"false"}}</el-button>
                    <el-button size="medium" style="" type="success" @click="clearRedisAccountCache('dream')">清理redisCache</el-button>
                </div>
            </div>
            <div>
                f7
                <div>
                    <el-button size="medium" style="" type="info" @click="revertGameStatus('f7')">帐号{{(GameStatus.f7 == 1) ? "true":"false"}}</el-button>
                    <el-button size="medium" style="" type="success" @click="clearRedisAccountCache('f7')">清理redisCache</el-button>
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
        created () {
            // 初始化函数
            request({url:'mao/gameStatus', method:'post'}).then(response => {
                let responseBody = response.data;
                if (responseBody.code) {
                    this.jsonViewData = "err";
                    return
                }
                this.GameStatus = responseBody.data.items;
            })
        },
        data () {
            return {
                jsonViewData: {},
                GameStatus: {f7:2, id5:2, dream:2, football:2},
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
            revertGameStatus(gameName) {
                let modifyStatus = 0;
                if (this.GameStatus[gameName] == 1) {
                    this.GameStatus[gameName] = 2;
                    modifyStatus = 2;
                } else {
                    this.GameStatus[gameName] = 1;
                    modifyStatus = 1;
                }

                request({url:'mao/revertGameStatus', method: 'post', data:{gameName:gameName, modifyStatus:modifyStatus}}).then(response => {
                    let responseBody = response.data;
                    this.jsonViewData = responseBody;
                })
            },
            clearRedisAccountCache(gameName) {
                request({url:'mao/clearRedisAccountCache', method: 'post', data:{gameName:gameName}}).then(response => {
                    let responseBody = response.data;
                    this.jsonViewData = responseBody;
                })
            },
        },
        filters: {}
    }
</script>
