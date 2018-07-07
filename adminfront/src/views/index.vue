<template>
    <div class="app-container calendar-list-container">
        <!--header筛选 搜索 ...-->
        <div class="filter-container">
            <el-button class="filter-item" style="margin-left: 10px;" @click="handleCreate" type="primary"
                       icon="el-icon-edit">添加礼包
            </el-button>
            <br/>
        </div>

        <!--列表界面-->
        <el-table :key='tableKey' :data="list" v-loading="listLoading" element-loading-text="给我一点时间" border fit highlight-current-row style="width: 100%">
            <el-table-column width="65px" label="礼包id" prop="gift_id"></el-table-column>
            <el-table-column width="100px" label="礼包标题" prop="gift_title"></el-table-column>
            <el-table-column width="150px" label="时间规格">
                <template slot-scope="scope">
                    <div  v-if="scope.row.gift_standard != ''" v-for="item in scope.row.gift_standard.split(',')">{{item}}</div>
                </template>
            </el-table-column>
            <el-table-column width="150px" label="礼包功能">
                <template slot-scope="scope">
                    <div  v-if="scope.row.service_name_rows != ''" v-for="item in scope.row.service_name_rows.split(',')">{{item}}</div>
                </template>
            </el-table-column>
            <el-table-column width="100px" label="礼包状态" prop="gift_status">
                <template slot-scope="scope">
                    <el-tag>{{scope.row.gift_status==1?'开启':'关闭'}}</el-tag>
                </template>
            </el-table-column>
            <el-table-column width="100px" label="内部使用" prop="is_company">
                <template slot-scope="scope">
                    <el-tag>{{scope.row.is_company==1?'是':'否'}}</el-tag>
                </template>
            </el-table-column>
            <el-table-column label="操作" width="230" class-name="small-padding fixed-width" align="center">
                <template slot-scope="scope">
                    <el-button size="mini" type="danger" @click="handleDelete(scope.$index,scope.row)">删除</el-button>
                </template>
            </el-table-column>
        </el-table>

        <!--分页-->
        <div class="pagination-container">
            <el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange"
                           :current-page.sync="listQuery.page" :page-sizes="[10,20,30, 50]" :page-size="listQuery.limit"
                           layout="total, sizes, prev, pager, next, jumper" :total="total">
            </el-pagination>
        </div>

        <!--编辑界面-->
        <el-dialog :rules="rules" :title="dialogTitle" :visible.sync="dialogFormVisible" width="40%">
            <el-form ref="dataForm" :model="temp" label-position="left" label-width="120px" style='width: 500px; margin-left:50px;'>
                <el-form-item label="套餐标题" prop="gift_title">
                    <el-input type="input" v-model="temp.gift_title"></el-input>
                </el-form-item>
                <el-form-item label="套餐状态">
                    <el-switch v-model="temp.gift_status"  active-text="启用" inactive-text="停用" :active-value="1" :inactive-value="2"></el-switch>
                </el-form-item>
                <el-form-item label="内部使用">
                    <el-switch v-model="temp.is_company"  active-text="是" inactive-text="否" :active-value="1" :inactive-value="2"></el-switch>
                </el-form-item>
                <el-form-item label="功能">
                    <el-checkbox-group v-model="temp.service_id_rows">
                        <el-checkbox v-for="(item,key) in service_list" :label="item.id">{{item.service_name}}</el-checkbox>
                    </el-checkbox-group>
                </el-form-item>
                <el-form-item label="规格">
                    <el-button class="filter-item" style="margin-left: 10px;" @click="handleAddGiftStandardRows" type="primary"
                               icon="el-icon-edit">添加
                    </el-button>
                    <div v-for="(item,key) in temp.gift_standard_rows">
                        <el-tag>规格{{key + 1}}</el-tag>
                        <el-input style="width:100px" placeholder="有效天数" v-model="item.avail_time_day"></el-input>
                        <el-input style="width:100px" size="mini" placeholder="价钱（元）" v-model="item.price_yuan"></el-input>
                    </div>
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
    import * as constant from '@/utils/constant'
    import * as filterOption from '@/utils/filter_option'

    export default {
        name: 'package_list',
        data() {
            return {
                tableKey: 0,
                list: null,
                total: null,
                listLoading: true,
                listQuery: {
                    page: 1,
                    limit: 20
                },
                service_list: '',
                temp: {
                    gift_title: '',
                    gift_status: 1,
                    service_id_rows: [],
                    is_company: 2,
                    price_yuan: 1,
                    gift_standard_rows: [{"price_yuan":"", "avail_time_day": ""}]
                },
                dialogFormVisible: false,
                dialogTitle: '',
                filterOption: filterOption,
                rules: {
                    gift_title : [
                        {required: true, 'message': "请输入标题", trigger: "blur"},
                        { min: 3, max: 5, message: '长度在 3 到 5 个字符', trigger: 'blur' }
                    ]
                }
            }
        },
        created() {
            this.getList()
        },
        computed: {},
        methods: {
            getList() {
                this.listLoading = true
                request({
                    url: 'service/package-list',
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
            resetTemp() {
                let temp = {
                    gift_title: "",
                    gift_status: 1,
                    service_id_rows: [],
                    is_company: 2,
                    price_yuan: 1,
                    gift_standard_rows: [{"price_yuan":"", "avail_time_day": ""}]
                }
                this.temp = temp
            },
            handleCreate() {
                this.resetTemp()
                this.dialogTitle = '添加功能套餐'
                this.dialogFormVisible = true
                this.$nextTick(() => {
                    this.$refs['dataForm'].clearValidate()
                })
            },
            saveData() {
                this.$refs['dataForm'].validate((valid) => {
                    if (valid) {
                        let url = 'service/package-add'
                        let addMode = true
                        let params = Object.assign({}, this.temp)
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
                            //偷懒使用全加载
                            this.getList()
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
                this.$confirm('此操作会永久删除记录?', '确认', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    request({
                        url: 'service/package-del',
                        method: 'post',
                        data: {
                            gift_id: row.gift_id
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
            },
            handleAddGiftStandardRows() {
                this.temp.gift_standard_rows.push({"price_yuan":"", "avail_time_day":""})
            },
        },
        filter:{}
    }
</script>
