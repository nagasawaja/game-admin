<template>
    <div class="app-container calendar-list-container">
        <div class="filter-container">
            <el-input @keyup.enter.native="handleFilter" style="width: 200px;"  :placeholder='"用户名"'  v-model="listQuery.user_name"></el-input>
            <el-input @keyup.enter.native="handleFilter" style="width: 200px;"  :placeholder='"用户id"'  v-model="listQuery.user_id"></el-input>
            <el-input @keyup.enter.native="handleFilter" style="width: 200px;"  :placeholder='"微信id"'  v-model="listQuery.wx_id"></el-input>
            <el-select clearable style="width: 150px"  v-model="listQuery.state_id" :placeholder='"微信状态"'>
                <el-option v-for="item in wx_state_list" :label="item.state_name" :value="item.state_id">
                </el-option>
            </el-select>
            <el-date-picker
                    v-model="listQuery.login_time"
                    type="daterange"
                    align="right"
                    value-format="yyyy-MM-dd"
                    unlink-panels
                    range-separator="至"
                    start-placeholder="登录时间开始"
                    end-placeholder="登录时间结束"
                    :picker-options="filterOption.DATE_FILTER_OPTION">
            </el-date-picker>
            <el-button class="filter-item" type="primary" v-waves icon="el-icon-search" @click="handleFilter"></el-button>
        </div>
        <el-tag size="medium">总数：{{total}}</el-tag>
        <el-tag size="medium">在线总数：{{online_total}}</el-tag>

        <el-table :key='tableKey' :data="list" v-loading="listLoading" element-loading-text="给我一点时间" border fit highlight-current-row style="width: 100%;margin-top:15px;">
            <el-table-column width="65px"  label="用户id" prop="user_id"></el-table-column>
            <el-table-column width="150px" label="用户名" prop="user_name"></el-table-column>
            <el-table-column width="150px" label="微信id" prop="wx_id"></el-table-column>
            <el-table-column width="150px" label="昵称" prop="nickname"></el-table-column>
            <el-table-column width="100px" label="备注" prop="remark"></el-table-column>
            <el-table-column width="100px" label="微信状态" prop="wx_state">
                <template slot-scope="scope">
                    <el-tag :type="scope.row.wx_state | wxStateFilter">{{scope.row.wx_state == 6 ? "在线" : "离线"}}</el-tag>
                </template>
            </el-table-column>
            <el-table-column width="150px" label="微信添加时间" prop="wx_id_create_time">
                <template slot-scope="scope">{{scope.row.wx_id_create_time | formatTime('{y}-{m}-{d} {h}:{i}')}}</template>
            </el-table-column>
            <el-table-column width="150px" label="最后一次登录时间" prop="wx_login_time">
                <template slot-scope="scope">{{scope.row.login_time | formatTime('{y}-{m}-{d} {h}:{i}')}}</template>
            </el-table-column>
            <el-table-column width="150px" label="已开通的功能服务" >
                <template slot-scope="scope">
                    <div  v-if="scope.row.service_name != ''" v-for="item in scope.row.service_name.split(',')">{{item}}</div>
                </template>
            </el-table-column>
            <el-table-column width="320px" label="功能服务结束时间" >
                <template slot-scope="scope">
                    <div  v-for="item in scope.row.service_end_time.split(',')">{{item | formatTime('{y}-{m}-{d} {h}:{i}', true)}}</div>
                </template>
            </el-table-column>
            <!--<el-table-column label="操作" width="230" v-show="" class-name="small-padding fixed-width" align="center">-->
            <!--<template slot-scope="scope">-->
            <!--<el-button type="primary" size="mini" @click="handleUpdate(scope.$index, scope.row)">编辑</el-button>-->
            <!--<el-button size="mini" type="danger" @click="handleDelete(scope.$index,scope.row)">删除-->
            <!--</el-button>-->
            <!--</template>-->
            <!--</el-table-column>-->
        </el-table>

        <div class="pagination-container">
            <el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange" :page-sizes="[10,20,30, 50]" :page-size="listQuery.limit" layout="total, sizes, prev, pager, next, jumper" :total="total">
            </el-pagination>
        </div>

        <el-dialog :title="dialogTitle" :visible.sync="dialogFormVisible" width="30%">
            <el-form ref="dataForm" :model="temp" label-position="left" label-width="80px" style='width: 400px; margin-left:50px;'>
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
                    phone: '',
                    user_name: '',
                    user_id: '',
                    user_type: '',
                    register_time: '',
                    state_id: '',
                    login_time: '',
                    wx_id: ''
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
                request({ url: 'wx/lists', method: 'get', params: this.listQuery }).then(response => {
                    const result = response.data
                    if (result.code) {
                        this.$message.error(result.msg || '系统错误')
                        this.listLoading = false
                        return
                    }

                    this.list = result.data.items;
                    this.total = result.data.total.total;
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
            saveData () {
                this.$refs['dataForm'].validate((valid) => {
                    if (valid) {
                        let url = 'recharge/add'
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
