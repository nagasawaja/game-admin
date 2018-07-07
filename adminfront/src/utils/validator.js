export const usernameValidator = (rule, value, callback) => {
  if (!value || !/^[a-zA-Z0-9]{2,20}$/.test(value)) {
    callback(new Error('账号必须为2 ~ 20位字母或数字'))
  } else {
    callback()
  }
}

export const passwordValidator = (rule, value, callback) => {
  if ((rule.required && !value) || (value && !/^[\S]{6,20}$/.test(value))) {
    callback(new Error('密码必须为6 ~ 20位字母或数字'))
  } else {
    callback()
  }
}
