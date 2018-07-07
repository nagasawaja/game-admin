<template>
<el-dialog :title="dialogTitle" :visible.sync="dialogVisible" width="30%">
  <el-checkbox :indeterminate="isIndeterminate" v-model="checkAll" @change="handleCheckAllChange">全选</el-checkbox>
  <div style="margin: 15px 0;"></div>
  <el-checkbox-group v-model="checkedURIs" @change="handleCheckedChange">
    <el-checkbox v-for="item in uris" :label="item.uri" :key="item.uri"><span :style="{'font-weight':item.menu?'bold':''}" :title="item.menu?'对应菜单`'+item.menu+'`':item.title">{{item.title}}</span></el-checkbox>
  </el-checkbox-group>
  <div slot="footer" class="dialog-footer">
    <el-button @click="dialogVisible = false">取消</el-button>
    <el-button type="primary" @click="saveData">确认</el-button>
  </div>
</el-dialog>
</template>

<script>
import request from '@/utils/request'

export default {
  name: 'AuthEditor',
  data () {
    return {
      dialogVisible: false,
      dialogTitle: '',
      checkAll: false,
      checkedURIs: [],
      uris: [],
      editedRow: null,
      isIndeterminate: false
    }
  },
  methods: {
    edit (item) {
      this.dialogVisible = true
      this.dialogTitle = item.rolename + ' 角色权限编辑'
      this.editedRow = item

      request({
        url: 'role/listAuth',
        method: 'get',
        params: {roleid: item.id}
      }).then(response => {
        const ret = response.data
        if (ret.code) {
          this.$message.error(ret.msg || '系统错误')
          return
        }

        this.uris = ret.data.items
        let checkeds = []
        this.uris.map((v, i) => {
          if (v.checked) {
            checkeds[i] = v.uri
          }
        })
        this.checkedURIs = checkeds
      }).catch(error => {
        this.$message.error(error.message)
      })
    },
    saveData () {
      let params = {uris: this.checkedURIs.join(','), roleid: this.editedRow.id}

      request({
        url: 'role/editAuth',
        method: 'post',
        data: params
      }).then(response => {
        const ret = response.data
        if (ret.code) {
          this.$message.error(ret.msg || '系统错误')
          return
        }

        this.dialogVisible = false
        this.$notify({
          title: '成功',
          message: '提交成功',
          type: 'success',
          duration: 2000
        })
      }).catch(error => {
        this.$message.error(error.message)
      })
    },
    handleCheckAllChange (val) {
      let uris = []
      this.uris.map((v, i) => {
        uris[i] = v.uri
      })
      this.checkedURIs = val ? uris : []
      this.isIndeterminate = false
    },
    handleCheckedChange (value) {
      let checkedCount = value.length
      this.checkAll = checkedCount === this.uris.length
      this.isIndeterminate = checkedCount > 0 && checkedCount < this.uris.length
    }
  }
}
</script>
