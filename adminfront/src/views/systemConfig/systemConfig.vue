<template>
    <div>
        <div>
            <el-switch v-model="systemConfig.serverStop" active-text="正常ing" inactive-text="维护ing"
                       active-value = 2 inactive-value = 1 v-on:change="saveData('serverStop')">
            </el-switch>
            <br/>
            后台标签名：
            <el-input style="width:150px" v-model="systemConfig.title"></el-input>
            <el-button type="success" icon="el-icon-check" v-on:click="saveData('title')"></el-button>
            <br/>
            代理转账最低额度：
            <el-input-number style="width:150px" v-model="systemConfig.agent_transfer_limit"></el-input-number>
            <el-button type="success" icon="el-icon-check" v-on:click="saveData('agent_transfer_limit')"></el-button>
        </div>

    </div>

</template>

<script>
    import request from '@/utils/request'
    import * as filterOption from '@/utils/filter_option'

    export default {
        name: 'admin-lists',
        data() {
            return {
                systemConfig: {
                    serverStop: null,
                    title: null,
                    agent_transfer_limit:0,
                },

                filterOption: filterOption
            }
        },
        created() {
            this.getList()
        },
        methods: {
            getList() {
                this.listLoading = true;
                request({
                    url: 'systemConfig/sys-config-menu',
                    method: 'get'
                }).then(response => {
                    const result = response.data;
                    if (result.code) {
                        this.$message.error(result.msg || '系统错误')
                        this.listLoading = false;
                        return;
                    }
                    this.systemConfig = result.data;
                    this.systemConfig.agent_transfer_limit = this.systemConfig.agent_transfer_limit / 100;
                    this.listLoading = false;
                })
            },
            saveData(submitType) {
                const aaa= {};
                if(submitType == 'agent_transfer_limit') {
                    this.systemConfig[submitType] = this.systemConfig[submitType] * 100;
                }
                aaa[submitType] = this.systemConfig[submitType]
                request({
                    url: 'systemConfig/sys-config-save',
                    method: 'post',
                    params: aaa
                }).then(response => {
                    const result = response.data;
                    if (result.code) {
                        this.$message.error(result.msg || '系统错误')
                        this.listLoading = false;
                        return;
                    }
                    this.getList();
                    this.listLoading = false;
                })
            },
        }
    }
</script>
