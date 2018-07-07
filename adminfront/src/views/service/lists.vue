<template>
    <div class="app-container calendar-list-container">
        <!--列表界面-->
        <el-table :key='tableKey' :data="list" v-loading="listLoading" element-loading-text="给我一点时间" border fit highlight-current-row style="width: 100%">
            <el-table-column width="65px" label="id" prop="service_id"></el-table-column>
            <el-table-column width="150px" label="功能名称" prop="service_name">
                <template slot-scope="scope">
                    {{service_list[scope.row.service_id].service_name}}
                </template>
            </el-table-column>
            <el-table-column width="150px" label="图标" prop="icon"></el-table-column>
            <el-table-column width="80px" label="状态" prop="status">
                <template slot-scope="scope">
                    <el-tag :type="filterOption.STATUS_SERVICE_CSS_TYPE[scope.row.status]">{{filterOption.STATUS_SERVICE[scope.row.status]}}</el-tag>
                </template>
            </el-table-column>
            <el-table-column label="操作" width="230" class-name="small-padding fixed-width" align="center">
                <template slot-scope="scope">
                    <el-button type="primary" size="mini" @click="handleUpdate(scope.$index, scope.row)">编辑</el-button>
                </template>
            </el-table-column>
        </el-table>

        <div class="pagination-container">
            <el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange"
                           :current-page.sync="listQuery.page" :page-sizes="[10,20,30, 50]" :page-size="listQuery.limit"
                           layout="total, sizes, prev, pager, next, jumper" :total="total">
            </el-pagination>
        </div>

        <!--编辑界面-->
        <el-dialog :title="dialogTitle" :visible.sync="dialogFormVisible" width="40%">
            <el-form ref="dataForm" :model="temp" label-position="left" label-width="120px" style='width: 500px; margin-left:50px;'>
                <el-form-item label="功能名称" prop="service_name">
                    <el-input type="input" style="width:200px" v-model="temp.service_name" prop="temp.service_name"></el-input>
                </el-form-item>
                <el-form-item label="状态">
                    <el-radio-group v-model="temp.status">
                        <el-radio-button v-for="(value, key) in filterOption.STATUS_SERVICE" :label="key">{{value}}</el-radio-button>
                    </el-radio-group>
                </el-form-item>
                <el-form-item label="图标" prop="icon">
                    <el-input type="input" style="width:200px" v-model="temp.icon" prop="temp.icon"></el-input>
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
    import * as filterOption from '@/utils/filter_option'

    export default {
        name: 'admin-lists',
        directives: {
            waves
        },
        data() {
            return {
                tableKey: 0,
                list: null,
                total: null,
                listLoading: true,
                listQuery: {
                    page: 1,
                    limit: 20,
                    service_id: "",
                    type: ""
                },
                service_list: '',
                service_product_status: [{
                    id: 1,
                    name: '启用'
                }, {
                    id: 2,
                    name: '停用'
                }],
                temp: {
                    service_id: 0,
                    service_name: "",
                    icon: "",
                    status: ""
                },
                dialogFormVisible: false,
                dialogTitle: '',
                previewVisible: false,
                previewIcon: '',
                filterOption: filterOption
            }
        },
        created() {
            this.getList()
        },
        computed: {
            statusMap() {
                let m = {}
                this.service_product_status.map(v => {
                    m[v.id] = v.name
                })
                return m
            }
        },
        methods: {
            getList() {
                this.listLoading = true
                request({
                    url: 'service/lists',
                    method: 'get',
                    params: this.listQuery
                }).then(response => {
                    const result = response.data
                    if (result.code) {
                        this.$message.error(result.msg || '系统错误')
                        this.listLoading = false
                        return
                    }

                    this.list = result.data.items
                    this.service_list = result.data.service_list
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
            handleFilter() {
                this.listQuery.page = 1
                this.getList()
            },
            resetTemp() {
                let temp = {
                    service_product_id: 0,
                        service_id : 0,
                        coins: 1,
                        service_product_status: 1,
                        service_product_type: 1,
                        avail_time_minute: 1,
                        avail_time_hour: 1,
                }
                this.temp = temp
            },
            handlePictureCardPreview(file) {
                this.previewIcon = file.url
                this.previewVisible = true
            },
            handleCreate() {
                this.resetTemp()
                this.dialogTitle = '添加功能服务'
                this.dialogFormVisible = true
                this.$nextTick(() => {
                    this.$refs['dataForm'].clearValidate()
                })
            },
            handleUpdate(idx, row) {
                this.dialogTitle = '编辑功能服务'
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
                        let url = 'service/service-add'
                        let addMode = true
                        let params = Object.assign({}, this.temp)
                        if (params.service_id > 0) {
                            url = 'service/service-edit'
                            addMode = false
                        }
                        request({
                            url: url,
                            method: 'post',
                            data: params
                        }).then(response => {
                            const ret = response.data
                            if (ret.code) {
                                this.$message.error(ret.msg || '系统错误')
                                return
                            }

                            this.dialogFormVisible = false
                            this.$notify({
                                title: '成功',
                                message: '提交成功',
                                type: 'success',
                                duration: 2000
                            })
                            //偷懒全加载
                            this.getList();
                        }).catch(error => {
                            this.$message.error(error.message)
                        })
                    }
                })
            },
            handleDelete(idx, row) {
                this.$confirm('此操作会永久删除记录?', '确认', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    request({
                        url: 'service/del-service',
                        method: 'post',
                        data: {
                            id: row.service_product_id
                        }
                    }).then(response => {
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
        },
        filters: {
            statusFilter(status) {
                if(status == constant.STATUS_VALID) {
                    return 'success';
                }else {
                    return 'info';
                }
            },
            typeFilter(status) {
                if(status == constant.TYPE_SERVICE_PRODUCT_FREE) {
                    return 'success';
                }else {
                    return 'info';
                }
            }
        }
    }
</script>
