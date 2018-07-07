<template>
    <div class="app-container calendar-list-container">
        <div class="filter-container">
            <el-input @keyup.enter.native="handleFilter" style="width: 200px;" class="filter-item" :placeholder='"用户名"'  v-model="listQuery.userName"></el-input>
            <el-input @keyup.enter.native="handleFilter" style="width: 200px;" class="filter-item" :placeholder='"增加天数"'  v-model="listQuery.day"></el-input>
            <el-button class="filter-item" type="primary" v-waves icon="el-icon-search" @click="handleFilter"></el-button>
        </div>
    </div>
</template>

<script>
    import request from '@/utils/request'
    import waves from '@/directive/waves' // 水波纹指令

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
                    day: '',
                    userName: '',
                },
                wx_state_list: [
                    {'state_id' : 6 , 'state_name' : '在线'},
                    {'state_id': -1, 'state_name' : '离线'}
                ],
                roles: [],
                temp: { id: undefined, name: '', description: '', coins: '', extra_coins: '', price: '' },
                dialogFormVisible: false,
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

        },
        methods: {
            getList () {
                this.listLoading = true
                request({ url: 'user/service_recharge', method: 'get', params: this.listQuery }).then(response => {
                    const result = response.data
                    if (result.code) {
                        this.$message.error(result.msg || '系统错误')
                        this.listLoading = false
                        return
                    }
                    this.$message.success("续费成功")


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
