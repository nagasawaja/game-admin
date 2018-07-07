// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import Vue from 'vue'
import App from './App'
import router from './router'

import 'normalize.css/normalize.css'// A modern alternative to CSS resets
import 'element-ui/lib/theme-chalk/index.css'
import '@/styles/index.scss' // global css
import store from './store'
import Element from 'element-ui'
import './icons' // icon
import './permission' // permission control
import * as filters from './filters' // global filters
import VueWechatTitle from 'vue-wechat-title';
// register global utility filters.
Object.keys(filters).forEach(key => {
  Vue.filter(key, filters[key])
})

Vue.config.productionTip = false

Vue.use(VueWechatTitle)
Vue.use(Element, {
  size: 'mini' // set element-ui default size: medium small mini
})

/* eslint-disable no-new */
new Vue({
  el: '#app',
  router,
  store,
  components: { App },
  template: '<App/>'
})
