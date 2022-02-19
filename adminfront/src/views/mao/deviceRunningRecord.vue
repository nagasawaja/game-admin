<template>
  <div v-loading="listLoading">
    <div class="filter-container">
      汇总频率：
      <el-select style="width: 200px;" clearable placeholder="汇总频率"
                v-model="listQuery.frequencySecond">
        <el-option
          v-for="item in frequencySecondList"
          :key="item"
          :label="item"
          :value="item">
        </el-option>
      </el-select>
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
      数据类型
      <el-select style="width: 200px;" clearable placeholder="数据类型"
                 v-model="listQuery.dataType">
        <el-option
          v-for="item in dataTypeList"
          :key="item"
          :label="item"
          :value="item">
        </el-option>
      </el-select>
      <br/>
      显示的电脑：  <el-select style="width: 200px;" clearable v-model="computerNameSelectList"  placeholder="请选择">
      <el-option
        v-for="(value,key) in computerNameList"
        :key="value"
        :label="value"
        :value="value">
      </el-option>
    </el-select>
      是否汇总：<el-select style="width:100px;" v-model="collectDataFlag"  placeholder="请选择">
        <el-option
          v-for="(value,key) in constant.yesOrNoShowText"
          :key=key
          :label=value
          :value=key>
        </el-option>
      </el-select>
      <el-button class="filter-item" type="primary" icon="el-icon-search" @click="handleFilter2">刷新表单</el-button>
    </div>
    <div id="mainaaa" style="width:100%; height:85vh;">

    </div>
  </div>
</template>

<script>
import request from '@/utils/request'
import * as echarts from 'echarts'
import '@/utils/common_function'

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
      seriess: [],
      xAxis: [],
      selectObj: {},
      computerNameList: [],
      computerNameSelectList: [],
      collectDataFlag: '0',
      constant: require('@/utils/constant'),
      frequencySecondList: [600, 900, 1800, 3600],
      dataTypeList: ['total', 'suc'],
      listQuery: {
        frequencySecond: 1800,
        end_time: stcCreate_datetime.format('yyyy-MM-dd 23:59:59'),
        start_time: stcCreate_datetime.format('yyyy-MM-dd 00:00:00'),
        gameId: '7539',
        dataType: 'total',
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
    handleFilter2 () {
      // 过滤
      if(typeof(this.computerNameSelectList) != "object") {
        let a = this.computerNameSelectList
        this.computerNameSelectList = []
        this.computerNameSelectList.push(a)
      }
      this.selectObj = {}
      if (this.computerNameSelectList.length > 0) {
        for (const i in this.legend) {
          let findFlag = false
          for (const o in this.computerNameSelectList) {
            // console.log(this.legend[i] + '----' + this.computerNameSelectList[o])
            if (this.legend[i].indexOf(this.computerNameSelectList[o]) === 0) {
              findFlag = true
              break
            }
          }
          if (findFlag === false) {
            this.selectObj[this.legend[i]] = false
          }
        }
      }

      this.chartSetOption(this.legend, this.xAxis, this.seriess, this.selectObj, this.collectDataFlag)
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
        this.listLoading = false
        this.xAxis = result.data.xAxis
        this.legend = result.data.legend
        this.computerNameList = result.data.computer_name
        let aa = result.data.series
        this.seriess = []
        for (var i in aa) {
          this.seriess.push({
            name: i,
            type: 'line',
            stack: 'total',
            // smooth: true,
            data: aa[i]
          })
        }

        this.chartSetOption(this.legend, this.xAxis, this.seriess, this.selectObj,this.collectDataFlag)
      })
    },
    chartSetOption (legend, xAxis, seriess, selectObj, collectDataFlag) {
      // let yInterval = 10
      // if(this.listQuery.frequencySecond >= 1800) {
      //   yInterval = 100
      // }
      let stack = 'total'
      let interval = 10
      if (collectDataFlag === '0') {
        // 不汇总
        stack = ''
        interval = 5
      }
      // 选择了电脑，选择汇总，选择suc
      console.log(this.listQuery.dataType)
      if ( Object.keys(selectObj).length > 0 && collectDataFlag === '1' && this.listQuery.dataType === 'suc') {
        interval = 5
      }
      // 数据选择成功的，且数据不汇总
      if (this.listQuery.dataType !== 'total' && collectDataFlag === '0') {
        interval = 1
      }
      // 没有选择对应的电脑和sum数据的时候
      if (Object.keys(selectObj).length === 0 && this.listQuery.dataType === 'total' && collectDataFlag === '1') {
        interval = 50
      }
      for (const i in seriess) {
        seriess[i].stack = stack
      }

      this.myChart.clear()
      this.myChart.setOption({
        tooltip: {
          trigger: 'axis'
        },
        legend: {
          data: legend,
          selected: selectObj
        },
        grid: {
          left: '2%',
          right: '10%',
          bottom: '3%',
          top: '10%',
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
          type: 'value',
          interval: interval
        },
        series: seriess
      })
    }
  }
}
</script>
