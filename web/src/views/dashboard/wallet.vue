<template>
<v-card style="margin-top: 52px;">
       <v-row >
       <v-col >
         
        <v-list v-model="selectedWallet" style="height: 100vh">
          <h3>БАЛАНС ФИНАНСОВОГО КОШЕЛЬКА</h3>
          <v-list-item v-for="(wall) in wallet">
            {{ wall.name }} {{ wall.balance }}
          </v-list-item>
          </v-list>
        </v-col>
      </v-row>
    </v-card>
</template>

<script>
import api from '../../api'

  export default {
  mounted () {
    api.get('api/wallet/list?wallettype=1', {
      headers: {
        'Authorization': localStorage.getItem('access_token')
      } // мой финасовый кошелек
    }).
      then(response => {
        //console.log('Получили данные кошелька')
        this.wallet = response.data
        localStorage.setItem('wallet', JSON.stringify(response.data))

        //console.log(this.wallet)
      }).catch(error => {
        //console.log(error)
      });
  },
  data: () => ({
      selectedItem: 0,
      selectedWallet: 1,
      wallet:[],
      })
}
</script>
<style scoped>
.v-list-item:hover {
  cursor: pointer;
}
</style>