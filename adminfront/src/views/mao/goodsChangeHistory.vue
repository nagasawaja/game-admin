<template>
    <div class="app-container calendar-list-container">
        <div class="filter-container">
            <el-date-picker
                v-model="listQuery.goods_detail_create_datetime_start"
                align="right"
                type="date"
                value-format="yyyy-MM-dd"
                placeholder="选择开始日期"
                :default-value="listQuery.goods_detail_create_datetime_start">
            </el-date-picker>
            <el-date-picker
                v-model="listQuery.goods_detail_create_datetime_end"
                align="right"
                type="date"
                value-format="yyyy-MM-dd"
                placeholder="选择结束日期"
                :default-value="listQuery.goods_detail_create_datetime_end">
             </el-date-picker>
            gameId：
            <el-select v-model="listQuery.game_id" filterable clearable>
                <el-option-group
                        v-for="group in final_game_rows"
                        :key="group.game_id"
                        :label="group.title"
                        :value="group.game_id">
                    <el-option
                            v-for="item in group.option"
                            :key="item.game_id"
                            :label="item.title"
                            :value="item.game_id">
                    </el-option>
                </el-option-group>
            </el-select>
            seller_name：<el-input @keyup.enter.native="handleFilter" style="width: 200px;"  placeholder='seller_name'  v-model="listQuery.seller_name"></el-input>
            goods_id：<el-input @keyup.enter.native="handleFilter" style="width: 200px;"  placeholder='goods_id'  v-model="listQuery.goods_id"></el-input>
            <br/>
            <el-button class="filter-item" type="primary" icon="el-icon-search" @click="handleFilter">查询</el-button>
        </div>
        <el-table :key='tableKey' :data="list" v-loading="listLoading" element-loading-text="给我一点时间" border fit highlight-current-row style="width: 100%;margin-top:15px;">
            <el-table-column width="210px" label="title" prop="goods_title"></el-table-column>
            <el-table-column width="85px" label="price" prop="price"></el-table-column>
            <el-table-column width="70px" label="sGc" content="123" prop="single_goods_count"></el-table-column>
            <el-table-column width="150px" label="seller_name" prop="seller_name"></el-table-column>
            <el-table-column width="150px" label="goods_sale_time" prop="goods_sale_create_datetime"></el-table-column>
            <el-table-column width="350px" label="goods_url" prop="goods_url">
                <template slot-scope="scope">
                    <a :href="scope.row.goods_url" target="_blank" class="buttonText">{{scope.row.goods_url}}</a>
                </template>
            </el-table-column>
            <el-table-column width="150px" label="goods_id" prop="goods_id"></el-table-column>
            <el-table-column width="210px" label="title" prop="game_title"></el-table-column>
            <el-table-column width="150px" label="game_id" prop="game_id"></el-table-column>
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
            var goodsDetail_create_datetime_end = new Date();
            goodsDetail_create_datetime_end.setDate(goodsDetail_create_datetime_end.getDate() + 1);
            return {
                tableKey: 0,
                list: null,
                total: 0,
                listLoading: true,
                final_game_rows:[],
                textarea: '',
                listQuery: {
                    page: 1,
                    limit: 500,
                    serverName:'',
                    seller_name:'',
                    goods_id:'',
                    getNumber:'',
                    email:'',
                    status:'',
                    oubo:'',
                    signDay:'',
                    goods_detail_create_datetime_start:new Date().format("yyyy-MM-dd"),
                    goods_detail_create_datetime_end:goodsDetail_create_datetime_end.format("yyyy-MM-dd"),
                    game_id:6587,
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
                request({ url: 'mao/goodsChangeHistory', method: 'post', params: this.listQuery }).then(response => {
                    const result = response.data;
                    if (result.code) {
                        this.$message.error(result.msg || '系统错误')
                        this.listLoading = false
                        return
                    }

                    this.list = result.data.items;
                    this.total = result.data.total;
                    this.final_game_rows = result.data.final_game_rows;
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
