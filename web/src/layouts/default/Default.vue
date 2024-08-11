<template>
  
  <v-app>
    
    <default-bar />
    
    <default-view/>
    
    <default-footer />
    
  </v-app>
  
</template>

<script>
  import DefaultBar from './AppBar.vue'
  import DefaultView from './View.vue'
  import DefaultFooter from './Footer.vue'

export default{
components: {
DefaultBar,
DefaultFooter,
DefaultView
},


}
</script>
