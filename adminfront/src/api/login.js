import request from '@/utils/request'
import md5 from 'js-md5'

export function loginByUsername (username, password) {
  const data = {
    username,
    password: md5(password)
  }
  return request({
    url: '/login/bypassword',
    method: 'post',
    data
  })
}

export function logout () {
  return request({
    url: '/login/logout',
    method: 'post'
  })
}

export function getUserInfo () {
  return request({
    url: '/admin/info',
    method: 'get'
  })
}
