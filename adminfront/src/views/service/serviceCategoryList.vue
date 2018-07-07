<template>
    <div class="app-container calendar-list-container">
        <!--列表界面-->
        <el-table :key='tableKey' :data="list" v-loading="listLoading" element-loading-text="给我一点时间" border fit
                  highlight-current-row style="width: 100%">
            <el-table-column width="100px" label="分类id" prop="id"></el-table-column>
            <el-table-column width="300px" label="分类名称" prop="title"></el-table-column>
            <el-table-column width="130px" label="排序(越小越靠前)" prop="sort_order"></el-table-column>
            <el-table-column width="100px" label="状态" prop="status">
                <template slot-scope="scope">
                    <el-tag :type="filterOption.STATUS_CSS_TYPE[scope.row.status]">{{filterOption.STATUS[scope.row.status]}}</el-tag>
                </template>
            </el-table-column>
            <el-table-column label="操作" width="230" class-name="small-padding fixed-width" align="center">
                <template slot-scope="scope">
                    <el-button type="primary" size="mini" @click="saveDataMenu(scope.$index, scope.row)">编辑</el-button>
                </template>
            </el-table-column>
        </el-table>

        <!--编辑界面-->
        <el-dialog :title="dialogTitle" :visible.sync="dialogFormVisible" width="40%">
            <el-form ref="dataForm" :model="temp" label-position="left" label-width="120px" style='width: 500px; margin-left:50px;'>
                <el-form-item label="分类id">
                    <el-tag>{{temp.serviceCategoryId}}</el-tag>
                </el-form-item>
                <el-form-item label="分类标题">
                    <el-input v-model="temp.serviceCategoryTitle"></el-input>
                </el-form-item>
                <el-form-item label="排序">
                    <el-input v-model="temp.serviceCategorySort"></el-input>
                </el-form-item>
                <el-form-item label="状态">
                    <el-switch v-model="temp.serviceCategoryStatus"  active-text="启用" inactive-text="停用" :active-value="1" :inactive-value="2"></el-switch>
                </el-form-item>
                <el-form-item label="功能">
                    <el-checkbox-group v-model="serviceCategoryDetail">
                        <el-checkbox v-for="item in serviceRows" :label="item.id">{{item.service_name}}</el-checkbox>
                    </el-checkbox-group>
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
        data() {
            return {
                tableKey: 0,
                list: null,
                listLoading: true,
                dialogFormVisible: false,
                dialogTitle: '',
                filterOption: filterOption,
                serviceCategoryDetail: null,
                serviceRows: null,
                listQuery: {
                    page: 1,
                    limit: 20,
                },
                temp: {
                    serviceCategoryId: null,
                    serviceCategoryTitle: null,
                    serviceCategoryStatus: null,
                    serviceCategorySort: null
                }
            }
        },
        created() {
            this.getList()
        },
        methods: {
            getList() {
                this.listLoading = true
                request({
                    url: 'service/service-category-list',
                    method: 'get',
                    params: this.listQuery
                }).then(response => {
                    const result = response.data
                    if (result.code) {
                        this.$message.error(result.msg || '系统错误')
                        this.listLoading = false
                        return
                    }
                    this.list = result.data.items;
                    this.serviceRows = result.data.serviceRows;
                    this.listLoading = false;
                })
            },
            saveDataMenu(idx, row) {
                this.dialogTitle = '编辑功能分类';
                this.temp.serviceCategoryId = row.id;
                this.temp.serviceCategoryTitle = row.title;
                this.temp.serviceCategoryStatus = row.status;
                this.temp.serviceCategorySort = row.sort_order;

                this.dialogFormVisible = true;
                request({
                    url: 'service/service-category-detail',
                    method: 'get',
                    params: {serviceCategoryId: row.id}
                }).then(response => {
                    const result = response.data;
                    if (result.code) {
                        this.$message.error(result.msg || '系统错误');
                        this.listLoading = false;
                        return;
                    }
                    this.serviceCategoryDetail = result.data.items;
                    this.listLoading = false;
                })
            },
            saveData() {
                request({
                    url: 'service/service-category-save',
                    method: 'post',
                    data: {
                        serviceCategoryId:this.temp.serviceCategoryId,
                        serviceCategoryTitle:this.temp.serviceCategoryTitle,
                        serviceRows:this.serviceCategoryDetail,
                        serviceCategorySort:this.temp.serviceCategorySort,
                        serviceCategoryStatus:this.temp.serviceCategoryStatus,
                    }
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
                })
            }
        }
    }
</script>
