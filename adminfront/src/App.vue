<template>
  <div id="app">
    <router-view></router-view>
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
