<template>
    <div class="app-container calendar-list-container">
        <div class="filter-container">
            <el-button class="filter-item" style="margin-left: 10px;" @click="handleCreate" type="primary"
                       icon="el-icon-edit">添加
            </el-button>
        </div>

        <el-table :key='tableKey' :data="list" v-loading="listLoading" element-loading-text="给我一点时间" border fit
                  highlight-current-row style="width: 100%">
            <el-table-column label="id" width="65" prop="id"></el-table-column>
            <el-table-column width="150px" label="名称" prop="name"></el-table-column>
            <el-table-column width="320px" label="描述" prop="description"></el-table-column>
            <el-table-column width="50px" label="金币" prop="coins"></el-table-column>
            <el-table-column width="50px" label="赠送" prop="extra_coins"></el-table-column>
            <el-table-column width="150px" label="价格" prop="price"></el-table-column>
            <el-table-column width="150px" label="修改时间" prop="update_time">
                <template slot-scope="scope">{{scope.row.update_time | formatTime('{y}-{m}-{d} {h}:{i}')}}</template>
            </el-table-column>
            <el-table-column label="操作" width="230" class-name="small-padding fixed-width" align="center">
                <template slot-scope="scope">
                    <el-button type="primary" size="mini" @click="handleUpdate(scope.$index, scope.row)">编辑</el-button>
                    <el-button size="mini" type="danger" @click="handleDelete(scope.$index,scope.row)">删除
                    </el-button>
                </template>
            </el-table-column>
        </el-table>

        <div class="pagination-container">
            <el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange"
                           :current-page="currentPage" :page-sizes="[10,20,30, 50]" :page-size="listQuery.limit"
                           layout="total, sizes, prev, pager, next, jumper" :total="total">
            </el-pagination>
        </div>

        <el-dialog :title="dialogTitle" :visible.sync="dialogFormVisible" width="30%">
            <el-form :rules="rules" ref="dataForm" :model="temp" label-position="left" label-width="80px"
                     style='width: 400px; margin-left:50px;'>
                <el-form-item label="名称">
                    <el-input v-model="temp.name"></el-input>
                </el-form-item>
                <el-form-item label="描述">
                    <el-input type="textarea" v-model="temp.description"></el-input>
                </el-form-item>
                <el-form-item label="金币" prop="coins">
                    <el-input v-model.number="temp.coins"></el-input>
                </el-form-item>
                <el-form-item label="赠送金币" prop="extra_coins">
                    <el-input v-model.number="temp.extra_coins"></el-input>
                </el-form-item>
                <el-form-item label="价格(元)" prop="price">
                    <el-input v-model.number="temp.price"></el-input>
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
    import * as constant from '@/utils/constant'

    export default {
        name: 'admin-lists',
        directives: {waves},
        data() {
            return {
                tableKey: 0,
                list: null,
                total: null,
                listLoading: true,
                listQuery: {page: 1, limit: 20},
                roles: [],
                temp: {id: undefined, name: '', description: '', coins: '', extra_coins: '', price: ''},
                dialogFormVisible: false,
                dialogTitle: '',
                rules: {
                    name: [{required: true, trigger: 'blur', message: '请输入套餐名称'}],
                    coins: [{required: true, trigger: 'blur', message: '请输入金币数'}, {
                        type: 'integer',
                        message: '金币必须为整数值',
                        trigger: 'blur'
                    }, {type: 'integer', min: 0, message: '金币不能少于0', trigger: 'blur'}],
                    extra_coins: [{required: true, trigger: 'blur', message: '请输入赠送金币数'}, {
                        type: 'integer',
                        message: '金币必须为整数值',
                        trigger: 'blur'
                    }, {type: 'integer', min: 0, message: '金币不能少于0', trigger: 'blur'}],
                    price: [{required: true, trigger: 'blur', message: '请输入价格'}, {
                        type: 'number',
                        message: '价格必须为数值类型'
                    }, {type: 'number', min: 0, message: '价格不能少于0', trigger: 'blur'}]
                },
                constant: constant
            }
        },
        created() {
            this.getList()
            console.log(this.constant)
        },
        methods: {
            getList() {
                this.listLoading = true
                request({url: 'recharge/lists', method: 'get', params: this.listQuery}).then(response => {
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
                if (this.listQuery.page === val) {
                    return
                }
                this.listQuery.page = val
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
                this.updatingRow = row
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
            handleDelete(idx, row) {
                this.$confirm('此操作将永久删除该管理员, 是否继续?', '确认', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    request({url: 'recharge/del', method: 'post', data: {id: row.id}}).then(response => {
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
                }).catch(() => {
                })
            }
        }
    }
</script>
