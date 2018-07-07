<template>
  <div class="menu-wrapper">
    <template v-for="item in menus" v-if="!item.hidden">
      <router-link v-if="!item.children" :to="item.path" :key="item.path">
        <el-menu-item :index="item.idx" :class="{'submenu-title-noDropdown':!isNest}">
          <i :class="'el-icon-'+item.icon"></i>
          <span slot="title">{{item.title}}</span>
        </el-menu-item>
      </router-link>

      <el-submenu v-else :index="item.idx" :key="item.idx">
        <template slot="title">
          <i :class="'el-icon-'+item.icon"></i>
          <span slot="title">{{item.title}}</span>
        </template>

        <template v-for="child in item.children" v-if="!child.hidden">
          <sidebar-item :is-nest="true" class="nest-menu" v-if="child.children&&child.children.length>0" :menus="[child]" :key="child.idx"></sidebar-item>

          <router-link v-else :to="child.path" :key="child.path">
            <el-menu-item :index="child.idx">
              <span>{{child.title}}</span>
            </el-menu-item>
          </router-link>
        </template>
      </el-submenu>

    </template>
  </div>
</template>

<script>

export default {
  name: 'SidebarItem',
  props: {
    menus: {
      type: Array
    },
    isNest: {
      type: Boolean,
      default: false
    }
  }
}
</script>
