<template>
  <v-main class="vmain">
    <div v-if="!isLoggedIn">
    <div class='block'><p>{{charts.symbol}}  {{ charts.price }}</p></div>
    <div class="main-screen">
<v-img src="../../assets/main-screen.svg"></v-img>
    </div>
    <v-btn class="start-button">
      <div class="start-button-text">
        Получите 50 $ за каждый завершенный ордер
      </div>
    </v-btn>
    <div class="liquid">
<v-img src="../../assets/liquid.svg"></v-img>
    </div>
  </div>
    <router-view v-else/>
  </v-main>
</template>

<script>
import axios from 'axios'
import apibin from '../../apibin'
  

export default {
  mounted(){
    apibin.get('price?symbol=BTCUSDT').
      then(response => {
        //console.log('Получили данные по доступным криптовалютам')
        this.charts = response.data
        //console.log(this.charts)
        //localStorage.setItem('p2p_data', JSON.stringify(response.data))

        ////console.log(this.charts)
      }).catch(error => {
        //console.log(error)
      })
 },
 data() {
      return {
        charts:[],
      }
      
},
computed : {
    isLoggedIn : function(){ 
      //console.log(this.$store.getters.isLoggedIn)
      return this.$store.getters.isLoggedIn
    },
  },
}
</script>

<style scoped>
.vmain {
position: absolute;
top: -64px;
}

.block{
  height: 30px;
  width: 200vh;
  margin:0 auto;
  
  
}
.block p{
  text-align:center;
  color:#fff;
  text-transform: uppercase;
  padding-top: 65px;
  animation: text 25s infinite linear;
  padding-left: 100%;
  white-space: nowrap;
  position: absolute;
  width: 1920px;
  height: 30px;
  left: calc(50% - 1920px/2);
  top: 65px;
  z-index: 1;


}
@keyframes text {
  0%{
    transform: translate(0, 0);
  }
  
  100%{
    transform: translate(-160%, 0);
  }
}

.start-button {
  /* div.Intro_actions__1S9hc */


width: 1920px;
height: 52px;
background: #2666FF;

}
/* Heading 3 ⏵ Получите 50 ₽ за каждый завершенный ордер */

.start-button-text{
position: absolute;
width: 398px;
height: 21px;
left: calc(50% - 398px/2);
top: calc(50% - 21px/2 + 0.5px);

/* main text-bold */
font-family: 'Roboto Flex';
font-style: normal;
font-weight: 600;
font-size: 18px;
line-height: 21px;
/* identical to box height */
display: flex;
align-items: center;

color: #FFFFFF;


}
.liquid {
 position: absolute;
width: 508.66px;
height: 603.41px;

opacity: 0.3;
filter: blur(4.5px);
transform: rotate(93.95deg); 
}

</style>