<template>
    <div class="app-container calendar-list-container">
        <div class="content-left">
            <el-form ref="dataForm" :model="temp">
                <el-input
                        type="textarea"
                        :rows="3"
                        placeholder="请输入redis"
                        v-model="listQuery.redisCommand">
                </el-input>
                <div style="float: left;">
                    <el-button type="primary" @click="saveData('query_redis')">确认</el-button>
                </div>
            </el-form>
            <div>
                <el-input
                        type="textarea"
                        :rows="40"
                        placeholder="原生Redis"
                        v-model="originRedis">
                </el-input>
            </div>

            <div style="float: left;">
                <el-button type="primary" @click="saveData('origin_redis')">确认</el-button>
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
    import * as filterOption from '@/utils/filter_option'

    export default {
        name: 'admin-lists',
        data () {
            return {
                tableKey: 0,
                list: null,
                total: 0,
                listLoading: true,
                listQuery: {
                    page: 1,
                    limit: 20,
                    serverName:'',
                    email:'',
                    status:'',
                    redisCommand:'',
                    passwd:''
                },
                originRedis:'',
                temp: { id: undefined, name: '', description: '', coins: '', extra_coins: '', price: '' },
                dialogFormVisible: false,
                dialogTitle: '',
                filterOption: filterOption,
                jsonViewData: {}
            }
        },
        created () {
            this.getList()
        },
        methods: {
            getList () {
                this.listLoading = true
                request({ url: 'account/queryRedis', method: 'post', params: this.listQuery }).then(response => {
                    const result = response.data;
                    if (result.code) {
                        this.$message.error(result.msg || '系统错误')
                        this.listLoading = false
                        return
                    }

                    this.originRedis = result.data.origin_redis_content;
                    this.total = 10;
                    this.listLoading = false
                })
            },
            handleSizeChange (val) {
                if (this.listQuery.limit === val) {
                    return
                }
                this.listQuery.limit = val
                this.getList()
            },
            handleCurrentChange (val) {
                console.log(val)
                console.log(this.listQuery.page)
                if (this.listQuery.page === val) {
                    return
                }
                this.listQuery.page = val
                this.getList()
            },
            resetTemp () {
                let temp = {}
                for (const i in this.temp) {
                    temp[i] = ''
                }
                this.temp = temp
            },
            handleCreate () {
                this.resetTemp()
                this.dialogTitle = '添加充值套餐'
                this.dialogFormVisible = true
                this.$nextTick(() => {
                    this.$refs['dataForm'].clearValidate()
                })
            },
            handleFilter() {
                this.listQuery.page = 1
                this.getList()
            },
            handleUpdate (idx, row) {
                this.dialogTitle = '编辑充值套餐'
                this.temp = Object.assign({}, row) // copy obj
                this.updatingRow = row;
                this.dialogFormVisible = true
                this.$nextTick(() => {
                    this.$refs['dataForm'].clearValidate()
                })
            },
            saveData (type) {
                let url = 'account/queryRedisSave';
                let params = Object.assign({}, this.listQuery);

                if(type == 'origin_redis') {
                    params.type = 'origin_redis';
                    params.origin_redis = this.originRedis;
                }
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
                        duration: 3000
                    })
                }).catch(error => {
                    this.$message.error(error.message)
                })
            }
        },
        filters: {
            wxStateFilter(status) {
                if(status == 6) {
                    return 'success';
                }else {
                    return 'info';
                }
            }
            }
    }
</script>

<style scoped>
    .app-container {
        display: flex;
    }
    el-tag {
        display:block
    }
    .content-left {
        flex: 1;
        padding-right: 10px;
    }
    .content-right {
        flex: 1;
    }
</style>