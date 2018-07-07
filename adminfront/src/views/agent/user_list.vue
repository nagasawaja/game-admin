<template>
    <div class="app-container calendar-list-container">
        <div class="filter-container">
            <el-input @keyup.enter.native="handleFilter" style="width: 200px;"  :placeholder='"用户名"'  v-model="listQuery.user_name"></el-input>
            <el-input @keyup.enter.native="handleFilter" style="width: 200px;"  :placeholder='"余额最低"'  v-model="listQuery.balance_down_limit"></el-input>
            <el-button class="filter-item" type="primary" v-waves icon="el-icon-search" @click="handleFilter"></el-button>
            <el-button class="filter-item" style="margin-left: 10px;" @click="handleCreate" type="primary"
                       icon="el-icon-edit">添加代理
            </el-button>
        </div>

        <el-table :key='tableKey' :data="list" v-loading="listLoading" element-loading-text="给我一点时间" border fit highlight-current-row style="width: 100%;margin-top:15px;">
            <el-table-column width="65px"  label="用户id" prop="user_id"></el-table-column>
            <el-table-column width="150px" label="用户名" prop="user_name"></el-table-column>
            <el-table-column width="150px" label="余额" prop="balance"></el-table-column>
            <el-table-column width="150px" label="成为代理时间" prop="agent_create_time">
                <template slot-scope="scope">{{scope.row.agent_create_time | formatTime('{y}-{m}-{d} {h}:{i}')}}</template>
            </el-table-column>
            <el-table-column label="操作" width="230" class-name="small-padding fixed-width" align="center">
                <template slot-scope="scope">
                    <el-button type="primary" size="mini" @click="handleRecharge(scope.$index, scope.row)">充值</el-button>
                </template>
            </el-table-column>

        </el-table>

        <div class="pagination-container">
            <el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange" :page-sizes="[10,20,30, 50]" :page-size="listQuery.limit" layout="total, sizes, prev, pager, next, jumper" :total="total">
            </el-pagination>
        </div>

        <el-dialog :title="dialogTitle" :visible.sync="dialogFormVisible" width="30%">
            <el-form :rules="rules" ref="dataForm" :model="temp" label-position="left" label-width="80px" style='width: 400px; margin-left:50px;'>
                <el-form-item label="用户名">
                    <el-input type="text" v-model="temp.user_name"></el-input>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="dialogFormVisible = false">取消</el-button>
                <el-button type="primary" @click="saveData">确认</el-button>
            </div>
        </el-dialog>

        <el-dialog :title="dialogTitle" :visible.sync="rechargeDialogFormVisible" width="30%">
            <el-form :rules="rules" ref="dataForm" :model="temp" label-position="left" label-width="80px" style='width: 400px; margin-left:50px;'>
                <el-form-item label="充值的金额">
                    <el-input  v-model="temp.balance_up"></el-input>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="rechargeDialogFormVisible = false">取消</el-button>
                <el-button type="primary" @click="saveBalance">确认</el-button>
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
                listLoading: true,
                listQuery: {
                    page: 1,
                    limit: 20,
                    user_name: '',
                    balance_down_limit: '',
                    balance_up_limit: ''
                },
                wx_state_list: [
                    {'state_id' : 6 , 'state_name' : '在线'},
                    {'state_id': -1, 'state_name' : '离线'}
                ],
                roles: [],
                balance:0,
                temp: { id: undefined, name: '', description: '', coins: '', extra_coins: '', price: '', balance:0, balance_up:0, user_name:""},
                dialogFormVisible: false,
                rechargeDialogFormVisible: false,
                dialogTitle: '',
                rules: {
                    name: [{ required: true, trigger: 'blur', message: '请输入套餐名称' }],
                    coins: [{ required: true, trigger: 'blur', message: '请输入金币数' }, { type: 'integer', message: '金币必须为整数值', trigger: 'blur' }, { type: 'integer', min: 0, message: '金币不能少于0', trigger: 'blur' }],
                    extra_coins: [{ required: true, trigger: 'blur', message: '请输入赠送金币数' }, { type: 'integer', message: '金币必须为整数值', trigger: 'blur' }, { type: 'integer', min: 0, message: '金币不能少于0', trigger: 'blur' }],
                    price: [{ required: true, trigger: 'blur', message: '请输入价格' }, { type: 'number', message: '价格必须为数值类型' }, { type: 'number', min: 0, message: '价格不能少于0', trigger: 'blur' }]
                }
            }
        },
        created () {
            this.getList()
        },
        methods: {
            getList () {
                this.listLoading = true
                request({ url: 'agent/user-list', method: 'get', params: this.listQuery }).then(response => {
                    const result = response.data
                    if (result.code) {
                        this.$message.error(result.msg || '系统错误')
                        this.listLoading = false
                        return
                    }

                    this.list = result.data.items;
                    this.total = result.data.total;
                    this.online_total = result.data.total.online_total;
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
            handleUpdate (idx, row) {
                this.dialogTitle = '编辑充值套餐'
                this.temp = Object.assign({}, row) // copy obj
                this.updatingRow = row;
                this.dialogFormVisible = true
                this.$nextTick(() => {
                    this.$refs['dataForm'].clearValidate()
                })
            },
            handleRecharge (idx, row) {
                this.dialogTitle = 'recharge'
                this.temp = Object.assign({}, row) // copy obj
                this.updatingRow = row;
                this.rechargeDialogFormVisible = true
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
                            this.getList()
                            // if (addMode) {
                            //     this.list.unshift(ret.data)
                            // } else {
                            //     for (const i in ret.data) {
                            //         if (this.updatingRow[i]) {
                            //             this.updatingRow[i] = params[i]
                            //         }
                            //     }
                            // }

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
            saveBalance () {
                this.$refs['dataForm'].validate((valid) => {
                    if (valid) {
                        let url = 'agent/save-agent-data'
                        let addMode = false
                        let params = Object.assign({}, this.temp)

                        request({ url: url, method: 'post', data: params }).then(response => {
                            const ret = response.data
                            if (ret.code) {
                                this.$message.error(ret.msg || '系统错误')
                                return
                            }
                            this.getList()
                            // if (addMode) {
                            //     this.list.unshift(ret.data)
                            // } else {
                            //     for (const i in ret.data) {
                            //         if (this.updatingRow[i]) {
                            //             this.updatingRow[i] = params[i]
                            //         }
                            //     }
                            // }

                            this.rechargeDialogFormVisible = false
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
