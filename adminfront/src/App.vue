<template>
  <div id="app">
      <keep-alive>
          <router-view></router-view>
      </keep-alive>
  </div>
</template>

<script>

export default{
  name: 'APP',
    data() {
      return {
          title: ''
      }
    },
    created() {
    },
}
</script>
