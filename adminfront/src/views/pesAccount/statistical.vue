<template>
  <div class="app-container calendar-list-container">
    <div class="filter-container">
      服务器：
      <el-select style="width: 180px;" v-model="listQuery.serverName" clearable placeholder="服务器">
        <el-option
          v-for="item in constant.pesServerList"
          :key="item.value"
          :label="item.label"
          :value="item.value">
        </el-option>
      </el-select>

      最后的更新时间：
      <el-date-picker
        v-model="listQuery.last_update_time"
        type="datetime"
        value-format="yyyy-MM-dd HH:00:00"
        placeholder="选择日期时间"
        :default-value="listQuery.last_update_time"
      >
      </el-date-picker>
      <br/>
      金币：
      <el-input @keyup.enter.native="handleFilter" style="width: 200px;" placeholder='金币1'
                v-model="listQuery.gold_1"></el-input>
      -
      <el-input @keyup.enter.native="handleFilter" style="width: 200px;" placeholder='金币2'
                v-model="listQuery.gold_2"></el-input>
      <br/>
      状态：
      <el-input @keyup.enter.native="handleFilter" style="width: 200px;" placeholder='状态'
                v-model="listQuery.status"></el-input>
      提取数量：
      <el-input @keyup.enter.native="handleFilter" style="width: 200px;" placeholder='提取数量'
                v-model="listQuery.getNumber"></el-input>
      <el-button class="filter-item" type="primary" icon="el-icon-search" @click="getList">刷新</el-button>
      <el-button class="filter-item" type="primary" icon="el-icon-download" @click="markAccountSoldOut">导出</el-button>
    </div>


    <el-table :key='tableKey' height="550px" :data="list" v-loading="listLoading" element-loading-text="给我一点时间"
              border fit highlight-current-row style="display:inline-block;width:auto;margin-top:15px">
      <el-table-column width="100px" label="金币" prop="gold"></el-table-column>
      <el-table-column width="100px" label="资金" prop="money"></el-table-column>
      <el-table-column width="100px" label="签到天数" prop="sign_times"></el-table-column>
      <el-table-column width="100px" label="错误次数" prop="error_times"></el-table-column>
      <el-table-column width="100px" label="数量" prop="count"></el-table-column>
    </el-table>
    <div class="pagination-container">
      <el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange"
                     :page-sizes="[10,20,30, 50]" :page-size="listQuery.limit"
                     layout="total, sizes, prev, pager, next, jumper" :total="total">
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
import '@/utils/common_function'

export default {
  name: 'admin-lists',
  data() {
    // eslint-disable-next-line camelcase
    var stcCreate_datetime = new Date()
    return {
      constant: require('@/utils/constant'),
      tableKey: 0,
      list: null,
      total: 0,
      listLoading: true,
      textarea: '',
      listQuery: {
        page: 1,
        limit: 20,
        serverName: 'pes_android',
        getNumber: 50,
        status: 2,
        black_player_1: '',
        black_player_2: '',
        gold_player_1: '',
        gold_player_2: '',
        gold_1: '',
        gold_2: '',
        money_1: '',
        money_2: '',
        last_update_time: stcCreate_datetime.format('yyyy-MM-dd 00:00:00')
      },
      temp: {id: undefined, name: '', description: '', coins: '', extra_coins: '', price: ''},
      dialogFormVisible: false,
      dialogTitle: '',
      filterOption: filterOption
    }
  },
  created() {
    this.getList()
  },
  methods: {
    getList() {
      this.listLoading = true
      request({url: 'pesAccount/statistical', method: 'post', params: this.listQuery}).then(response => {
        const result = response.data
        if (result.code) {
          this.$message.error(result.msg || '系统错误')
          this.listLoading = false
          return
        }

        this.list = result.data.rows;
        this.total = 10;
        this.listLoading = false
      })
    },
    markAccountSoldOut() {
      this.listLoading = true
      request({url: 'pesAccount/mark-account-sold-out', method: 'post', params: this.listQuery}).then(response => {
        const result = response.data;
        if (result.code) {
          this.$message.error(result.msg || '系统错误')
          this.listLoading = false
          return
        }

        request({
          url: 'pesAccount/sold-out-account-detail',
          method: 'post',
          params: {id: result.data.id}
        }).then(response => {
          const result = response.data;
          if (result.code) {
            this.$message.error(result.msg || '系统错误')
            this.listLoading = false
            return
          }
          this.dialogFormVisible = true;
          this.textarea = result.data.rows.content;
        })
        this.listLoading = false;
        this.dialogTitle = '服务器:' + this.listQuery.serverName + '----黑球:' + this.listQuery.black_player_1 + '-' + this.listQuery.black_player_2
          + '----金球:' + this.listQuery.gold_player_1 + '-' + this.listQuery.gold_player_2
          + '----金币' + this.listQuery.gold_1 + '-' + this.listQuery.gold_2
          + '----提取数量:' + this.listQuery.getNumber;
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
      console.log(val)
      console.log(this.listQuery.page)
      if (this.listQuery.page === val) {
        return
      }
      this.listQuery.page = val
      this.getList()
    },
    resetTemp() {
      let temp = {}
      for (const i in this.temp) {
        temp[i] = ''
      }
      this.temp = temp
    },
    handleFilter() {
      this.listQuery.page = 1
      this.getList()
    },
    handleUpdate(idx, row) {
      this.dialogTitle = '编辑充值套餐'
      this.temp = Object.assign({}, row) // copy obj
      this.updatingRow = row;
      this.dialogFormVisible = true
      this.$nextTick(() => {
        this.$refs['dataForm'].clearValidate()
      })
    },
    saveData() {
      this.$refs['dataForm'].validate((valid) => {
        if (valid) {
          let url = 'recharge/add'
          let addMode = true
          let params = Object.assign({}, this.temp)
          if (params.id > 0) {
            url = 'recharge/edit'
            addMode = false
          }

          request({url: url, method: 'post', data: params}).then(response => {
            const ret = response.data
            if (ret.code) {
              this.$message.error(ret.msg || '系统错误')
              return
            }
            if (addMode) {
              this.list.unshift(ret.data)
            } else {
              for (const i in ret.data) {
                if (this.updatingRow[i]) {
                  this.updatingRow[i] = params[i]
                }
              }
            }

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
  },
  filters: {
    wxStateFilter(status) {
      if (status == 6) {
        return 'success';
      } else {
        return 'info';
      }
    }
  }
}
</script>
