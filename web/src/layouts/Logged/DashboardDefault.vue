<template>
  
  <v-layout>
    <!-- <v-app-bar class="d-flex d-sm-none"> -->
      <v-app-bar>
    
    
      <v-row style="width: 100%;">
        <v-col>
        <v-list-item-title class="text-capitalize" align="left">
    
          <v-img
          width="30px"
          style="margin:10px"
          src="../../assets/greenavi.png"
          >
          </v-img>  
        </v-list-item-title>
        </v-col>

      </v-row>
     



      
    
    
    <!-- <v-main class="d-flex">
      
    </v-main>
    <v-main class="d-none d-sm-flex d-md-flex d-lg-flex d-xl-flex">
      2
    
    <v-divider vertical></v-divider>
    </v-main> 
    
    <v-main class="d-none d-md-flex d-lg-flex d-xl-flex">
      3
    <v-divider vertical></v-divider>
    </v-main>
    
    <v-main class="d-none d-lg-flex d-xl-flex">
      4
    <v-divider vertical></v-divider>
    </v-main>
    
    <v-main class="d-none d-xl-flex">
      5
    </v-main> -->
<!--  xs sm md lg xl
Hidden only on xs	.d-none .d-sm-flex
Hidden only on sm	.d-sm-none .d-md-flex
Hidden only on md	.d-md-none .d-lg-flex
Hidden only on lg	.d-lg-none .d-xl-flex
Hidden only on xl	.d-xl-none

Visible only on xs	.d-flex .d-sm-none
Visible only on sm	.d-none .d-sm-flex .d-md-none
Visible only on md	.d-none .d-md-flex .d-lg-none
Visible only on lg	.d-none .d-lg-flex .d-xl-none
Visible only on xl	.d-none .d-xl-flex -->
    
      <!-- <v-responsive @click="logout" style="cursor: pointer;" >
         <v-list-item>
        <v-list-item-title 
        align="right"
        >
       
        </v-list-item-title>
          
        </v-list-item>
      </v-responsive> -->
    
  
      <v-app-bar-nav-icon class="humburger"
         style="position: absolute; right: 2%; top: 11px; border: 1.66667px solid #2666FF;"
         color="#2666FF"
       
         @click.stop="drawer = !drawer"
         ></v-app-bar-nav-icon>
  </v-app-bar>      
  
  <v-main>
      <v-navigation-drawer
      location="top"
      rail
      v-model="drawer"
      style="min-height: 400px;"
      
      >
      <v-list      
      >
          
              <v-list-item v-for="(item, i) in items" :key="i" :to="item.route" link>
                    <v-icon :icon="item.icon"></v-icon> {{ item.text }}
                  </v-list-item> 
            
          </v-list>
          <v-divider></v-divider>
        <v-list>
          <v-list-item @click="logout" style="cursor: pointer;" >
              
              <v-icon x-large>mdi-logout-variant</v-icon>  Выйти       
              
            </v-list-item>
          </v-list>
     
      
      </v-navigation-drawer>
 
      
      <router-view></router-view>
    
    </v-main>
    
      
  
  </v-layout>

</template>

<script>
  
import api from '../../api'



export default {


  computed : {
    isLoggedIn : function(){ 
      //console.log(this.$store.getters.isLoggedIn)
      return this.$store.getters.isLoggedIn
    },
    
    
  },
  mounted() {
    
    
    if (localStorage.getItem('profileGetter') == 1)
    {

      this.profileData = JSON.parse(localStorage.getItem('profile'))
      //console.log(this.profileData)
      //console.log('Получили профиль пользователя из хранилища')

    }
    else {
    this.$store.dispatch('getProfile')
       .then((response) => {
       this.profileData = response.data
       localStorage.setItem('profileGetter', 1)
       localStorage.setItem('profile', JSON.stringify(this.profileData))
      }
       )
       .catch(err => console.log(err))
  }
   // setInterval(this.getNow, 1000);

        
      


  },
  methods: {
    logout: function () {
      this.$store.dispatch('logout')
      .then(() => {
        this.$router.push('/login')
      })
    },
  getNow: function() {
      const today = new Date();
      // const time = today.getHours() + ":" + today.getMinutes() + ":" + (today.getSeconds() < 10 ?  "0" + today.getSeconds() : today.getSeconds()) ;
      const date = ((today.getDate())  < 10 ?  "0" + (today.getDate()) : (today.getDate()))+'-'+((today.getMonth() + 1)  < 10 ?  "0" + (today.getMonth()+1) : (today.getMonth()+1))+'-'+today.getFullYear();
      //const date = (new Date(Date.now() - (new Date()).getTimezoneOffset() * 60000)).toISOString().substring(0, 10)
      const time = (today.getHours() < 10 ?  "0" + today.getHours() : today.getHours()) + ":" + (today.getMinutes() < 10 ?  "0" + today.getMinutes() : today.getMinutes()) + ":" + (today.getSeconds() < 10 ?  "0" + today.getSeconds() : today.getSeconds())
      const dateTime = date +' '+ time
      this.timestamp = dateTime
  },
},
data: () => ({
    
    drawer: false,
    profileData: [],
    timestamp: "",
    
    items: [
        {icon: 'mdi-bank', text: 'Главная', route: '/dashboard/main'},
        {icon: 'mdi-cash-sync', text: 'Биржа', route: '/dashboard/trade'},
        {icon: 'mdi-swap-horizontal', text: 'P2P', route: '/dashboard/p2p'},
        {icon: 'mdi-arrow-left-right-bold-outline', text: 'B2B', route: '/dashboard/b2b'},
        {icon: 'mdi-wallet', text: 'Портфель', route: '/dashboard/wallet'},
        {icon: 'mdi-human', text: 'Профиль', route: '/dashboard/profile'},
        
        
    ]
    })

}
</script>
<script setup>

import { ref } from "vue";
import { useTheme } from "vuetify";
const theme = useTheme();
const darkMode = ref(false);
const toggleTheme = () => {
theme.global.name.value = darkMode.value ? "dark" : "light";
localStorage.setItem("theme", darkMode.value)

////console.log(`Current theme is dark? ${darkMode.value}`);

};
</script>
<style>
.v-row{
  padding: 0px;
  margin: 0px;
}
.v-col{
  padding: 0px;
  margin: 0px;
  font-size: 12px;
}

</style>