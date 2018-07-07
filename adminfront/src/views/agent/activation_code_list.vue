<template>
    <div class="app-container calendar-list-container">
        <div class="filter-container">
            <el-input @keyup.enter.native="handleFilter" style="width: 200px;"  :placeholder='"激活码"'  v-model="listQuery.code"></el-input>
            <el-input @keyup.enter.native="handleFilter" style="width: 200px;"  :placeholder='"礼包名称"'  v-model="listQuery.gift_name"></el-input>
            <el-select clearable style="width: 150px"  v-model="listQuery.code_status" :placeholder='"激活码状态"'>
                <el-option :label="'未使用'" :value="1"></el-option>
                <el-option :label="'已使用'" :value="2"></el-option>
            </el-select>
            <el-date-picker
                    v-model="listQuery.activation_time"
                    type="daterange"
                    align="right"
                    value-format="yyyy-MM-dd"
                    unlink-panels
                    range-separator="至"
                    start-placeholder="使用时间开始"
                    end-placeholder="使用时间结束"
                    :picker-options="filterOption.DATE_FILTER_OPTION">
            </el-date-picker>
            <el-button  type="primary" v-waves icon="el-icon-search" @click="handleFilter"></el-button>
            <el-button  type="primary" v-waves icon="el-icon-download" @click="exportData"></el-button>
        </div>

        <!--统计栏目-->
        <el-tag size="medium">未使用总数：{{unused_total}}</el-tag>
        <el-tag size="medium">已使用总数：{{used_total}}</el-tag>
        <el-tag size="medium">售出总价：{{price_total}}</el-tag>


        <el-table :key='tableKey' :data="list" v-loading="listLoading" element-loading-text="给我一点时间" border fit highlight-current-row style="width: 100%;margin-top:15px;">
            <el-table-column width="150px"  label="激活码" prop="code"></el-table-column>
            <el-table-column width="150px" label="礼包名称" prop="gift_name"></el-table-column>
            <el-table-column width="150px" label="礼包价格(元)" prop="price">
                <template slot-scope="scope">{{scope.row.price / 100}}</template>
            </el-table-column>
            <el-table-column width="150px" label="套餐时间（天）" prop="avail_time_day"></el-table-column>
            <el-table-column width="150px" label="激活码来源" prop="origin">
                <template slot-scope="scope">{{filterOption.OTHER_CODE_ORIGIN[scope.row.origin]}}</template>
            </el-table-column>
            <el-table-column width="150px" label="用户id" prop="buyer_user_id"></el-table-column>
            <el-table-column width="150px" label="激活码状态" prop="code_status">
                <template slot-scope="scope">{{filterOption.STATUS_USED[scope.row.code_status]}}</template>
            </el-table-column>
            <el-table-column width="150px" label="创建时间" prop="code_create_time">
                <template slot-scope="scope">{{scope.row.code_create_time | formatTime('{y}-{m}-{d} {h}:{i}')}}</template>
            </el-table-column>
            <el-table-column width="150px" label="使用wxid" prop="wxid"></el-table-column>
            <el-table-column width="150px" label="使用时间" prop="activation_time">
                <template slot-scope="scope">{{scope.row.activation_time | formatTime('{y}-{m}-{d} {h}:{i}')}}</template>
            </el-table-column>
        </el-table>

        <div class="pagination-container">
            <el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange" :page-sizes="[10,20,30, 50]" :page-size="listQuery.limit" layout="total, sizes, prev, pager, next, jumper" :total="total">
            </el-pagination>
        </div>

        <el-dialog :title="dialogTitle" :visible.sync="dialogFormVisible" width="30%">
            <el-form ref="dataForm" :model="temp" label-position="left" label-width="80px" style='width: 400px; margin-left:50px;'>
                <el-form-item label="phone" prop="phone">
                    <el-input type="textarea" v-model="temp.phone"></el-input>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="dialogFormVisible = false">取消</el-button>
                <el-button type="primary" @click="saveData">确认</el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script>
    import request from '@/utils/request'
    import waves from '@/directive/waves' // 水波纹指令
    import * as filterOption from '@/utils/filter_option'

    export default {
        name: 'admin-lists',
        directives: { waves },
        data () {
            return {
                tableKey: 0,
                list: null,
                total: 0,
                online_total:0,
                offline_total:0,
                price_total:0,
                unused_total:0,
                used_total:0,
                listLoading: true,
                listQuery: {
                    page: 1,
                    limit: 20,
                    code: '',
                    gift_name: '',
                    code_status: '',
                    activation_time: ''
                },
                wx_state_list: [
                    {'state_id' : 6 , 'state_name' : '在线'},
                    {'state_id': -1, 'state_name' : '离线'}
                ],
                roles: [],
                temp: { id: undefined, name: '', description: '', coins: '', extra_coins: '', price: '' },
                dialogFormVisible: false,
                dialogTitle: '',
                filterOption: filterOption
            }
        },
        created () {
            this.getList()
        },
        methods: {
            getList () {
                this.listLoading = true
                request({ url: 'agent/activation-code-list', method: 'get', params: this.listQuery }).then(response => {
                    const result = response.data
                    if (result.code) {
                        this.$message.error(result.msg || '系统错误')
                        this.listLoading = false
                        return
                    }

                    this.list = result.data.items;
                    this.total = result.data.total;
                    this.unused_total = result.data.unused_total;
                    this.used_total = result.data.used_total;
                    this.unused_total = result.data.unused_total;
                    this.price_total = result.data.price_total;
                    this.offline_total = result.data.total.offline_total;
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
                this.dialogTitle = '添加代理'
                this.dialogFormVisible = true
                this.$nextTick(() => {
                    this.$refs['dataForm'].clearValidate()
                })
            },
            handleFilter() {
                this.listQuery.page = 1
                this.getList()
            },
            exportData() {
                request({
                    url: 'agent/export-activation-code',
                    method: 'get',
                    params: this.listQuery
                }).then(response => {
                    var a = document.createElement("a");
                    a.href = response.data.data.file;
                    a.download = response.data.data.name;
                    console.log(a)
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                })
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
            saveData () {
                this.$refs['dataForm'].validate((valid) => {
                    if (valid) {
                        let url = 'agent/be-a-agent'
                        let addMode = true
                        let params = Object.assign({}, this.temp)
                        if (params.id > 0) {
                            url = 'recharge/edit'
                            addMode = false
                        }

                        request({ url: url, method: 'post', data: params }).then(response => {
                            const ret = response.data
                            if (ret.code) {
                                this.$message.error(ret.msg || '系统错误')
                                return
                            }
                            if (addMode) {
                                this.list.unshift(ret.data)
                            } else {
                                for (const i in ret.data) {
                                    if (this.updatingRow[i]) {
                                        this.updatingRow[i] = params[i]
                                    }
                                }
                            }

                            this.dialogFormVisible = false
                            this.$notify({
                                title: '成功',
                                message: '提交成功',
                                type: 'success',
                                duration: 2000
                            })
                        }).catch(error => {
                            this.$message.error(error.message)
                        })
                    }
                })
            },
            handleDelete (idx, row) {
                this.$confirm('此操作将永久删除该管理员, 是否继续?', '确认', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    request({ url: 'recharge/del', method: 'post', data: {id: row.id} }).then(response => {
                        const ret = response.data
                        if (ret.code) {
                            this.$message.error(ret.msg || '系统错误')
                            return
                        }

                        this.$notify({
                            title: '成功',
                            message: '删除成功',
                            type: 'success',
                            duration: 2000
                        })

                        this.list.splice(idx, 1)
                    }).catch(error => {
                        this.$message.error(error.message)
                    })
                }).catch(() => {})
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
