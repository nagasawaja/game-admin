<template>
<div class="app-container calendar-list-container">
  <div class="filter-container">
    <el-button class="filter-item" style="margin-left: 10px;" @click="handleCreate" type="primary" icon="el-icon-edit">添加</el-button>
  </div>

  <el-table :key='tableKey' :data="list" v-loading="listLoading" element-loading-text="给我一点时间" border fit highlight-current-row style="width: 100%">
    <el-table-column label="id" width="65" prop="id"></el-table-column>
    <el-table-column width="150px" label="账号" prop="username"></el-table-column>
    <el-table-column width="150px" label="所属角色">
      <template slot-scope="scope">
          <el-tag>{{scope.row.rolename}}</el-tag>
        </template>
    </el-table-column>
    <el-table-column width="110px" label="最后登录ip" prop="login_ip"></el-table-column>
    <el-table-column width="150px" label="最后登录时间">
      <template slot-scope="scope">{{scope.row.login_time | formatTime('{y}-{m}-{d} {h}:{i}')}}</template>
    </el-table-column>
    <el-table-column width="150px" label="真实姓名" prop="realname"></el-table-column>
    <el-table-column label="操作" width="230" class-name="small-padding fixed-width" align="center">
      <template slot-scope="scope">
          <el-button type="primary" size="mini" @click="handleUpdate(scope.$index, scope.row)">编辑</el-button>
          <el-button :disabled="scope.row.id==1" size="mini" type="danger" @click="handleDelete(scope.$index,scope.row)">删除
          </el-button>
        </template>
    </el-table-column>
  </el-table>

  <div class="pagination-container">
    <el-pagination background @size-change="handleSizeChange" @current-change="handleCurrentChange" :current-page.sync="listQuery.page" :page-sizes="[10,20,30, 50]" :page-size="listQuery.limit" layout="total, sizes, prev, pager, next, jumper" :total="total">
    </el-pagination>
  </div>

  <el-dialog :title="dialogTitle" :visible.sync="dialogFormVisible" width="30%">
    <el-form :rules="rules" ref="dataForm" :model="temp" label-position="left" label-width="80px" style='width: 400px; margin-left:50px;'>
      <el-form-item label="账号" prop="username">
        <el-input v-model="temp.username" :readonly="temp.id>0"></el-input>
      </el-form-item>
      <el-form-item label="真实姓名" prop="realname">
        <el-input v-model="temp.realname"></el-input>
      </el-form-item>
      <el-form-item label="密码" prop="password">
        <el-input v-model="temp.password" type="password"></el-input>
      </el-form-item>
      <el-form-item label="确认密码" prop="confirm_password">
        <el-input v-model="temp.confirm_password" type="password"></el-input>
      </el-form-item>
      <el-form-item label="角色" prop="roleid">
        <el-select class="filter-item" :disabled="temp.id==1" v-model="temp.roleid" placeholder="请选择角色">
          <el-option v-for="item in roles" :key="item.id" :label="item.rolename" :value="item.id">
          </el-option>
        </el-select>
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
import md5 from 'js-md5'
import request from '@/utils/request'
import { usernameValidator, passwordValidator } from '@/utils/validator'
import waves from '@/directive/waves' // 水波纹指令

export default {
  name: 'admin-lists',
  directives: { waves },
  data () {
    return {
      tableKey: 0,
      list: null,
      total: null,
      listLoading: true,
      listQuery: { page: 1, limit: 20 },
      roles: [],
      temp: { id: undefined, username: '', realname: '', roleid: '', password: '', confirm_password: '' },
      dialogFormVisible: false,
      dialogTitle: '',
      rules: {
        roleid: [{ required: true, message: '角色必选', trigger: 'change' }],
        username: [{ required: true, trigger: 'blur', validator: usernameValidator }],
        password: [{ required: true, trigger: 'blur', validator: passwordValidator }],
        confirm_password: [{ required: true,
          trigger: 'blur',
          validator: (rule, value, callback) => {
            if (value !== this.temp.password) {
              callback(new Error('密码不一致'))
              return
            }
            passwordValidator(rule, value, callback)
          }
        }]
      }
    }
  },
  created () {
    this.getList()
  },
  methods: {
    getList () {
      this.listLoading = true
      request({ url: 'admin/lists', method: 'get', params: this.listQuery }).then(response => {
        const result = response.data
        if (result.code) {
          this.$message.error(result.msg || '系统错误')
          this.listLoading = false
          return
        }

        this.list = result.data.items
        this.total = result.data.total
        this.listLoading = false
      })
    },
    fetchRoles () {
      if (this.roles.length) {
        return
      }
      request({ url: 'role/lists?status=1', method: 'get' }).then(response => {
        const ret = response.data
        if (ret.code) {
          this.$message.error(ret.msg || '系统错误')
          return
        }

        this.roles = ret.data.items
      }).catch(error => {
        this.$message.error(error.message)
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
      this.rules.password[0].required = true
      this.rules.confirm_password[0].required = true
      this.resetTemp()
      this.dialogTitle = '添加管理员'
      this.dialogFormVisible = true
      this.$nextTick(() => {
        this.$refs['dataForm'].clearValidate()
      })
      this.fetchRoles()
    },
    handleUpdate (idx, row) {
      this.rules.password[0].required = false
      this.rules.confirm_password[0].required = false
      this.dialogTitle = '编辑管理员信息'
      this.temp = Object.assign({}, row) // copy obj
      this.updatingRow = row
      this.dialogFormVisible = true
      this.$nextTick(() => {
        this.$refs['dataForm'].clearValidate()
      })
      this.fetchRoles()
    },
    saveData () {
      this.$refs['dataForm'].validate((valid) => {
        if (valid) {
          let url = 'admin/add'
          let addMode = true
          let params = Object.assign({}, this.temp)
          if (params.id > 0) {
            url = 'admin/edit'
            addMode = false
          }

          if (params.password) {
            params.password = md5(params.password)
            params.confirm_password = md5(params.confirm_password)
          }

          request({ url: url, method: 'post', data: params }).then(response => {
            const ret = response.data
            if (ret.code) {
              this.$message.error(ret.msg || '系统错误')
              return
            }
            params.rolename = this.roles.find((v) => v.id === params.roleid).rolename
            if (addMode) {
              params.id = ret.data.id
              params.password = ''
              params.confirm_password = ''
              this.list.unshift(params)
            } else {
              for (const i in params) {
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
    handleDelete (idx, row) {
      this.$confirm('此操作将永久删除该管理员, 是否继续?', '确认', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        request({ url: 'admin/del', method: 'post', data: {id: row.id} }).then(response => {
          const ret = response.data
          if (ret.code) {
            this.$message.error(ret.msg || '系统错误')
            return
          }

          this.$notify({
            title: '成功',
            message: '删除成功',
            type: 'success',
            duration: 2000
          })

          this.list.splice(idx, 1)
        }).catch(error => {
          this.$message.error(error.message)
        })
      }).catch(() => {})
    }
  }
}
</script>
