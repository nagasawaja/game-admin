<template>
    <div class="app-container calendar-list-container">
        <div class="content-left">
            <div>
                pes
                <div>
                    <el-button size="medium" style="" type="success" @click="clearRedisAccountCache('pes')">cleanAccountCache</el-button>
                    <el-button size="medium" style="" type="success" @click="recoverAccountStatus('pes')">reset异常帐号</el-button>
                    <el-button size="medium" style="" type="success" @click="getGameConfig('pes')">getGameConfig</el-button>
                </div>
            </div>
            <div>
                id5
                <div>
                    <el-button size="medium" style="" type="success" @click="clearRedisAccountCache('id5')">cleanAccountCache</el-button>
                    <el-button size="medium" style="" type="success" @click="recoverAccountStatus('id5')">reset异常帐号</el-button>
                    <el-button size="medium" style="" type="success" @click="getGameConfig('id5')">getGameConfig</el-button>
                </div>
            </div>
            <div>
                id5Android
                <div>
                    <el-button size="medium" style="" type="success" @click="clearRedisAccountCache('id5Android')">cleanAccountCache</el-button>
                    <el-button size="medium" style="" type="success" @click="recoverAccountStatus('id5Android')">reset异常帐号</el-button>
                    <el-button size="medium" style="" type="success" @click="getGameConfig('id5Android')">getGameConfig</el-button>
                </div>
            </div>
            <div>
                mz
                <div>
                    <el-button size="medium" style="" type="success" @click="clearRedisAccountCache('mz')">cleanAccountCache</el-button>
                    <el-button size="medium" style="" type="success" @click="recoverAccountStatus('mz')">reset异常帐号</el-button>
                    <el-button size="medium" style="" type="success" @click="getGameConfig('mz')">getGameConfig</el-button>
                </div>
            </div>
            <div>
                f7
                <div>
                    <el-button size="medium" style="" type="success" @click="clearRedisAccountCache('f7')">cleanAccountCache</el-button>
                    <el-button size="medium" style="" type="success" @click="recoverAccountStatus('f7')">reset异常帐号</el-button>
                    <el-button size="medium" style="" type="success" @click="getGameConfig('f7')">getGameConfig</el-button>
                </div>
                idcard
                <div>
                    <el-button size="medium" style="" type="info" @click="getIdCard()">获取一个idcard</el-button>
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
            })
        },
        data () {
            return {
                jsonViewData: {},
            }
        },
        methods: {
            clearRedisAccountCache(gameName) {
                request({url:'mao/clearRedisAccountCache', method: 'post', data:{gameName:gameName}}).then(response => {
                    let responseBody = response.data;
                    this.jsonViewData = responseBody;
                })
            },
            getIdCard() {
                request({url:'mao/getIdCard', method: 'post'}).then(response => {
                    let responseBody = response.data;
                    this.jsonViewData = responseBody;
                })
            },
            getGameConfig() {
                request({url:'mao/getGameConfig', method: 'post', data:{gameName:gameName}}).then(response => {
                    let responseBody = response.data;
                    this.jsonViewData = responseBody;
                })
            },
            setGameConfig() {
                request({url:'mao/getGameConfig', method: 'post', data:{gameName:gameName}}).then(response => {
                    let responseBody = response.data;
                    this.jsonViewData = responseBody;
                })
            },
            recoverAccountStatus(gameName) {
                request({url:'mao/recoverAccountStatus', method: 'post', data:{gameName:gameName}}).then(response => {
                    let responseBody = response.data;
                    this.jsonViewData = responseBody;
                })
            },
        },
        filters: {}
    }
</script>
