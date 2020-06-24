<template>
    <div class="app-container calendar-list-container">
        <div class="filter-container">
            <span>
                email：<el-input style="width: 300px;" clearable  placeholder='email能识别前后空格'  v-model="listQuery.email"></el-input>
                <el-button class="filter-item" type="primary" icon="el-icon-download" @click="CreateForbidEmail">封号新增</el-button>
            </span>
            <br/>
            <span>
                orderId：<el-input style="width: 200px;" clearable  placeholder='orderId'  v-model="listQuery.orderId"></el-input>
                <el-button class="filter-item" type="primary" icon="el-icon-download" @click="markAccountSoldOut">导出</el-button>
                <el-button class="filter-item" type="primary" icon="el-icon-search" @click="getList">刷新</el-button>
            </span>


        </div>
        <el-table :key='tableKey' height="550px" :data="list" v-loading="listLoading" element-loading-text="给我一点时间"
                  border fit highlight-current-row style="display:inline-block;width:auto;margin-top:15px">
            <el-table-column width="200px" label="orderId" prop="orderId"></el-table-column>
            <el-table-column width="150px" label="create_time" prop="create_time">
                <template slot-scope="scope">{{scope.row.create_time | formatTime('{y}-{m}-{d} {h}:{i}:{s}')}}</template>
            </el-table-column>
            <el-table-column width="300px" label="description" prop="description"></el-table-column>
            <el-table-column width="100px" label="forbidCount" prop="forbidCount"></el-table-column>
        </el-table>
        <div class="pagination-container">
            <el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange"
                           :page-sizes="[10,20,30, 50]" :page-size="listQuery.limit" layout="total, sizes, prev, pager, next, jumper" :total="total">
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

    export default {
        name: 'admin-lists',
        data () {
            return {
                tableKey: 0,
                list: null,
                total: 0,
                listLoading: true,
                textarea: '',
                listQuery: {orderId:'', email:''},
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
                request({ url: 'taobao/forbidStatistical', method: 'post', params: this.listQuery }).then(response => {
                    const result = response.data;
                    if (result.code) {
                        this.$message.error(result.msg || '系统错误');
                        this.listLoading = false;
                        return
                    }
                    this.list = result.data.rows;
                    this.listLoading = false
                })
            },
            markAccountSoldOut () {
                this.listLoading = true;
                request({ url: 'taobao/mark-account-sold-out', method: 'post', params: this.listQuery }).then(response => {
                    const result = response.data;
                    if (result.code) {
                        this.$message.error(result.msg || '系统错误')
                        this.listLoading = false
                        return
                    }

                    request({ url: 'taobao/sold-out-account-detail', method: 'post', params: {id: result.data.id} }).then(response => {
                        const result = response.data;
                        if(result.code) {
                            this.$message.error(result.msg || '系统错误');
                            this.listLoading = false
                            return
                        }
                        this.dialogFormVisible = true;
                        this.textarea = result.data.rows.content;
                    })
                    this.listLoading = false;
                    this.dialogTitle = 'orderId:' + this.listQuery.orderId;
                })
            },
            CreateForbidEmail() {
                this.listLoading = true;
                request({ url: 'taobao/createForbidEmail', method: 'post', params: this.listQuery }).then(response => {
                    const result = response.data;
                    if (result.code) {
                        this.$message.error(result.msg || '系统错误');
                        this.listLoading = false;
                        return
                    }
                    this.$message.info("添加成功");
                    this.listLoading = false;
                })
            },
        },
    }
</script>
