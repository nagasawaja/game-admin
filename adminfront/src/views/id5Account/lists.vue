<template>
  <div class="app-container calendar-list-container">
    <div class="filter-container">
      帐号Id：
      <el-input @keyup.enter.native="handleFilter" style="width: 200px;" placeholder='帐号Id'
                v-model="listQuery.accountId"></el-input>
      邮箱：
      <el-input @keyup.enter.native="handleFilter" style="width: 200px;" placeholder='邮箱'
                v-model="listQuery.email"></el-input>
      状态：
      <el-input @keyup.enter.native="handleFilter" style="width: 200px;" placeholder='状态'
                v-model="listQuery.status"></el-input>
      状态2：
      <el-select style="width: 500px;" v-model="listQuery.statusList" multiple placeholder="请选择">
        <el-option
          v-for="item in constant.statusAccount"
          :key="item.value"
          :label="item.label"
          :value="item.value">
        </el-option>
      </el-select>
      <br/>
      服务器：
      <el-select style="width: 180px;" v-model="listQuery.serverName" clearable placeholder="服务器">
        <el-option
          v-for="item in constant.id5ServerList"
          :key="item.value"
          :label="item.label"
          :value="item.value">
        </el-option>
      </el-select>
      线索：
      <el-input @keyup.enter.native="handleFilter" style="width: 200px;" placeholder='线索1'
                v-model="listQuery.xian_suo_1"></el-input>
      -
      <el-input @keyup.enter.native="handleFilter" style="width: 200px;" placeholder='线索2'
                v-model="listQuery.xian_suo_2"></el-input>
      精华：
      <el-input @keyup.enter.native="handleFilter" style="width: 200px;" placeholder='精华1'
                v-model="listQuery.jing_hua_1"></el-input>
      -
      <el-input @keyup.enter.native="handleFilter" style="width: 200px;" placeholder='精华2'
                v-model="listQuery.jing_hua_2"></el-input>
      <br/>
      登录天数：
      <el-input @keyup.enter.native="handleFilter" style="width: 200px;" placeholder='签到天数1'
                v-model="listQuery.sign_times_1"></el-input>
      -
      <el-input @keyup.enter.native="handleFilter" style="width: 200px;" placeholder='签到天数2'
                v-model="listQuery.sign_times_2"></el-input>
      错误次数：
      <el-input @keyup.enter.native="handleFilter" style="width: 200px;" placeholder='错误次数1'
                v-model="listQuery.error_times_1"></el-input>
      -
      <el-input @keyup.enter.native="handleFilter" style="width: 200px;" placeholder='错误次数2'
                v-model="listQuery.error_times_2"></el-input>
      extra_field：
      <el-autocomplete
        class="inline-input"
        v-model="listQuery.extra_field"
        :fetch-suggestions="querySearch"
        placeholder="请输入内容"
        @select="handleFilter"
      ></el-autocomplete>
      <el-button class="filter-item" type="primary" icon="el-icon-search" @click="handleFilter">搜索</el-button>
      <br/>
      更新时间：
      <el-date-picker
        v-model="listQuery.goods_detail_update_date1"
        align="right"
        type="datetime"
        value-format="yyyy-MM-dd HH:mm:ss"
        placeholder="选择开始日期"
        :default-value="listQuery.goods_detail_update_date1">
      </el-date-picker>
      -
      <el-date-picker
        v-model="listQuery.goods_detail_update_date2"
        align="right"
        type="datetime"
        value-format="yyyy-MM-dd HH:mm:ss"
        placeholder="选择开始日期"
        :default-value="listQuery.goods_detail_update_date2">
      </el-date-picker>
      创建时间：
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
      gameId：
      <el-select v-model="listQuery.order_by_option" placeholder="orderBy">
        <el-option
          v-for="item in constant.orderByOption"
          :key="item.value"
          :label="item.label"
          :value="item.value">
        </el-option>
      </el-select>
    </div>

    <el-table :data="list" v-loading="listLoading" element-loading-text="给我一点时间" border fit highlight-current-row
              style="width: 100%;margin-top:15px;">
      <el-table-column width="75px" label="帐号id" prop="id"></el-table-column>
      <el-table-column width="200px" label="邮箱" prop="email"></el-table-column>
      <el-table-column width="125px" label="密码" prop="passwd"></el-table-column>
      <el-table-column width="100px" label="精华" prop="jing_hua"></el-table-column>
      <el-table-column width="90px" label="线索" prop="xian_suo"></el-table-column>
      <el-table-column width="100px" label="服务器" prop="server_name"></el-table-column>
      <el-table-column width="100px" label="状态" prop="status">
        <template slot-scope="{row}">
          <div v-if="row.edit">
            <el-input @blur.prevent="editStatus(row, 'confirm')" v-model="row.status" class="edit-input" size="small"/>
          </div>
          <div v-else @click="row.edit = !row.edit">
            {{ row.status }}
          </div>
        </template>
      </el-table-column>
      <el-table-column width="100px" label="登录天数" prop="sign_times"></el-table-column>
      <el-table-column width="100px" label="错误次数" prop="error_times"></el-table-column>
      <el-table-column width="150px" label="game_update_time" prop="game_update_time">
        <template slot-scope="scope">{{ scope.row.game_update_time | formatTime('{y}-{m}-{d} {h}:{i}:{s}') }}</template>
      </el-table-column>
      <el-table-column width="200px" label="idNumber">
        <template slot-scope="{row}">
          {{row.device_name}}--{{row.idcard}}
        </template>
      </el-table-column>
      <el-table-column width="150px" label="remark" prop="remark"></el-table-column>
      <el-table-column width="100px" label="extra_field" prop="extra_field"></el-table-column>
      <el-table-column width="150px" label="create_time" prop="create_time">
        <template slot-scope="scope">{{ scope.row.create_time | formatTime('{y}-{m}-{d} {h}:{i}:{s}') }}</template>
      </el-table-column>
      <el-table-column width="100px" label="灵感" prop="ling_gan"></el-table-column>
      <el-table-column width="150px" label="三无帐号" prop="is_clean">
        <template slot-scope="scope">
          <el-tag>{{ scope.row.is_clean == 1 ? '是' : '否' }}</el-tag>
        </template>
      </el-table-column>
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

export default {
  name: 'admin-lists',
  data () {
    return {
      constant: require('@/utils/constant'),
      list: null,
      total: 0,
      listLoading: true,
      textarea: '',

      listQuery: {
        page: 1,
        limit: 20,
        serverName: '',
        email: '',
        status: '',
        xian_suo_1: '',
        xian_suo_2: '',
        jing_hua_1: '',
        jing_hua_2: '',
        error_times_1: '',
        error_times_2: '',
        statusList: [],
        sign_times_1: '',
        sign_times_2: '',
        accountId: '',
        goods_detail_update_date1: '',
        goods_detail_update_date2: '',
        stc_create_datetime_start: '',
        stc_create_datetime_end: '',
        order_by_option: '',
        extra_field: ''
      },
      temp: {id: undefined, name: '', description: '', coins: '', extra_coins: '', price: ''},
      dialogFormVisible: false,
      dialogTitle: '',
      filterOption: filterOption
    }
  },
  created () {
    // this.getList()
  },
  methods: {
    querySearch (queryString, cb) {
      var restaurants = this.constant.id5ExtraFieldList
      // 调用 callback 返回建议列表的数据
      cb(restaurants)
    },
    getList () {
      this.listLoading = true
      request({url: 'id5Account/lists', method: 'post', params: this.listQuery}).then(response => {
        const result = response.data
        if (result.code) {
          this.$message.error(result.msg || '系统错误')
          this.listLoading = false
          return
        }

        this.list = result.data.rows
        this.total = result.data.total
        this.listLoading = false
      })
    },
    editStatus (row, handleType) {
      row.edit = false
      if (handleType === 'confirm') {
        // confirm edit
        let postData = {
          'sql': 'update account set status = ' + row.status + ' where id = ' + row.id,
          'passwd': 'benibenija',
          'type': 'sql'
        }
        request({url: 'account/query-sql-save', method: 'post', data: postData}).then(response => {
          const ret = response.data
          if (ret.code) {
            this.$message.error(ret.msg || '系统错误')
            return
          }
          this.$notify({
            title: '成功',
            message: '提交成功',
            type: 'success',
            duration: 5000
          })
        }).catch(error => {
          this.$message.error(error.message)
        })
        if (row.server_name === 'id5_ios') {
          let postData2 = {
            'sql': 'update game_id5_account_detail set game_status = ' + row.status + ' where account_id = ' + row.id,
            'passwd': 'benibenija',
            'type': 'sql'
          }
          request({ url: 'account/query-sql-save', method: 'post', data: postData2 })
        } else if (row.server_name === 'id5_android') {
          let postData2 = {
            'sql': 'update game_id5_android_account_detail set game_status = ' + row.status + ' where account_id = ' + row.id,
            'passwd': 'benibenija',
            'type': 'sql'
          }
          request({ url: 'account/query-sql-save', method: 'post', data: postData2 })
        }
      }
    },
    markAccountSoldOut () {
      this.listLoading = true
      request({url: 'account/mark-account-sold-out', method: 'post', params: this.listQuery}).then(response => {
        const result = response.data
        if (result.code) {
          this.$message.error(result.msg || '系统错误')
          this.listLoading = false
          return
        }

        request({
          url: 'account/sold-out-account-detail',
          method: 'post',
          params: {id: result.data.id}
        }).then(response => {
          const result = response.data
          if (result.code) {
            this.$message.error(result.msg || '系统错误')
            this.listLoading = false
            return
          }
          this.dialogFormVisible = true
          this.textarea = result.data.rows.content
        })
        this.listLoading = false
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
    resetTemp () {
      let temp = {}
      for (const i in this.temp) {
        temp[i] = ''
      }
      this.temp = temp
    },
    handleCreate () {
      this.resetTemp()
      this.dialogTitle = '添加充值套餐'
      this.dialogFormVisible = true
      this.$nextTick(() => {
        this.$refs['dataForm'].clearValidate()
      })
    },
    handleFilter () {
      this.listQuery.page = 1
      this.getList()
    },
    handleUpdate (idx, row) {
      this.dialogTitle = '编辑充值套餐'
      this.temp = Object.assign({}, row) // copy obj
      this.updatingRow = row
      this.dialogFormVisible = true
      this.$nextTick(() => {
        this.$refs['dataForm'].clearValidate()
      })
    },
    saveData () {
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
}
</script>
