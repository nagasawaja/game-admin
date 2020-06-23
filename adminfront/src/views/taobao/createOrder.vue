<template>
    <div class="app-container calendar-list-container">
        <div class="content-left">
            <el-form ref="dataForm" :model="temp">
                <div style="float: left;">
                    <el-button type="primary" @click="createOrder">提交</el-button>
                    <el-button type="primary" @click="resetTemp">重置</el-button>
                </div>
            </el-form>
            <div>
                <el-input
                        type="textarea"
                        :rows="2"
                        placeholder="orderId"
                        v-model="listQuery.orderId">
                </el-input>
                <el-input
                        type="textarea"
                        :rows="2"
                        placeholder="description"
                        v-model="listQuery.description">
                </el-input>
                <el-input
                        type="textarea"
                        :rows="40"
                        placeholder="originContent"
                        v-model="listQuery.originContent">
                </el-input>
            </div>
        </div>
        <div class="content-right">
            <div>
                <el-input
                        type="textarea"
                        :rows="40"
                        placeholder="easyContent"
                        v-model="listQuery.easyContent">
                </el-input>
            </div>
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
                    orderId:'',
                    easyContent:'',
                    originContent:'',
                    description:'',
                },
                originRedis:'',
                temp: { id: undefined, name: '', description: '', coins: '', extra_coins: '', price: '' },
                dialogFormVisible: false,
                dialogTitle: '',
                filterOption: filterOption,
                jsonViewData: {}
            }
        },
        methods: {
            createOrder () {
                this.listLoading = true;
                request({ url: 'taobao/createOrder', method: 'post', params: this.listQuery }).then(response => {
                    const result = response.data;
                    if (result.code) {
                        this.$message.error(result.msg || '系统错误')
                        this.listLoading = false;
                        return
                    };
                    this.$message.info('新增成功');
                    this.listLoading = false
                })
            },
            resetTemp () {
                for(let key in this.listQuery){
                    this.listQuery[key] = '';
                }


            },
        },
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