<template>
    <div class="app-container calendar-list-container">

        <div class="filter-container">
            <el-date-picker
                    v-model="listQuery.stc_create_datetime_start"
                    align="right"
                    type="datetime"
                    value-format="yyyy-MM-dd HH:00:00"
                    placeholder="选择开始日期"
                    :default-value="listQuery.stc_create_datetime_start">
            </el-date-picker>
            <el-date-picker
                    v-model="listQuery.stc_create_datetime_end"
                    align="right"
                    type="datetime"
                    value-format="yyyy-MM-dd HH:00:00"
                    placeholder="选择结束日期"
                    :default-value="listQuery.stc_create_datetime_end">
            </el-date-picker>
            gameId：<el-input @keyup.enter.native="handleFilter" style="width: 200px;"  placeholder='gameId'  v-model="listQuery.game_id"></el-input>

            <el-button class="filter-item" type="primary" icon="el-icon-search" @click="handleFilter"></el-button>

                <el-tag size="large">{{gameTitle}}</el-tag>
            <div style="margin: 10px 0;">
                <el-tag size="large">阴阳师：4308</el-tag>
                <el-tag size="large">永远的七日之都：6378</el-tag>
                <el-tag size="large">第五人格：6587</el-tag>
                <el-tag size="large">FIFA：7744</el-tag>
            </div>

        </div>
        <div style="min-width:500px;width:100%;max-height:500px;position: relative;">
            <canvas ref="line_chart" id="myChart" height="500" style="width:100%;height:100%;"></canvas>
        </div>
    </div>
</template>

<script>
    import { Line } from 'vue-chartjs'
    import chart from 'chart.js'
    import request from '@/utils/request';
    import * as filterOption from '@/utils/filter_option'

    export default {
        name: 'activity-share-total-lists',
        extends: Line,
        data () {
            var stcCreate_datetime_start = new Date();
            stcCreate_datetime_start.setDate(stcCreate_datetime_start.getDate() -30)
            return {
                list: [],
                listQuery: {
                    page: 1,
                    limit: 10,
                    statisticsTime: '',
                    stc_create_datetime_start:stcCreate_datetime_start.format("yyyy-MM-dd 00:00:00"),
                    stc_create_datetime_end:new Date().format("yyyy-MM-dd hh:ii:ss"),
                    game_id:6378,
                },
                listLoading: false,
                gameTitle:"aNull",
                filterOption: filterOption,
                total: {},
                rowTotal: 0,
                chartdata: {
                    datacollection: {
                        labels: ['January', 'February'],
                        datasets: [
                            {
                                label: 'Data One',
                                backgroundColor: '#f87979',
                                data: [40, 20]
                            }
                        ]
                    }
                },
                options: {
                    maintainAspectRatio: false
                }
            }
        },
        async mounted () {
            await this.getList();
            this.initChart();
        },
        methods: {
            initChart() {
                const labelArr = new Array();
                for(var i =0; i<this.list.length; i++) {
                    labelArr.push(this.list[i].create_datetime);
                }
                this.gameTitle = this.list[0].title;
                const array_list = Object.values(this.list)
                const stc = array_list.map(val=>{return val.stc})
                const goods_total_count = array_list.map(val=>{return val.goods_total_count})
                const sale_count = array_list.map(val=>{return val.sale_count})
                let ctx = this.$refs.line_chart.getContext('2d')
                var myChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labelArr,
                        datasets: [{
                            label: 'stc',
                            data: stc,
                            backgroundColor:['rgba(0, 5, 5, 0.1)'],
                            borderWidth: 1
                        },{
                            label: 'goods_total_count',
                            data: goods_total_count,
                            backgroundColor:['rgba(255, 99, 132, 0.2)'],
                            borderWidth: 1
                        },{
                            label: 'sale_count',
                            data: sale_count,
                            backgroundColor:['rgba(255, 206, 86, 0.2)'],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: false,
                        tooltips: {
                            mode: 'point'
                        },
                        scales: {
                            yAxes: [{
                                ticks: {
                                    beginAtZero:true
                                }
                            }]
                        }
                    }
                });
            },
            async handleFilter() {
                await this.getList();
                this.initChart()
            },
            async getList () {
                this.listLoading = true;
                await request({ url: 'mao/data-report', method: 'post', params: this.listQuery, timeout:10000}).then(response => {
                    const result = response.data;
                    if (result.code) {
                        this.$message.error(result.msg || '系统错误');
                        this.listLoading = false;
                        return;
                    }
                    this.list = result.data.rows;
                    this.listLoading = false;

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
        }
    }
</script>
