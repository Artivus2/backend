// Utilities
import { createStore } from 'vuex'
 
import axios from 'axios'
import api from '@/api'
 
export default new createStore({
  state: () => ({
    status: '',
    token: localStorage.getItem('access_token') || '',
    user : {},
    profile: {}
  }),
  mutations: {
    auth_request(state){
      state.status = 'loading'
    },
    auth_success(state, token, user){
      state.status = 'success'
      state.token = token
      state.user = user
    },
    auth_error(state){
      state.status = 'error'
    },
    logout(state){
      state.status = ''
      state.token = ''
    },
  },
  actions: {
    code({commit}, user){
      return new Promise((resolve, reject) => {
        commit('auth_request')
        api({url: '/api/user/code', data: user, method: 'POST' })
        .then(resp => {
          const token = resp.data.access_token
          const user = resp.data.user
          localStorage.setItem('access_token', token)
          axios.defaults.headers.common['Authorization'] = token
          commit('auth_success', token, user)
          resolve(resp)
        })
        .catch(err => {
          commit('auth_error')
          localStorage.removeItem('token')
          reject(err)
        })
      })
      const token =
      commit('auth_success', token, user)
    },
    register({commit}, user){
      return new Promise((resolve, reject) => {
        commit('auth_request')
        axios({url: '/api/user/code', data: user, method: 'POST' })
        .then(resp => {
          const token = resp.data.access_token
          const user = resp.data.user
          localStorage.setItem('access_token', token)
          axios.defaults.headers.common['Authorization'] = token
          commit('auth_success', token, user)
          resolve(resp)
        })
        .catch(err => {
          commit('auth_error', err)
          localStorage.removeItem('token')
          reject(err)
        })
      })
    },
    changePassword({commit}, user){
      return new Promise((resolve, reject) => {
        commit('auth_request')
        axios({url: '/api/user/change-password', data: user, method: 'POST' })
        .then(resp => {
          const token = resp.data.access_token
          const user = resp.data.user
          localStorage.setItem('access_token', token)
          axios.defaults.headers.common['Authorization'] = token
          commit('auth_success', token, user)
          resolve(resp)
        })
        .catch(err => {
          commit('auth_error')
          localStorage.removeItem('token')
          reject(err)
        })
      })
      const token =
      commit('auth_success', token, user)
    },
    logout({commit}){
      return new Promise((resolve, reject) => {
        commit('logout')
        localStorage.removeItem('access_token')
        localStorage.removeItem('profile')
        localStorage.removeItem('profileGetter')
        localStorage.removeItem('charts')
        localStorage.removeItem('currency')
        localStorage.removeItem('payments')
        localStorage.removeItem('userpayment')
        localStorage.removeItem('wallet')
        localStorage.removeItem('type')
        localStorage.removeItem('theme')
        localStorage.removeItem('p2p_data')
        delete axios.defaults.headers.common['Authorization']
        resolve()
      })
    },
   
    getProfile({commit}) {
      return new Promise((resolve, reject) => {
        commit('auth_request')
        api({url: '/api/user/profile',
          headers: {
          'Authorization': localStorage.getItem('access_token')
          }, method:'GET'
        })
        .then(resp => {
          //const profile = resp.data
          resolve(resp)
        })
        .catch(err => {
          reject(err)
        })
      })
    },

  },
   
  getters : {
    isLoggedIn: state => !!state.token,
    authStatus: state => state.status,
    profile: state => state.profile,
  }
})