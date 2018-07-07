<template>
  <div id="app">
    <router-view v-wechat-title="title"></router-view>
  </div>
</template>

<script>

import request from '@/utils/request'
export default{
  name: 'APP',
    data() {
      return {
          title: ''
      }
    },
    created() {
        this.title = request({
            url: 'systemConfig/sys-config-menu',
            methos: 'get'
        }).then(response => {
            const result = response.data;
            this.title = result.data.title;
        })
    },
}
</script>
