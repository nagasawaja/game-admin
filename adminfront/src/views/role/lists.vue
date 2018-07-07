<template>
<div class="app-container calendar-list-container">
  <div class="filter-container">
    <el-button class="filter-item" style="margin-left: 10px;" @click="handleCreate" type="primary" icon="el-icon-edit">添加</el-button>
  </div>

  <el-table :key='tableKey' :data="list" v-loading="listLoading" element-loading-text="给我一点时间" border fit highlight-current-row style="width: 100%">
    <el-table-column label="id" width="65" prop="id"></el-table-column>
    <el-table-column width="150px" label="角色">
      <template slot-scope="scope">
          <el-tag>{{scope.row.rolename}}</el-tag>
        </template>
    </el-table-column>
    <el-table-column width="320px" label="描述" prop="description"></el-table-column>
    <el-table-column width="60px" label="状态">
      <template slot-scope="scope">
        <el-tag :type="scope.row.status!=1?'info':''">{{scope.row.status!=1?'禁用':'启用'}}</el-tag>
      </template>
    </el-table-column>
    <el-table-column align="center" label="操作" width="260" class-name="small-padding">
      <template slot-scope="scope">
          <el-button :disabled="scope.row.id==1" type="primary" size="mini" @click="handleAuth(scope.$index, scope.row)">权限设置</el-button>
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
      <el-form-item label="角色" prop="rolename">
        <el-input v-model="temp.rolename"></el-input>
      </el-form-item>
      <el-form-item label="描述">
        <el-input type="textarea" v-model="temp.description"></el-input>
      </el-form-item>
      <el-form-item label="状态">
        <el-switch v-model="temp.status" :disabled="temp.id==1" active-text="启用" inactive-text="禁用" :active-value="1" :inactive-value="2"></el-switch>
      </el-form-item>
    </el-form>
    <div slot="footer" class="dialog-footer">
      <el-button @click="dialogFormVisible = false">取消</el-button>
      <el-button type="primary" @click="saveData">确认</el-button>
    </div>
  </el-dialog>

  <auth-editor ref="authEditor"></auth-editor>

</div>
</template>

<script>
import request from '@/utils/request'
import waves from '@/directive/waves' // 水波纹指令
import AuthEditor from './AuthEditor'

export default {
  name: 'role-lists',
  components: { AuthEditor },
  directives: { waves },
  data () {
    return {
      tableKey: 0,
      list: null,
      total: null,
      listLoading: true,
      listQuery: { page: 1, limit: 20 },
      temp: { id: undefined, rolename: '', description: '', status: 1 },
      dialogFormVisible: false,
      dialogTitle: '',
      rules: {
        rolename: [{ required: true, message: '角色名称不能为空', trigger: 'blur' }]
      }
    }
  },
  created () {
    this.getList()
  },
  methods: {
    getList () {
      this.listLoading = true
      request({ url: 'role/lists', method: 'get', params: this.listQuery }).then(response => {
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
      this.temp.rolename = ''
      this.temp.id = undefined
    },
    handleCreate () {
      this.resetTemp()
      this.dialogTitle = '添加角色'
      this.dialogFormVisible = true
      this.$nextTick(() => {
        this.$refs['dataForm'].clearValidate()
      })
    },
    handleUpdate (idx, row) {
      this.dialogTitle = '编辑角色信息'
      this.temp = Object.assign({}, row) // copy obj
      this.temp.status = this.temp.status
      this.updatingRow = row
      this.dialogFormVisible = true
      this.$nextTick(() => {
        this.$refs['dataForm'].clearValidate()
      })
    },
    handleAuth (idx, row) {
      this.$refs['authEditor'].edit(row)
    },
    saveData () {
      this.$refs['dataForm'].validate((valid) => {
        if (valid) {
          let url = 'role/add'
          let addMode = true
          let params = Object.assign({}, this.temp)
          if (params.id > 0) {
            url = 'role/edit'
            addMode = false
          }

          request({ url: url, method: 'post', data: params }).then(response => {
            const ret = response.data
            if (ret.code) {
              this.$message.error(ret.msg || '系统错误')
              return
            }
            if (addMode) {
              params.id = ret.data.id
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
        request({ url: 'role/del', method: 'post', data: {id: row.id} }).then(response => {
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
