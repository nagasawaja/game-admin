<template>
    <div class="app-container calendar-list-container">
        <div class="filter-container">
            帐号Id：<el-input @keyup.enter.native="handleFilter" style="width: 200px;"  placeholder='帐号Id'  v-model="listQuery.accountId"></el-input>
            邮箱：<el-input @keyup.enter.native="handleFilter" style="width: 200px;"  placeholder='邮箱'  v-model="listQuery.email"></el-input>
            状态：<el-input @keyup.enter.native="handleFilter" style="width: 200px;"  placeholder='状态'  v-model="listQuery.status"></el-input>
            状态2：  <el-select style="width: 500px;" v-model="listQuery.statusList" multiple placeholder="请选择">
            <el-option
                    v-for="item in constant.statusAccount"
                    :key="item.value"
                    :label="item.label"
                    :value="item.value">
            </el-option>
        </el-select>
            <br/>
            服务器：<el-input @keyup.enter.native="handleFilter" style="width: 200px;"  placeholder='服务器'  v-model="listQuery.serverName"></el-input>
            金币：<el-input @keyup.enter.native="handleFilter" style="width: 200px;"  placeholder='金币1'  v-model="listQuery.gold_1"></el-input>-<el-input @keyup.enter.native="handleFilter" style="width: 200px;"  placeholder='金币2'  v-model="listQuery.gold_2"></el-input>
            资金：<el-input @keyup.enter.native="handleFilter" style="width: 200px;"  placeholder='资金1'  v-model="listQuery.money_1"></el-input>-<el-input @keyup.enter.native="handleFilter" style="width: 200px;"  placeholder='资金2'  v-model="listQuery.money_2"></el-input>
            <br/>
            黑球：<el-input @keyup.enter.native="handleFilter" style="width: 200px;"  placeholder='黑球1'  v-model="listQuery.black_player_1"></el-input>-<el-input @keyup.enter.native="handleFilter" style="width: 200px;"  placeholder='黑球2'  v-model="listQuery.black_player_2"></el-input>
<!--            金球：<el-input @keyup.enter.native="handleFilter" style="width: 200px;"  placeholder='金球1'  v-model="listQuery.gold_player_1"></el-input>-<el-input @keyup.enter.native="handleFilter" style="width: 200px;"  placeholder='金球2'  v-model="listQuery.gold_player_2"></el-input>-->
<!--            银球：<el-input @keyup.enter.native="handleFilter" style="width: 200px;"  placeholder='银球1'  v-model="listQuery.silver_player_1"></el-input>-<el-input @keyup.enter.native="handleFilter" style="width: 200px;"  placeholder='银球2'  v-model="listQuery.silver_player_2"></el-input>-->
            <br/>
            登录天数：<el-input @keyup.enter.native="handleFilter" style="width: 200px;"  placeholder='登录天数1'  v-model="listQuery.sign_times_1"></el-input>-<el-input @keyup.enter.native="handleFilter" style="width: 200px;"  placeholder='登录天数2'  v-model="listQuery.sign_times_2"></el-input>
            错误次数：<el-input @keyup.enter.native="handleFilter" style="width: 200px;"  placeholder='错误次数1'  v-model="listQuery.error_times_1"></el-input>-<el-input @keyup.enter.native="handleFilter" style="width: 200px;"  placeholder='错误次数2'  v-model="listQuery.error_times_2"></el-input>
            <el-button class="filter-item" type="primary" icon="el-icon-search" @click="handleFilter">搜索</el-button>
            <br/>
            最后更新时间：<el-date-picker
                v-model="listQuery.goods_detail_update_date1"
                align="right"
                type="date"
                value-format="yyyy-MM-dd"
                placeholder="选择开始日期"
                :default-value="listQuery.goods_detail_update_date1">
        </el-date-picker>
            -
            <el-date-picker
                    v-model="listQuery.goods_detail_update_date2"
                    align="right"
                    type="date"
                    value-format="yyyy-MM-dd"
                    placeholder="选择开始日期"
                    :default-value="listQuery.goods_detail_update_date2">
            </el-date-picker>
            创建时间：
            <el-date-picker
                    v-model="listQuery.stc_create_datetime_start"
                    align="right"
                    type="datetime"
                    value-format="yyyy-MM-dd HH:00:00"
                    placeholder="选择开始日期"
                    :default-value="listQuery.stc_create_datetime_start">
            </el-date-picker>
            <el-date-picker
                    v-model="listQuery.stc_create_datetime_end"
                    align="right"
                    type="datetime"
                    value-format="yyyy-MM-dd HH:00:00"
                    placeholder="选择结束日期"
                    :default-value="listQuery.stc_create_datetime_end">
            </el-date-picker>
        </div>

        <el-table :key='tableKey' :data="list" v-loading="listLoading" element-loading-text="给我一点时间" border fit highlight-current-row style="width: 100%;margin-top:15px;">
            <el-table-column width="65px"  label="帐号id" prop="id"></el-table-column>
            <el-table-column width="200px" label="邮箱" prop="email"></el-table-column>
            <el-table-column width="125px" label="密码" prop="passwd"></el-table-column>
            <el-table-column width="100px" label="状态" prop="status">
                <template slot-scope="{row}">
                    <div v-if="row.edit">
                        <el-input @blur.prevent="editStatus(row, 'confirm')" v-model="row.status" class="edit-input" size="small" />
                    </div>
                    <div v-else @click="row.edit = !row.edit">
                        {{row.status}}
                    </div>
                </template>
            </el-table-column>
            <el-table-column width="100px" label="金币" prop="gold"></el-table-column>
            <el-table-column width="100px" label="资金" prop="money"></el-table-column>
            <el-table-column width="100px" label="黑球" prop="black_player"></el-table-column>
<!--            <el-table-column width="100px" label="金球" prop="gold_player"></el-table-column>-->
<!--            <el-table-column width="100px" label="银球" prop="silver_player"></el-table-column>-->

            <el-table-column width="100px" label="登录天数" prop="sign_times"></el-table-column>
            <el-table-column width="150px" label="create_time" prop="create_time">
                <template slot-scope="scope">{{scope.row.create_time | formatTime('{y}-{m}-{d} {h}:{i}:{s}')}}</template>
            </el-table-column>
            <el-table-column width="150px" label="game_update_time" prop="game_update_time">
                <template slot-scope="scope">{{scope.row.game_update_time | formatTime('{y}-{m}-{d} {h}:{i}')}}</template>
            </el-table-column>
            <el-table-column width="100px" label="错误次数" prop="error_times"></el-table-column>
<!--            <el-table-column width="150px" label="登陆时间" prop="game_update_time">-->
<!--                <template slot-scope="scope">{{scope.row.game_update_time | formatTime('{y}-{m}-{d} {h}:{i}')}}</template>-->
<!--            </el-table-column>-->
<!--            <el-table-column width="150px" label="邮件时间" prop="email_time">-->
<!--                <template slot-scope="scope">{{scope.row.email_time | formatTime('{y}-{m}-{d} {h}:{i}')}}</template>-->
<!--            </el-table-column>-->
<!--            <el-table-column width="150px" label="创建时间" prop="create_time">-->
<!--                <template slot-scope="scope">{{scope.row.create_time | formatTime('{y}-{m}-{d} {h}:{i}')}}</template>-->
<!--            </el-table-column>-->
            <el-table-column width="150px" label="服务器" prop="server_name"></el-table-column>
            <el-table-column width="150px" label="三无帐号" prop="is_clean">
                <template slot-scope="scope">
                    <el-tag>{{scope.row.is_clean==1?'是':'否'}}</el-tag>
                </template>
            </el-table-column>
        </el-table>

        <div class="pagination-container">
            <el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange" :page-sizes="[10,20,30, 50]" :page-size="listQuery.limit" layout="total, sizes, prev, pager, next, jumper" :total="total">
            </el-pagination>
        </div>

        <el-dialog :title="dialogTitle" :visible.sync="dialogFormVisible" width="35%">
            <el-input
                    type="textarea"
                    :rows="100"
                    placeholder="请输入内容"
                    v-model="textarea">
            </el-input>
            <div slot="footer" class="dialog-footer">
                <el-button @click="dialogFormVisible = false">关闭</el-button>
            </div>
        </el-dialog>

    </div>
</template>

<script>
    import request from '@/utils/request'
    import * as filterOption from '@/utils/filter_option'

    export default {
        name: 'admin-lists',
        data () {
            return {
                constant: require('@/utils/constant'),
                tableKey: 0,
                list: null,
                total: 0,
                listLoading: true,
                textarea: '',
                listQuery: {
                    page: 1,
                    limit: 20,
                    serverName:'',
                    email:'',
                    status:'',
                    gold_1: '',
                    gold_2: '',
                    money_1: '',
                    money_2: '',
                    black_player_1: '',
                    black_player_2: '',
                    gold_player_1: '',
                    gold_player_2: '',
                    silver_player_1: '',
                    silver_player_2: '',
                    error_times_1: '',
                    error_times_2: '',
                    sign_times_1: '',
                    sign_times_2: '',
                    accountId:'',
                    email_time:'',
                    goods_detail_update_date1:'',
                    goods_detail_update_date2:'',
                    statusList: [],
                    stc_create_datetime_start: '',
                    stc_create_datetime_end: '',
                },
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
                this.listLoading = true;
                request({ url: 'footballAccount/lists', method: 'post', params: this.listQuery }).then(response => {
                    const result = response.data;
                    if (result.code) {
                        this.$message.error(result.msg || '系统错误')
                        this.listLoading = false
                        return
                    }

                    this.list = result.data.rows;
                    this.total = result.data.total;
                    this.listLoading = false
                })
            },
            editStatus(row, handleType) {
                row.edit = !row.edit;
                if(handleType === "confirm") {
                    // confirm edit
                    let postData = {
                        "sql":"update account set status = " + row.status + " where id = " + row.id,
                        "passwd":"benibenija",
                        "type":"sql",
                    };
                    console.log(postData);
                    request({ url: "account/query-sql-save", method: 'post', data: postData }).then(response => {
                        const ret = response.data;
                        if (ret.code) {
                            this.$message.error(ret.msg || '系统错误')
                            return
                        }
                        this.$notify({
                            title: '成功',
                            message: '提交成功',
                            type: 'success',
                            duration: 5000
                        })
                    }).catch(error => {
                        this.$message.error(error.message)
                    })
                }

            },
            markAccountSoldOut () {
                this.listLoading = true;
                request({ url: 'account/mark-account-sold-out', method: 'post', params: this.listQuery }).then(response => {
                    const result = response.data;
                    if (result.code) {
                        this.$message.error(result.msg || '系统错误')
                        this.listLoading = false
                        return
                    }

                    request({ url: 'account/sold-out-account-detail', method: 'post', params: {id: result.data.id} }).then(response => {
                        const result = response.data;
                        if(result.code) {
                            this.$message.error(result.msg || '系统错误')
                            this.listLoading = false
                            return
                        }
                        this.dialogFormVisible = true;
                        this.textarea = result.data.rows.content;
                    })
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
                this.resetTemp();
                this.dialogTitle = '添加充值套餐';
                this.dialogFormVisible = true;
                this.$nextTick(() => {
                    this.$refs['dataForm'].clearValidate()
                })
            },
            handleFilter() {
                this.listQuery.page = 1;
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
