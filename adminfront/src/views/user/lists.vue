<template>
    <div class="app-container calendar-list-container">
        <div class="filter-container">
            <el-input @keyup.enter.native="handleFilter" style="width: 200px;"  :placeholder='"用户名"'  v-model="listQuery.user_name"></el-input>
            <el-input @keyup.enter.native="handleFilter" style="width: 200px;"  :placeholder='"用户id"'  v-model="listQuery.user_id"></el-input>
            <el-date-picker
                    v-model="listQuery.register_time"
                    type="daterange"
                    align="right"
                    value-format="yyyy-MM-dd"
                    unlink-panels
                    range-separator="至"
                    start-placeholder="注册日期"
                    end-placeholder="注册日期"
                    :picker-options="filterOption.DATE_FILTER_OPTION">
            </el-date-picker>
            <el-date-picker
                    v-model="listQuery.login_time"
                    type="daterange"
                    align="right"
                    value-format="yyyy-MM-dd"
                    unlink-panels
                    range-separator="至"
                    start-placeholder="登录日期"
                    end-placeholder="登录日期"
                    :picker-options="filterOption.DATE_FILTER_OPTION">
            </el-date-picker>
            <el-button class="filter-item" type="primary" icon="el-icon-search" @click="handleFilter"></el-button>
        </div>

        <el-table :key='tableKey' :data="list" v-loading="listLoading" element-loading-text="给我一点时间" border fit
                  highlight-current-row style="width: 100%">
            <el-table-column width="65px" label="id" prop="id"></el-table-column>
            <el-table-column width="150px" label="用户名" prop="user_name"></el-table-column>
            <el-table-column width="150px" label="微信号数量" prop="user_wx_account"></el-table-column>
            <el-table-column width="150px" label="注册时间" prop="register_time">
                <template slot-scope="scope">{{scope.row.register_time | formatTime('{y}-{m}-{d} {h}:{i}')}}</template>
            </el-table-column>
            <el-table-column width="150px" label="最后一次登录时间" prop="login_time">
                <template slot-scope="scope">{{scope.row.login_time | formatTime('{y}-{m}-{d} {h}:{i}')}}</template>
            </el-table-column>
            <el-table-column label="操作" width="230" v-show=""  align="center">
            <template slot-scope="scope">
            <el-button type="warning" @click="showResetPassword(scope.$index,scope.row)">重置密码
            </el-button>
            </template>
            </el-table-column>
        </el-table>

        <div class="pagination-container">
            <el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange"
                           :page-sizes="[10,20,30, 50]" :page-size="listQuery.limit"
                           layout="total, sizes, prev, pager, next, jumper" :total="total">
            </el-pagination>
        </div>

        <el-dialog :title="dialogTitle" :visible.sync="dialogFormVisible" width="30%">
            <el-form  ref="dataForm" :model="temp" label-position="left" label-width="80px"
                     style='width: 400px; margin-left:50px;'>
                <el-form-item label="id" prop="id">
                    <el-input v-model="temp.name"></el-input>
                </el-form-item>
                <el-form-item label="phone" prop="phone">
                    <el-input type="textarea" v-model="temp.description"></el-input>
                </el-form-item>
                <el-form-item label="coin" prop="coin">
                    <el-input v-model.number="temp.coins"></el-input>
                </el-form-item>
                <el-form-item label="register_time" prop="register_time">
                    <el-input v-model.number="temp.extra_coins"></el-input>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="dialogFormVisible = false">取消</el-button>
                <el-button type="primary" @click="saveData">确认</el-button>
            </div>
        </el-dialog>

        <el-dialog :title="'重置密码'" :visible.sync="resetPasswordFormVisible" width="30%">
            <el-form ref="dataForm" :model="temp" label-position="left" label-width="80px" style='width: 400px; margin-left:50px;'>
                <el-form-item label="用户名" prop="user_name">
                    <el-input :disabled="true" v-model="user_name"></el-input>
                </el-form-item>
                <el-form-item label="密码" prop="reset_password">
                    <el-input v-model="reset_password"></el-input>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="resetPasswordFormVisible = false">取消</el-button>
                <el-button type="primary" @click="resetPasswordSave">确认</el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script>
    import request from '@/utils/request'
    import * as filterOption from '@/utils/filter_option'


    export default {
        name: 'admin-lists',
        data() {
            return {
                tableKey: 0,
                list: null,
                total: null,
                listLoading: true,
                listQuery: {
                    page: 1,
                    limit: 20,
                    phone: '',
                    user_name: '',
                    user_id: '',
                    user_type: '',
                    register_time: ''
                },
                roles: [],
                temp: {id: undefined, name: '', description: '', coins: '', extra_coins: '', price: ''},
                reset_password: '',
                user_name: '',
                dialogFormVisible: false,
                resetPasswordFormVisible: false,
                dialogTitle: '',
                filterOption: filterOption
            }
        },
        created() {
            this.getList()
        },
        methods: {
            getList() {
                this.listLoading = true
                request({url: 'user/lists', method: 'get', params: this.listQuery}).then(response => {
                    const result = response.data
                    if (result.code) {
                        this.$message.error(result.msg || '系统错误')
                        this.listLoading = false
                        return
                    }

                    this.list = result.data.items
                    this.list.map((v) => {
                        v.price = (v.price * 0.01).toFixed(2)
                    })
                    this.total = result.data.total
                    this.listLoading = false
                })
            },
            handleSizeChange(val) {
                if (this.listQuery.limit === val) {
                    return
                }
                this.listQuery.limit = val
                this.getList()
            },
            handleCurrentChange(val) {
                console.log(val)
                console.log(this.listQuery.page)
                if (this.listQuery.page === val) {
                    return
                }
                this.listQuery.page = val
                this.getList()
            },
            handleFilter() {
                this.listQuery.page = 1
                this.getList()
            },
            resetTemp() {
                let temp = {}
                for (const i in this.temp) {
                    temp[i] = ''
                }
                this.temp = temp
            },
            handleCreate() {
                this.resetTemp()
                this.dialogTitle = '添加充值套餐'
                this.dialogFormVisible = true
                this.$nextTick(() => {
                    this.$refs['dataForm'].clearValidate()
                })
            },
            handleUpdate(idx, row) {
                this.dialogTitle = '编辑充值套餐'
                this.temp = Object.assign({}, row) // copy obj
                this.updatingRow = row;
                this.dialogFormVisible = true
                this.$nextTick(() => {
                    this.$refs['dataForm'].clearValidate()
                })
            },
            saveData() {
                this.$refs['dataForm'].validate((valid) => {
                    if (valid) {
                        let url = 'recharge/add'
                        let addMode = true
                        let params = Object.assign({}, this.temp)
                        if (params.id > 0) {
                            url = 'recharge/edit'
                            addMode = false
                        }

                        request({url: url, method: 'post', data: params}).then(response => {
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
            showResetPassword(idx, row) {
                console.log(row.user_name)
                this.reset_password = ''
                this.user_name = row.user_name
                this.resetPasswordFormVisible = true
            },
            resetPasswordSave() {
                this.$refs['dataForm'].validate((valid) => {
                    if (valid) {
                        let url = 'user/edit-user'
                        let params = Object.assign({user_name:this.user_name, reset_password:this.reset_password})
                        request({url: url, method: 'post', data: params}).then(response => {
                            const ret = response.data
                            if (ret.code) {
                                this.$message.error(ret.msg || '系统错误')
                                return
                            }
                            this.resetPasswordFormVisible = false
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
            }
        }
    }
</script>
