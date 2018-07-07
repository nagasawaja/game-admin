import { constantRouterMap } from '@/router'
import Layout from '@/views/layout/Layout'
const _import = require('@/router/_import_' + process.env.NODE_ENV)

function genRoutesByMenus (menus, n) {
  const routes = []
  let home = ''
  let firstLevel = false
  if (!n) {
    firstLevel = true
    n = 0
  }
  for (const menu of menus) {
    let meta = {title: menu.title}
    let route = {meta, name: n + ''}
    menu.idx = n + ''

    if (menu.path) {
      route.component = _import(menu.path)
      route.path = '/' + menu.path
      menu.path = route.path
      if (menu.home && !home) {
        home = route.path
      }
    } else {
      route.component = Layout
      route.path = '#'
    }

    n += 1
    if (menu.children) {
      const [subroutes, nn, foundHome] = genRoutesByMenus(menu.children, n)
      route.children = subroutes
      if (!home && foundHome) {
        home = foundHome
      }
      n = nn
    }

    routes.push(route)
  }

  if (firstLevel && home) {
    routes.push({path: '', redirect: home})
  }

  return [routes, n, home]
}

const permission = {
  state: {
    routers: constantRouterMap,
    sideMenus: []
  },
  mutations: {
    SET_ROUTERS: (state, routers) => {
      routers.push({ path: '*', component: { template: '<h2>页面不存在<a style="font-size:13px;margin-left:8px;color:#333" href="javascript:history.back()">返回</a></h2>' }, hidden: true })
      state.routes = routers
    },
    SET_SIDE_MENUS: (state, sideMenus) => {
      state.sideMenus = sideMenus
    }
  },
  actions: {
    GenerateRoutes ({ commit }, data) {
      return new Promise(resolve => {
        const { menus } = data
        let [accessedRouters] = genRoutesByMenus(menus)
        commit('SET_SIDE_MENUS', menus)
        commit('SET_ROUTERS', accessedRouters)
        resolve()
      })
    }
  }
}

export default permission
