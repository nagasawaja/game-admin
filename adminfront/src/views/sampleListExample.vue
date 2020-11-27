<template>
    <div class="app-container calendar-list-container">
        <!--  filterBar  -->
        <div class="filter-container">
            username：
            <el-input @keyup.enter.native="getList(true)" style="width: 200px;"   placeholder='username'
                      v-model="listQuery.username"></el-input>
            排序：
            <el-select style="width: 180px;" v-model="listQuery.orderBy" clearable placeholder="请选择">
                <el-option
                        v-for="item in this.orderByOption"
                        :key="item.value"
                        :label="item.label"
                        :value="item.value">
                </el-option>
            </el-select>
            <el-button class="filter-item" type="primary" icon="el-icon-search" @click="getList(true)">搜索</el-button>
        </div>
        <el-button type="primary" size="mini" @click="addRow()">新增</el-button>

        <!--  dataTable  -->
        <el-table :key='tableKey' :data="list" v-loading="listLoading" element-loading-text="给我一点时间" border fit
                  highlight-current-row
                  style="width: 100%">
            <!--   fieldData   -->
            <el-table-column width="65px" label="id" prop="id"></el-table-column>
            <el-table-column width="100px" label="名称" prop="name"></el-table-column>
            <el-table-column width="100px" label="是否删除">
                <template slot-scope="{row}">
                    <el-tag :type="constant.yesOrNoCssType[row.is_delete]">
                        {{constant.yesOrNoShowText[row.is_delete]}}
                    </el-tag>
                </template>
            </el-table-column>
            <el-table-column width="150px" label="操作">
                <template slot-scope="scope">
                    <el-button type="primary" size="mini" @click="editRow(scope.$index, scope.row)">编辑</el-button>
                    <el-button type="danger" size="mini" @click="delRow(scope.$index, scope.row)">删除</el-button>
                </template>
            </el-table-column>
        </el-table>

        <!--    新增、编辑共同拥有的临时窗口    -->
        <el-dialog :title="dialogTitle" :visible.sync="dialogFormVisible" width="50%">
            <el-form ref="dataForm" :model="tempData" label-position="left" label-width="80px"
                     style='width: 400px; margin-left:50px;'>
                <el-form-item label="id">
                    <el-input v-model="tempData.id" :readonly=true></el-input>
                </el-form-item>
                <el-form-item label="name"><el-input v-model="tempData.name"></el-input></el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="dialogFormVisible = false">取消</el-button>
                <el-button type="primary" @click="saveData">确认</el-button>
            </div>
        </el-dialog>

        <!--  pageBar -->
        <div class="pagination-container">
            <el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange"
                           :page-sizes="[10,20,30, 50]" :page-size="listQuery.limit"
                           layout="total, sizes, prev, pager, next, jumper" :total="total">
            </el-pagination>
        </div>

    </div>
</template>

<script>
    import request from '@/utils/request'

    export default {
        name: 'complexTable',
        data() {
            return {
                constant:require("@/utils/constant"),
                tableKey: 0,
                list: null,
                total: null,
                listLoading: true,
                dialogTitle: '',
                dialogFormVisible: false,
                tempData: {id: 0, name: ""},
                orderByOption: [{value: "create_time desc", label: "开始时间（倒叙）"}, {
                    value: "memberCount desc",
                    label: "会员数（倒叙）"
                }],
                listQuery: {
                    page: 1,
                    limit: 20,
                    username: '',
                }
            }
        },
        filters: {},
        created() {
            this.getList(false)
        },
        methods: {
            // 获取数据
            getList(filter) {
                this.listLoading = true;
                if (filter === true) {
                    this.listQuery.page = 1;
                }
                request({url: 'url', method: 'post', params: this.listQuery}).then(response => {
                    const result = response.data;
                    if (result.code) {
                        this.$message.error(result.msg || '系统错误');
                        this.listLoading = false;
                        return
                    }

                    this.list = result.data.rows;
                    this.total = result.data.total;
                    this.listLoading = false;
                })
            },
            // 提交数据到后端
            saveData() {
                let postParams = Object.assign({}, this.tempData);
                request({url: 'url', method: 'post', params: postParams}).then(response => {
                    const respJson = response.data;
                    if (respJson.code) {
                        this.$message.error(respJson.msg || '系统错误');
                        return
                    }
                    this.dialogFormVisible = false;
                    this.$notify({
                        title: '成功',
                        message: '提交成功',
                        type: 'success',
                        duration: 3000
                    });
                    // 先用全部更新方式
                    this.getList(false);

                    // if(this.tempData.id == '' || this.tempData.id == 0) {
                    //     // update
                    //     for (const i in postParams) {
                    //         if (this.updatingRow[i]) {
                    //             this.updatingRow[i] = params[i]
                    //         }
                    //     }
                    // } else {
                    //     // create
                    //     postParams.id = ret.data.id;
                    //     this.list.unshift(params);
                    // }
                }).catch(error => {
                    console.log(error.message)
                })
            },
            // 新增一条记录
            addRow() {
                this.resetTempData();
                this.dialogTitle = '新增';
                this.dialogFormVisible = true;
            },
            // 编辑一条记录
            editRow(idx, tmpRow) {
                this.dialogTitle = '编辑';
                this.tempData = Object.assign({}, tmpRow);
                this.dialogFormVisible = true;
            },
            // 删除一条记录
            delRow(idx, tmpRow) {
                this.$confirm('是否继续删除?', '确认', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    // 确认删除
                    let postParams = Object.assign({}, tmpRow);
                    request({url: 'url', method: 'post', params: postParams}).then(response => {
                        const respJson = response.data;
                        if (respJson.code) {
                            this.$message.error(respJson.msg || '系统错误');
                            return
                        }

                        this.$notify({
                            title: '成功',
                            message: '删除成功',
                            type: 'success',
                            duration: 3000
                        });
                        this.list.splice(idx, 1)
                    });
                }).catch(() => {
                    console.log("cancelDel")
                })
            },
            // 重置临时数据区
            resetTempData() {
                let tData = {};
                for (const i in this.tempData) {
                    tData[i] = ''
                }
                this.tempData = tData
            },
            // 改变页面显示个数时触发的函数
            handleSizeChange(val) {
                if (this.listQuery.limit === val) {
                    return
                }
                this.listQuery.limit = val;
                this.getList(false);
            },
            // 改变当前页面
            handleCurrentChange(val) {
                console.log(val);
                console.log(this.listQuery.page);
                if (this.listQuery.page === val) {
                    return
                }
                this.listQuery.page = val;
                this.getList(false)
            },
        }
    }
</script>
