const getters = {
  sidebar: state => state.app.sidebar,
  visitedViews: state => state.tagsView.visitedViews,
  cachedViews: state => state.tagsView.cachedViews,
  token: state => state.user.token,
  avatar: state => state.user.avatar,
  name: state => state.user.name,
  introduction: state => state.user.introduction,
  status: state => state.user.status,
  roles: state => state.user.roles,
  rolelist: state => state.user.roles,
  setting: state => state.user.setting,
  sideMenus: state => state.permission.sideMenus,
  routes: state => state.permission.routes
}
export default getters
