<template>
    <div class="app-container calendar-list-container">
        <div class="filter-container">
            <el-date-picker
                    v-model="listQuery.stc_create_datetime"
                    align="right"
                    type="date"
                    value-format="yyyy-MM-dd"
                    placeholder="选择开始日期"
                    :default-value="listQuery.stc_create_datetime">
            </el-date-picker>
            min_show：<el-input @keyup.enter.native="getList()" style="width: 200px;"   placeholder='min_show' v-model="listQuery.min_show"></el-input>
            <el-button class="filter-item" type="primary" icon="el-icon-search" @click="handleFilter"></el-button>
        </div>

        <el-table :data="detailList" v-loading="listLoading" element-loading-text="给我一点时间" border fit highlight-current-row style="width: 521px;margin-top:15px;">
            <el-table-column width="100px" label="record_date" prop="record_date"></el-table-column>
            <el-table-column width="150px" label="game_id" prop="game_id">
                <template slot-scope="scope">
                    <el-tag>{{listQuery.gameIdMap[scope.row.game_id]}}</el-tag>{{scope.row.game_id}}
                </template>
            </el-table-column>
            <el-table-column width="200px" label="run_log" prop="run_log"></el-table-column>
            <el-table-column width="70px" label="count" prop="count"></el-table-column>
        </el-table>

<br/>
        ---------------------------------------------------------------------分割线-------------------------------------------------------------------------------

        <el-table :data="list" v-loading="listLoading" element-loading-text="给我一点时间" border fit highlight-current-row style="width: 521px;margin-top:15px;">
            <el-table-column width="100px" label="record_date" prop="record_date"></el-table-column>
            <el-table-column width="150px" label="game_id" prop="game_id">
                <template slot-scope="scope">
                    <el-tag>{{listQuery.gameIdMap[scope.row.game_id]}}</el-tag>{{scope.row.game_id}}
                </template>
            </el-table-column>
            <el-table-column width="200px" label="status" prop="status"></el-table-column>
            <el-table-column width="70px" label="count" prop="count"></el-table-column>
        </el-table>

    </div>
</template>

<script>

    import request from '@/utils/request'
    import * as filterOption from '@/utils/filter_option'
    import * as constant from '@/utils/constant'

    Date.prototype.format = function (fmt) {
        var o = {
            "M+": this.getMonth() + 1, //月份
            "d+": this.getDate(), //日
            "h+": this.getHours(), //小时
            "m+": this.getMinutes(), //分
            "s+": this.getSeconds(), //秒
            "q+": Math.floor((this.getMonth() + 3) / 3), //季度
            "S": this.getMilliseconds() //毫秒
        };
        if (/(y+)/.test(fmt)) fmt = fmt.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
        for (var k in o)
            if (new RegExp("(" + k + ")").test(fmt)) fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
        return fmt;
    }

    export default {
        name: 'admin-lists',
        data () {
            var stcCreate_datetime = new Date();
            return {
                list: null,
                detailList: null,
                total: 0,
                listLoading: true,
                textarea: '',
                listQuery: {
                    page: 1,
                    limit: 500,
                    serverName:'',
                    getNumber:'',
                    email:'',
                    status:'',
                    oubo:'',
                    signDay:'',
                    stc_create_datetime:stcCreate_datetime.format("yyyy-MM-dd"),
                    min_show:5,
                    game_id:0,
                    gameIdMap: constant.gameIdMap,
                },
                temp: { id: undefined, name: '', description: '', coins: '', extra_coins: '', price: '' },
                dialogFormVisible: false,
                dialogTitle: '',
                filterOption: filterOption
            }
        },
        created () {
            this.getList(true);
        },
        methods: {
            getList (createdFlag) {
                this.listLoading = true;
                request({ url: 'mao/scriptRecord', method: 'post', params: this.listQuery }).then(response => {
                    const result = response.data;
                    if (result.code) {
                        this.$message.error(this.result.msg || '系统错误');
                        this.listLoading = false;
                        return
                    }
                    if(createdFlag === true) {
                        this.listQuery.min_count = result.data.min_count;
                    }
                    this.list = result.data.items;
                    this.detailList = result.data.detailItems;
                    this.listLoading = false;
                });
            },
            handleFilter() {
                this.listQuery.page = 1;
                this.getList()
            }
        }
    }
</script>
