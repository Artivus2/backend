/**
 * plugins/vuetify.js
 *
 * Framework documentation: https://vuetifyjs.com`
 */

// Styles
import '@mdi/font/css/materialdesignicons.css'
import 'vuetify/styles'

// Composables
import { createVuetify } from 'vuetify'

// https://vuetifyjs.com/en/introduction/why-vuetify/#feature-guides
export default createVuetify({
  theme: {
    defaultTheme: 'dark',
    themes: {
      // light: {
      //   colors: {
      //     primary: 'ebebeb',
      //     secondary: 'ebebeb',
      //     background: 'ebebeb',
      //     success:'38a3a5',
      //     error:'618985',
      //     warning:'22577a',
      //     info:'c0c0c0',
      //     surface:'ffffff',
      //     "on-info":'000000',
      //   "on-error": 'ffffff',
      //   "on-success": 'ffffff',
      //   "on-warning": 'ffffff',
      //   "on-background": '000000',
      //   "on-surface": '000000'
       
         
      //   },
      // },
      dark: {
        colors:{
          background:'#1C2B46',
          success:'38a3a5',
          error:'618985',
          warning:'22577a',
          info:'c0c0c0',
          surface:'1C2B46'
          // "on-error": 'ffffff',
          // "on-success": 'ffffff',
          // "on-warning": 'ffffff',
          // "on-background": 'ffffff',
          // "on-surface": 'ffffff'
       
       
        }
      }
    },
  },
})
