<template>
  <scroll-bar>
    <el-menu mode="vertical" :default-active="$route.path" :collapse="isCollapse" background-color="#304156" text-color="#bfcbd9" active-text-color="#409EFF" :default-openeds="openeds">
      <sidebar-item :menus="sideMenus"></sidebar-item>
    </el-menu>
  </scroll-bar>
</template>

<script>
import { mapGetters } from 'vuex'
import SidebarItem from './SidebarItem'
import ScrollBar from '@/components/ScrollBar'

export default {
  components: { SidebarItem, ScrollBar },
  computed: {
    ...mapGetters([
      'sideMenus',
      'sidebar'
    ]),
    isCollapse () {
      return !this.sidebar.opened
    },
    openeds () {
      let opens = []
      this.sideMenus.map((v) => {
        if (v.children && v.children.length) {
          opens.push(v.idx)
        }
      })

      return opens
    }
  }
}
</script>
