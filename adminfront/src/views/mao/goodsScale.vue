<template>
    <div class="app-container calendar-list-container">
        <div class="filter-container">
            <el-date-picker
                    v-model="listQuery.stc_create_datetime"
                    align="right"
                    type="datetime"
                    value-format="yyyy-MM-dd HH:00:00"
                    placeholder="选择开始日期"
                    :default-value="listQuery.stc_create_datetime">
            </el-date-picker>

            <el-button class="filter-item" type="primary" icon="el-icon-search" @click="handleFilter"></el-button>
            <el-button class="filter-item" type="primary" icon="el-icon-download" @click="markAccountSoldOut"></el-button>
        </div>

        <el-table :key='tableKey' :data="list" v-loading="listLoading" element-loading-text="给我一点时间" border fit highlight-current-row style="width: 100%;margin-top:15px;">
            <el-table-column width="245px" label="gameTitle" prop="title"></el-table-column>
            <el-table-column width="100px" label="sale_count" prop="sale_count"></el-table-column>
            <el-table-column width="85px" label="goodsTc" prop="goods_total_count"></el-table-column>
            <el-table-column width="70px" label="stc" prop="stc"></el-table-column>
            <el-table-column width="70px" label="game_id" prop="game_id"></el-table-column>
            <el-table-column width="300px" label="url" prop="url"></el-table-column>
            <el-table-column width="150px" label="id" prop="id"></el-table-column>
        </el-table>

        <div class="pagination-container">
            <el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange" :page-sizes="[10,20,30, 500]" :page-size="listQuery.limit" layout="total, sizes, prev, pager, next, jumper" :total="total">
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
    import '@/utils/common_function'

    export default {
        name: 'admin-lists',
        data () {
            var stcCreate_datetime = new Date();
            return {
                tableKey: 0,
                list: null,
                total: 0,
                listLoading: true,
                textarea: '',
                listQuery: {
                    page: 1,
                    limit: 500,
                    serverName:'',
                    getNumber:'',
                    email:'',
                    status:'',
                    oubo:'',
                    signDay:'',
                    stc_create_datetime:stcCreate_datetime.format("yyyy-MM-dd hh:00:00"),
                    game_id:0,
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
                request({ url: 'mao/goodsScale', method: 'post', params: this.listQuery }).then(response => {
                    const result = response.data;
                    if (result.code) {
                        this.$message.error(result.msg || '系统错误')
                        this.listLoading = false
                        return
                    }

                    this.list = result.data.items;
                    this.total = result.data.total;
                    this.listLoading = false
                })
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
