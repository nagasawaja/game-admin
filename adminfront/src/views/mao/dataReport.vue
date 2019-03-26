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
            gameId：<el-select v-model="listQuery.game_id" filterable >
            <el-option-group
                    v-for="group in final_game_rows"
                    :key="group.game_id"
                    :label="group.title"
                    :value="group.game_id">
                    <el-option
                            v-for="item in group.option"
                            :key="item.game_id"
                            :label="item.title"
                            :value="item.game_id">
                    </el-option>
                </el-option-group>
            </el-select>
            <!--gameId：<el-input @keyup.enter.native="handleFilter" style="width: 200px;"  placeholder='gameId'  v-model="listQuery.game_id"></el-input>-->

            <el-button class="filter-item" type="primary" icon="el-icon-search" @click="handleFilter"></el-button>
            <el-tag size="large">{{gameTitle}}</el-tag>

        </div>
        <div style="min-width:500px;width:100%;max-height:500px;position: relative;" id="myChartDiv" ref="myChartDiv_ref">
            <canvas ref="line_chart" id="myChart" height="500px" style="width:100%;height:100%;"></canvas>
        </div>
    </div>
</template>

<script>
    import Chart from 'chart.js'
    import request from '@/utils/request';
    import * as filterOption from '@/utils/filter_option'

    export default {
        name: 'activity-share-total-lists',
        data () {
            var stcCreate_datetime_start = new Date();
            stcCreate_datetime_start.setDate(stcCreate_datetime_start.getDate() -30)
            return {
                list: [],
                final_game_rows:[],
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
                myChart: "",
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
        async created () {
            await this.getList();
            this.initChart();
        },
        methods: {
            initChart() {
                let labelArr = [];
                for(let i =0; i<this.list.length; i++) {
                    labelArr.push(this.list[i].create_datetime);
                }
                console.log(this.list[0]);
                this.gameTitle = this.list[0].title;
                const array_list = Object.values(this.list);
                const stc = array_list.map(val=>{return val.stc});
                const goods_total_count = array_list.map(val=>{return val.goods_total_count});
                const sale_count = array_list.map(val=>{return val.sale_count});
                //this.$refs.line_chart.width = '500px';
                let ctx = this.$refs.line_chart;
                this.myChart = new Chart(ctx, {
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
                        animation: {
                            duration: 0,
                            onComplete: function () {
                                var chartInstance = this.chart,
                                ctx = chartInstance.ctx;
                                ctx.font = Chart.helpers.fontString(15, Chart.defaults.global.defaultFontStyle, Chart.defaults.global.defaultFontFamily);
                                ctx.textAlign = 'center';
                                ctx.textBaseline = 'bottom';
                                ctx.fillStyle = this.chart.config.options.defaultFontColor;

                                this.data.datasets.forEach(function (dataset, i) {
                                    var meta = chartInstance.controller.getDatasetMeta(i);
                                    meta.data.forEach(function (bar, index) {
                                        var data = dataset.data[index];
                                        ctx.fillText(data, bar._model.x - 7, bar._model.y - 5);
                                    });
                                });
                            }
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
                const res =  await this.getList();
                await this.removeData(this.myChart);
                let labelArr = [];
                for(let i =0; i<this.list.length; i++) {
                    labelArr.push(this.list[i].create_datetime);
                }
                this.gameTitle = this.list[0].title;
                const array_list = Object.values(this.list);
                const stc = array_list.map(val=>{return val.stc});
                const goods_total_count = array_list.map(val=>{return val.goods_total_count});
                const sale_count = array_list.map(val=>{return val.sale_count});
                let dd = [{
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
                this.addData(this.myChart, labelArr, dd);
            },
            removeData(c) {
                while(c.data.labels.pop() != undefined) {}
                c.data.datasets.forEach((dataset) => {
                    while(dataset.data.pop() != undefined) {
                        console.log('a')
                    };
                });

                c.update();

            },
            addData(c, label, data) {
                for(let i=0; i<label.length; i ++) {
                    c.data.labels.push(label[i]);
                }
                for(let i = 0; i< data.length; i++) {
                    for(let k = 0; k< data[i].data.length; k++) {
                        c.data.datasets[i].data.push(data[i].data[k]);
                    }
                }
                c.update();
            },
            async getList () {
                this.listLoading = true;
                const res = await request({ url: 'mao/dataReport', method: 'post', params: this.listQuery, timeout:10000}).then(response => {
                    const result = response.data;
                    if (result.code) {
                        this.$message.error(result.msg || '系统错误');
                        this.listLoading = false;
                        return;
                    }
                    console.log("finish");
                    this.list = result.data.rows;
                    this.final_game_rows = result.data.final_game_rows;
                    this.listLoading = false;
                });
                return "a"
            }
        }
    }
</script>
