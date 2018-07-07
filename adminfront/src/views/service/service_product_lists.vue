<template>
    <div class="app-container calendar-list-container">
        <div class="filter-container">
            <el-select clearable style="width: 150px" class="filter-item" v-model="listQuery.type" :placeholder='"收费类型"'>
                <el-option v-for="(item, index) in filterOption.TYPE_SERVICE_PRODUCT" :label="item" :value="index">
                </el-option>
            </el-select>
            <el-select clearable style="width: 150px" class="filter-item" v-model="listQuery.service_id" :placeholder='"功能服务"'>
                <el-option v-for="item in service_list" :label="item.service_name" :value="item.id">
                </el-option>
            </el-select>
            <el-button class="filter-item" type="primary" v-waves icon="el-icon-search"
                       @click="handleFilter"></el-button>
            <el-button class="filter-item" style="margin-left: 10px;" @click="handleCreate" type="primary"
                       icon="el-icon-edit">添加
            </el-button>
            <br/>
        </div>

        <!--列表界面-->
        <el-table :key='tableKey' :data="list" v-loading="listLoading" element-loading-text="给我一点时间" border fit highlight-current-row style="width: 100%">
            <el-table-column width="65px" label="id" prop="service_product_id"></el-table-column>
            <el-table-column width="150px" label="所属功能名称" prop="service_id">
                <template slot-scope="scope">
                    {{service_list[scope.row.service_id].service_name}}
                </template>
            </el-table-column>
            <el-table-column width="150px" label="有效时长（小时）" prop="avail_time_minute">
                <template slot-scope="scope">{{scope.row.avail_time_minute / 60}}</template>
            </el-table-column>
            <el-table-column width="80px" label="状态" prop="service_product_status">
                <template slot-scope="scope">
                    <el-tag :type="scope.row.service_product_status | statusFilter">{{statusMap[scope.row.service_product_status]}}</el-tag>
                </template>
            </el-table-column>
            <el-table-column width="100px" label="试用状态" prop="service_product_type">
                <template slot-scope="scope">
                    <el-tag :type="scope.row.service_product_type | typeFilter">{{scope.row.service_product_type==1?'否':'是'}}</el-tag>
                </template>
            </el-table-column>
            <el-table-column label="操作" width="230" class-name="small-padding fixed-width" align="center">
                <template slot-scope="scope">
                    <el-button type="primary" size="mini" @click="handleUpdate(scope.$index, scope.row)">编辑</el-button>
                    <el-button size="mini" type="danger" @click="handleDelete(scope.$index,scope.row)">删除</el-button>
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
                <el-form-item label="所属功能名称" prop="service_name">
                    <el-select class="filter-item" v-model="temp.service_id" placeholder="请选择服务状态">
                        <el-option v-for="item in service_list" :label="item.service_name" :value="item.id">
                        </el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label="价格(币)" prop="coins" v-show="">
                    <el-input-number v-model="temp.coins"  :min="0" label="coins"></el-input-number>
                </el-form-item>
                <el-form-item label="有效时长（小时）" prop="avail_time_hour">
                    <el-input type="text" v-model="temp.avail_time_hour"></el-input>
                </el-form-item>
                <el-form-item label="状态">
                    <el-switch v-model="temp.service_product_status"  active-text="启用" inactive-text="停用" :active-value="1" :inactive-value="2"></el-switch>
                </el-form-item>
                <el-form-item label="是否试用" prop="service_product_type">
                    <el-radio-group v-model="temp.service_product_type">
                        <el-radio-button label="1">收费</el-radio-button>
                        <el-radio-button label="2">免费试用</el-radio-button>
                    </el-radio-group>
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
                    service_product_id: 0,
                    service_id : 0,
                    coins: 1,
                    service_product_status: 1,
                    service_product_type: 1,
                    avail_time_minute: 1,
                    avail_time_hour: 1,
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
                    url: 'service/service-product-lists',
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
                this.temp.avail_time_hour = row.avail_time_minute / 60;
                this.updatingRow = row
                this.dialogFormVisible = true
                this.$nextTick(() => {
                    this.$refs['dataForm'].clearValidate()
                })
            },
            saveData() {
                this.$refs['dataForm'].validate((valid) => {
                    if (valid) {
                        let url = 'service/add'
                        let addMode = true
                        this.temp.avail_time_minute = this.temp.avail_time_hour * 60;
                        let params = Object.assign({}, this.temp)
                        if (params.service_product_id > 0) {
                            url = 'service/edit'
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
                        url: 'service/del',
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
