<template>

  <div id="app">

      <keep-alive>
          <router-view></router-view>
      </keep-alive>

  </div>

</template>

<script>

export default {
    name: 'APP',
    data() {
        return {
            title: '',

        }
    },

    mounted() {
        // setTimeout(()=> {
        //     $('#live2d-widget').draggable()
        // },3000)
    },
    created() {
        // setTimeout(() => {
        //     var a = {
        //         pluginRootPath: '/static/live2dw/',
        //         pluginJsPath: '/static/live2dw/lib/',
        //         pluginModelPath: '/static/live2dw/mashiro/',
        //         tagMode: false,
        //         debug: false,
        //         model: {jsonPath: '/static/live2dw/mashiro/ryoufuku.model.json'},
        //         display: {position: 'right', width: 450, height: 450},
        //         mobile: {show: false},
        //         log: true,
        //         index:2,
        //     };
        //     var b = {
        //         pluginRootPath: '/static/live2dw/',
        //         pluginJsPath: '/static/live2dw/lib/',
        //         pluginModelPath: '/static/live2dw/mashiro/',
        //         tagMode: false,
        //         debug: false,
        //         model: {jsonPath: '/static/live2dw/mashiro/seifuku.model.json'},
        //         display: {position: 'right', width: 450, height: 450},
        //         mobile: {show: false},
        //         log: true,
        //         index:2,
        //     };
        //     var c = {
        //         pluginRootPath: '/static/live2dw/',
        //         pluginJsPath: '/static/live2dw/lib/',
        //         pluginModelPath: '/static/live2dw/mashiro/',
        //         tagMode: false,
        //         debug: false,
        //         model: {jsonPath: '/static/live2dw/mashiro/shifuku.model.json'},
        //         display: {position: 'right', width: 450, height: 450},
        //         mobile: {show: false},
        //         log: true,
        //         index:1
        //     };
        //     var items = [a, b, c]
        //     console.log(items[Math.floor(Math.random()*items.length)])
        //     window.L2Dwidget.init(items[Math.floor(Math.random()*items.length)])
        // }, 1000)
    }
}
</script>
