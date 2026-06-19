import axios from 'axios';
import authHeader from './auth-header';
import apiBaseUrl from './api-base';

const API_URL = apiBaseUrl()
const BASE_URL = process.env.VUE_APP_BASE_URL

export default {

  async login(user) {
    var response = await axios.post(API_URL + '/login', {
      email: user.email,
      password: user.password
    }, 
    {
      timeout: 15000,
      headers: {
        Accept: "application/vnd.api+json",
        "Content-Type": "application/vnd.api+json",
      }
    });
    if (response.data.access_token) {
      localStorage.setItem('user_free', JSON.stringify(response.data.access_token));
    }
    return response.data;
  },

  async logout() {
    await axios.post(API_URL + "/logout", {}, { headers: authHeader() })
    localStorage.removeItem('user_free');
  },

  async register(user) {
    var response = await axios.post(API_URL + '/register', {
      name: user.name,
      email: user.email,
      badge_number: user.badgeNumber,
      department: user.department,
      requested_role: user.requestedRole,
      password: user.password,
      password_confirmation: user.confirmPassword
    },
    {
      headers: {
        Accept: "application/vnd.api+json",
        "Content-Type": "application/vnd.api+json",
      }
    });
    if (response.data.access_token) {
      localStorage.setItem('user_free', JSON.stringify(response.data.access_token));
    }
    return response.data;
  },

  async passwordForgot(userEmail) {

    var response = await axios.post(API_URL + '/password-forgot', {
      redirect_url: BASE_URL + "/password-reset",
      email: userEmail
    })
    return response.status;
  },

  async passwordReset(passwordDTO) {

    var response = await axios.post(API_URL + '/password-reset', {
      password: passwordDTO.newPassword,
      password_confirmation: passwordDTO.confirmPassword,
      email: passwordDTO.email,
      token: passwordDTO.token
    })
    return response.status;
  }
}
