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
            <el-table-column width="100px" label="页面排序（越小越靠前）" prop="sort_order">
                <template slot-scope="scope">
                    <template v-if="scope.row.edit">
                        <el-input class="edit-input" size="small" v-model="scope.row.sort_order"></el-input>
                        <el-button class='cancel-btn' size="small" icon="el-icon-refresh" type="warning" @click="saveOneFieldCancel({sort_order:scope.row.originSortOrder}, scope.row)">cancel</el-button>
                    </template>
                    <span v-else>{{ scope.row.sort_order }}</span>
                </template>
            </el-table-column>
            <el-table-column width="100px" label="礼包状态" prop="gift_status">
                <template slot-scope="scope">
                    <el-tag>{{scope.row.gift_status==1?'开启':'关闭'}}</el-tag>
                </template>
            </el-table-column>
            <el-table-column width="100px" label="内部使用" prop="is_company">
                <template slot-scope="scope">
                    <el-button v-if="scope.row.is_company=='1'"  size="mini" type="success" @click="saveData2(scope.row, 2)">是
                    </el-button>
                    <el-button v-if="scope.row.is_company=='2'"  size="mini" type="danger" @click="saveData2(scope.row, 1)">否
                    </el-button>
                </template>
            </el-table-column>
            <el-table-column label="操作" width="230" class-name="small-padding fixed-width" align="center">
                <template slot-scope="scope">
                    <el-button v-if="scope.row.edit" type="success" @click="saveOneField({gift_id:scope.row.gift_id, param:{sort_order:scope.row.sort_order}})" size="small" icon="el-icon-circle-check-outline">Ok</el-button>
                    <el-button v-else type="primary" @click='scope.row.edit=!scope.row.edit' size="small" icon="el-icon-edit">排序编辑</el-button>
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
        <el-dialog :title="dialogTitle" :visible.sync="dialogFormVisible" width="40%">
            <el-form :rules="rules" ref="dataForm" :model="temp" label-position="left" label-width="120px" style='width: 500px; margin-left:50px;'>
                <el-form-item label="套餐标题" prop="gift_title">
                    <el-input type="input" v-model="temp.gift_title"></el-input>
                </el-form-item>
                <el-form-item label="套餐排序（越小越靠前）" prop="sort_order">
                    <el-input type="input" v-model="temp.sort_order"></el-input>
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
                    gift_standard_rows: [{"price_yuan":"", "avail_time_day": ""}],
                    sort_order: 99
                },
                dialogFormVisible: false,
                dialogTitle: '',
                filterOption: filterOption,
                rules: {
                    gift_title : [
                        { required: true, message: '请输入活动名称', trigger: 'blur' },
                        { min: 3, max: 10, message: '长度在 3 到 10 个字符', trigger: 'blur' }
                    ]
                }
            };
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
                    //this.list = result.data.items
                    this.list = result.data.items.map(v => {
                        this.$set(v, 'edit', false)
                        v.originSortOrder = v.sort_order
                        return v
                    });
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
                    gift_standard_rows: [{"price_yuan":"", "avail_time_day": ""}],
                    sort_order: 99,
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
            saveData2(giftRaw, value) {
                let url = 'service/save-package-list-data'
                request({
                    url: url,
                    method: 'post',
                    data: {gift_id: giftRaw.gift_id, is_company:value}
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
            },
            saveOneField(param) {
                let url = 'service/package-edit'
                let params = param
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
                    this.$notify({
                        title: '成功',
                        message: '提交成功',
                        type: 'success',
                        duration: 2000
                    })
                }).catch(error => {
                    this.$message.error(error.message)
                })
            },
            saveOneFieldCancel(param, row) {
                console.log(row)
                console.log(param)
                for(var i in param) {
                    row[i] = param[i]
                    console.log(row)
                }
                row.edit = false
                this.$message({
                    message: 'The title has been restored to the original value',
                    type: 'warning'
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
