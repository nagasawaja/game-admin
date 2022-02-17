<template>
  <div v-loading="listLoading">
    <div class="filter-container">
      汇总频率：
      <el-input @keyup.enter.native="handleFilter" style="width: 200px;" placeholder='汇总频率'
                v-model="listQuery.frequencySecond"></el-input>
      游戏名：
      <el-select style="width: 180px;" v-model="listQuery.gameId" clearable placeholder="游戏名">
        <el-option
          v-for="(value, key) in constant.gameIdMap"
          :key="key"
          :label="value"
          :value="key">
        </el-option>
      </el-select>
      <el-button class="filter-item" type="primary" icon="el-icon-search" @click="handleFilter">搜索</el-button>
      <br/>
      更新时间：
      <el-date-picker
        v-model="listQuery.start_time"
        align="right"
        type="datetime"
        value-format="yyyy-MM-dd HH:mm:ss"
        placeholder="选择开始日期"
        :default-value="listQuery.start_time">
      </el-date-picker>
      -
      <el-date-picker
        v-model="listQuery.end_time"
        align="right"
        type="datetime"
        value-format="yyyy-MM-dd HH:mm:ss"
        placeholder="选择开始日期"
        :default-value="listQuery.end_time">
      </el-date-picker>

    </div>
    <div id="mainaaa" style="width:100%; height:90vh;">

    </div>
  </div>
</template>

<script>
import request from '@/utils/request'
import * as echarts from 'echarts'

export default {
  name: 'activity-share-total-lists',
  data () {
    // eslint-disable-next-line camelcase
    var stcCreate_datetime_start = new Date()
    stcCreate_datetime_start.setDate(stcCreate_datetime_start.getDate() - 30)
    // eslint-disable-next-line camelcase
    var stcCreate_datetime = new Date()
    return {
      legend: [],
      series: [],
      xAxis: [],
      constant: require('@/utils/constant'),
      listQuery: {
        frequencySecond: 1800,
        end_time: stcCreate_datetime.format('yyyy-MM-dd 23:59:59'),
        start_time: stcCreate_datetime.format('yyyy-MM-dd 00:00:00'),
        gameId: '7539'
      },
      myChart: '',
      listLoading: false
    }
  },
  created () {
    this.getList()
  },
  mounted () {
    var myChart = echarts.init(document.getElementById('mainaaa'))
    window.addEventListener('resize', function () {
      myChart.resize()
    })
    this.myChart = myChart
  },
  methods: {
    handleFilter () {
      this.getList()
    },
    getList () {
      this.listLoading = true
      request({url: 'mao/deviceRunningRecord', method: 'post', params: this.listQuery}).then(response => {
        const result = response.data
        if (result.code) {
          this.$message.error(result.msg || '系统错误')
          this.listLoading = false
          return
        }
        // this.legend = result.data.legend
        this.series = result.data.series
        this.xAxis = result.data.xAxis
        this.listLoading = false
        let seriess = []
        for (var i in this.series) {
          seriess.push({
            name: i,
            type: 'line',
            // stack: 'Total',
            data: this.series[i]
          })
        }

        let legend = result.data.legend
        let selectObj = {}
        let length = legend.length
        legend.map((item, i) => {
          if (i > 5 && i < (length - 5)) {
            selectObj[legend[i]] = false
          }
        })
        this.myChart.clear()
        this.myChart.setOption({
          tooltip: {
            trigger: 'axis'
          },
          legend: {
            data: legend
            // selected: selectObj
          },
          grid: {
            left: '3%',
            right: '4%',
            bottom: '3%',
            containLabel: true
          },
          toolbox: {
            feature: {
              saveAsImage: {}
            }
          },
          xAxis: {
            type: 'category',
            boundaryGap: false,
            data: this.xAxis
          },
          yAxis: {
            type: 'value'
          },
          series: seriess
        })
      })
    }
  }
}
</script>
