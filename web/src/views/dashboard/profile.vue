<template>
  <v-card style="margin-top: 52px;">
   <v-tabs v-model="selectedSettings" height="50px" @click="getSettings">
    <v-col>
        <v-tab :value="1" color="success" density="compact">
        <p color="success">Редактировать профиль</p>
      </v-tab>
      <v-tab :value="2" color="success" density="compact">
        <p color="success">Настройки</p>
      </v-tab>
      <v-tab :value="3" color="success" density="compact">
        <p color="success">Мои сделки</p>
      </v-tab>
      <v-tab :value="4" color="success" density="compact">
        <p color="success">Реферальная система</p>
      </v-tab>
      <v-tab :value="5" color="success" density="compact">
        <p color="success">Юридические лица B2B</p>
      </v-tab>

      <v-tab :value="6" color="success" density="compact">
        <p color="success">Способы оплаты P2P</p>
      </v-tab>
      <v-tab :value="7" color="success" density="compact">
        <p color="success">Информация и помощь</p>
      </v-tab>
    </v-col>
    
    </v-tabs>
    <v-window v-model="selectedSettings">
      <v-window-item :value="1">
      <v-row>
      <v-col>
        <v-card>

          <v-form>
            <v-container>
              <v-row>
            

                <v-col
                  cols="12"
                  md="4"
                >
                  <v-text-field 
                  persistent-clear 
                  :placeholder=profileData.email 
                  density="compact"
                  prepend-inner-icon="mdi-email-outline"
                  variant="outlined"
                  label="Электронная почта"
                  v-model="selectedEmail"
                  disabled
                  ></v-text-field>
                </v-col>

                <v-col
                  cols="12"
                  md="4"
                >
                  <v-text-field
                  density="compact"
                  
                  variant="outlined"
                  :placeholder=profileData.login
                  label="Логин"
                  v-model="selectedLogin"
                  />
                </v-col>
                <v-col
                  cols="12"
                  md="6"
                >
                  <v-text-field
                    label="Телефон"
                    density="compact"
                  disabled
                  variant="outlined"
                  :placeholder=profileData.phone
                  v-model="selectedPhone"
                  />
                </v-col>
                <v-col
                  cols="12"
                  md="6"
                >
                  <v-text-field
                    label="Фамилия"
                    density="compact"
                  
                  variant="outlined"
                  :placeholder=profileData.last_name
                  v-model="selectedLastname"
                  />
                </v-col>
                
                <v-col
                  cols="12"
                  md="6"
                >
                  <v-text-field
                  density="compact"
                  
                  variant="outlined"
                  label="Имя"
                  
                  :placeholder=profileData.first_name
                  v-model="selectedFirstname"
                  />
                </v-col>

                <v-col
                  cols="12"
                  md="6"
                >
                  <v-text-field
                    label="Отчество"
                    density="compact"
                  
                  variant="outlined"
                  :placeholder=profileData.patronymic
                  v-model="selectedPatronymic"
                  />
                </v-col>

                
                <v-col
                  cols="12"
                  md="6"
                >
                  <v-text-field
                    label="Телеграм"
                    density="compact"
                  
                  variant="outlined"
                  :placeholder=profileData.telegram
                  v-model="selectedTelegram"
                  />
                </v-col>

                <!-- <v-col cols="12">
                  <v-textarea
                  density="compact"
                  variant="outlined"
                  label="Обо мне"
                  value=""
                  v-model="selectedDescription"
                  />
                </v-col> -->

                <v-col
                  cols="12"
                  class="text-right"
                >
                  <v-btn
                  color="success"
                  class="mr-0"
                  @click="updateProfile"
                  >
                  Обновить профиль
                  </v-btn>
                </v-col>
              </v-row>
            </v-container>
          </v-form>
        </v-card>
      </v-col>
    </v-row>
  </v-window-item>

  <v-window-item :value="5">
    
    <v-row class="ma-3" style="max-width: 400px">
    <v-col cols="12">
    <v-card>
      
      <div class ="d-flex">
      <v-checkbox
      v-model="selectedIP"
      ></v-checkbox> 
      
      <h5 class="ma-3">Юридическое лицо</h5>
      </div>
      <v-text-field v-if="selectedIP"
      
      type="number"
      label="ИНН организации"
      v-model="selectedInn"
      
      />
    
      <v-text-field v-else="selectedIP"
      
      type="number"
      label="ИНН организации"
      disabled
      />

      <v-btn color="success" @click="findCompany">Получить инфо</v-btn>

    </v-card>
    <v-card>
      <v-col
                  cols="12"
                  md="6"
                >
                  <v-text-field
                  density="compact"
                  
                  variant="outlined"
                  label="Название"
                  
                  :placeholder=company?.value
                  v-model="selectedValue"
                  />
                </v-col>
                
                
                <v-col
                  cols="12"
                  md="6"
                >
                  <v-text-field
                  density="compact"
                  
                  variant="outlined"
                  label="ОГРН"
                  
                  :placeholder=company?.data.ogrn
                  v-model="selectedOgrn"
                  />
                </v-col>
                <v-col
                  cols="12"
                  md="6"
                >
                  <v-text-field
                  density="compact"
                  
                  variant="outlined"
                  label="Адрес"
                  
                  :placeholder=company?.data.address.value
                  v-model="selectedAddress"
                  />
                </v-col>
                <v-col
                  cols="12"
                  md="6"
                >
                  <v-text-field
                  density="compact"
                  
                  variant="outlined"
                  label="ОКВЕД"
                  
                  :placeholder=company?.data.okved
                  v-model="selectedOkveds"
                  />
                </v-col>
                <v-btn color="success"
                @click ="createCompany"
                >
                  Создать компанию B2B
                </v-btn>
    </v-card>
    </v-col>
    
  </v-row>

  </v-window-item>

  <v-window-item :value="6">
     <!-- <v-select v-model="selectedPayment" :items="paymentsAll" label="Способы оплаты" item-title="name" item-value="id"
    @click="getPayments"
    density="compact"
    hide-details
    style="max-width: 25%; min-width: 190px;"
    > 
    
    </v-select>-->
    <v-select v-model="selectedUserPayment" :items="paymentsAll" label="Доступные реквизиты" item-title="name" item-value="id"
    @click="getPayments"
    density="compact"
    hide-details
    style="max-width: 50%; min-width: 190px;">

    </v-select>
    <v-col cols="12" md="6">
    <!-- <v-text-field label="Банк">
      {{this.userpayment.find(up => up.payment_id === this.selectedUserPayment) ? this.userpayment.find(up => up.payment_id === this.selectedUserPayment).name : '-' }}
    </v-text-field> -->
    
    <v-text-field 
    :placeholder="this.userpayment.find(up => up.payment_id === this.selectedUserPayment) ? this.userpayment.find(up => up.payment_id === this.selectedUserPayment).value : 'Реквизиты'" 
    density="compact" 
    variant="outlined" 
    type="number"
    v-model="createValue">
      
    </v-text-field>
    
    <v-text-field 
    :placeholder="this.userpayment.find(up => up.payment_id === this.selectedUserPayment) ? this.userpayment.find(up => up.payment_id === this.selectedUserPayment).payment_receiver : 'Получатель ФИО'" 
    density="compact" 
    variant="outlined" 
    v-model="createPaymentReceiver">
       
    </v-text-field>  

    <v-btn 
    color="success" 
    @click="editPayment">Сохранить изменения</v-btn>
    </v-col>
              
  </v-window-item>
  </v-window>








</v-card>


<v-dialog v-model="dialogChangeEmail" max-width="400px" style="opacity: 1; min-height:300px">
  <v-row style="width: 100%; justify-content: center; align-items: center;">
  <v-card>
    <v-row>
  
        <v-col>
          <h3 style="text-align: center;">Подтвердите изменение почты</h3>
        </v-col>
      </v-row>
      <v-divider></v-divider>
      
      <v-row>
        <v-col cols="12" class="d-flex">
          <v-btn color="error" style="width: 130px; margin: 10px" @click="cancelEmailChange">
            Отмена
          </v-btn>
          <v-col></v-col>
          <v-btn color="success" style="width: 130px; margin: 10px" @click="confirmEmailChange">
            Подтвердить
          </v-btn>
        </v-col>
        
      </v-row>
    
    </v-card>
  </v-row>
</v-dialog>

<!-- код -->
<v-dialog v-model="code" persistent width="auto" @keydown.esc="code = false; otp=''">

<v-card>
  <v-card-title class="text-h5" style="text-align: center;">
    Введите полученный код:
  </v-card-title>
  <v-otp-input ref="needfocus" v-model="otp" type="number" @finish="sendcode" :rules="[
    (value) => value.length === 6 || 'OTP should be 6 digits'
  ] "></v-otp-input>

</v-card>
</v-dialog>

</template>

<script>
import api from '../../api'
import { VOtpInput } from 'vuetify/labs/VOtpInput';


  export default {
    components: {
    VOtpInput
  },
    data: () => ({
    selectedItem: 0,
    drawer: null,
    dialogChangeEmail: false,
    otp: '',
    code: false,
    company: null,
    selectedSettings: localStorage.getItem('settings') == null || localStorage.getItem('settings') == "undefined" ? 5 : JSON.parse(localStorage.getItem('settings')),
    profileData:  localStorage.getItem('profile') == null || localStorage.getItem('profile') == "undefined" ? [] : JSON.parse(localStorage.getItem('profile')),
    paymentsAll: [],
    userpayment: [],
    selectedPayment: [],
    createPaymentReceiver: null,
    createValue: null,
    selectedUserPayment: [],
    createPayment: null,
    selectedIP: true,
    selectedInn: null,
    selectedValue: null,
    selectedOgrn: null,
    selectedAddress: null,
    selectedOkveds: null,
    selectedEmail: localStorage.getItem('profile') == null || localStorage.getItem('profile') == "undefined" ? null : JSON.parse(localStorage.getItem('profile')).email,
    selectedLogin: localStorage.getItem('profile') == null || localStorage.getItem('profile') == "undefined" ? null : JSON.parse(localStorage.getItem('profile')).login,
    selectedFirstname: localStorage.getItem('profile') == null || localStorage.getItem('profile') == "undefined" ? null : JSON.parse(localStorage.getItem('profile')).first_name,
    selectedLastname: localStorage.getItem('profile') == null || localStorage.getItem('profile') == "undefined" ? null : JSON.parse(localStorage.getItem('profile')).last_name,
    selectedPatronymic: localStorage.getItem('profile') == null || localStorage.getItem('profile') == "undefined" ? null : JSON.parse(localStorage.getItem('profile')).patronymic,
    selectedPhone: localStorage.getItem('profile') == null || localStorage.getItem('profile') == "undefined" ? null : JSON.parse(localStorage.getItem('profile')).phone,
    selectedTelegram: localStorage.getItem('profile') == null || localStorage.getItem('profile') == "undefined" ? null : JSON.parse(localStorage.getItem('profile')).telegram,
    }),
   
    computed : {
      isLoggedIn : function(){ 
        //console.log(this.$store.getters.isLoggedIn)
        return this.$store.getters.isLoggedIn
      },
    getSettings() {
      localStorage.setItem('settings', this.selectedSettings)
      //console.log(this.selectedSettings)
      },
    getPayments(){
      ////console.log(this.selectedPayment)  
      },
    
      
          

    },
    mounted() {


      

      api.get('api/payment/my-list', {
        headers: {
          'Authorization': localStorage.getItem('access_token')
        } // мои способы оплаты
      }).
        then(response => {
          //console.log('Получили данные по реквизитам пользователя')
          this.userpayment = response.data
          localStorage.setItem('userpayment', JSON.stringify(response.data))
          //console.log(this.userpayment)
  
          
          
        }).catch(error => {
          //console.log(error)
        });

        api.get('api/payment/list?currency_id=1', {
            headers: {
              'Authorization': localStorage.getItem('access_token')
            }
          }).
            then(response => {
              //console.log('Получили данные по доступным способам оплаты по валюте')
              this.paymentsAll = response.data
              
              //console.log(this.paymentsAll)
  
  
  
  
            }).catch(error => {
              //console.log(error)
            })
    if (localStorage.getItem('profileGetter') == 1)
    {

      this.profileData = JSON.parse(localStorage.getItem('profile'))
      //console.log(this.profileData)
      //console.log('Получили профиль пользователя из хранилища')

    }
    else {
    api.get('api/user/profile', {
        headers: {
        'Authorization': localStorage.getItem('access_token')
        } // добавьте ваш заголовок авторизации
      }).
      then(response =>{
      //console.log('Получили профиль пользователя')
      this.profileData = response.data
      localStorage.setItem('profile', JSON.stringify(this.profileData))
      localStorage.setItem('profileGetter', 1)
      //console.log(this.profileData)
    }).catch(error => {
      //console.log(error)
    });
  }
    
  },
    methods: {
      logout: function () {
        this.$store.dispatch('logout')
        .then(() => {
          this.$router.push('/login')
        })
      },
    editPayment() {
      if (this.createValue!==null || this.createPaymentReceiver !== null) {
        
        //console.log('сохранили изменения')
        //payment/update или payment/create
        let url = 'update'
        if (this.userpayment.find(up => up.payment_id === this.selectedUserPayment)) {
          //console.log('Обновление' + this.userpayment.find(up => up.payment_id === this.selectedUserPayment).payment_id) 
          var send = {
            id: this.userpayment.find(up => up.payment_id === this.selectedUserPayment).id,
            payment_id: this.selectedUserPayment,
            value: this.createValue,
            payment_receiver: this.createPaymentReceiver,
            
          }
        } else {
          //console.log('Добавление'  + this.paymentsAll.find(up => up.id === this.selectedUserPayment).id) 
          url = 'create'
          var send = {
            payment_id: this.selectedUserPayment,
            value: this.createValue,
            payment_receiver: this.createPaymentReceiver,
          }
          }
          
        

//        //console.log(send)
  
          api({
            method: 'post',
            url: '/api/payment/' + url,
            headers: {
              'Authorization': localStorage.getItem('access_token')
            },
            data: send
          })
            .then(function (response) {
              alert(response.data.message)
              //console.log(response);
  
            })
            .catch(function (error) {
  
              alert(error.response.data.message);
              //console.log(error);
  
            });
      
        }
    },
    updateProfile() {
      
      //console.log(this.selectedLogin)
      //console.log(this.selectedEmail)
      //console.log(this.selectedLastname)
      //console.log(this.selectedFirstname)
      //console.log(this.selectedPatronymic)
      //console.log(this.selectedTelegram)
      let flag = false
      ////console.log(JSON.parse(localStorage.getItem('profile')).email)
      if (this.selectedEmail === JSON.parse(localStorage.getItem('profile')).email) {
        //console.log('Изменений не проведено')
      } else {
        if (/^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/.test(this.selectedEmail) && this.selectedEmail !== null) 
            {
              ////console.log(this.registerData.emailregister + ' Успех')
              flag = true
              this.dialogChangeEmail = true
            } else {
              alert("Указана не корректная почта")
            }
      }
      
      if (this.selectedLogin.length < 5 || this.selectedLogin == null) {
        this.message = "Логин не соответствует минимальным требованиям"
        alert('не верные параметры логина')

      } else {
        //console.log("Валидация логина успешно")
        flag = true
      }

      if (flag == true) {
        //api
        var send = {
          login: this.selectedLogin,
          last_name: this.selectedLastname,
          first_name: this.selectedFirstname,
          patronymic: this.selectedPatronymic,
          telegram: this.selectedTelegram

        }
        api({
            method: 'post',
            url: '/api/user/update',
            headers: {
              'Authorization': localStorage.getItem('access_token')
            },
            data: send
          })
            .then(function (response) {
              //alert(response.data.message)
              //console.log(response.data);
              
  
            })
            .catch(function (error) {
  
              alert(error.response.data.message);
              //console.log(error.response.data.message);
  
            });

      } else {
        alert('Изменений не обнаружено')
      }
      

    },
    cancelEmailChange(){
      this.dialogChangeEmail = false
    },

    confirmEmailChange(){
      //this.dialogChangeEmail = false
      this.code = true
      //api changeemail
    },
    sendcode() {
      ////console.log(this.otp)
      //api confirm-email-change только с 2fa
      this.code = false
      
      // let code = this.otp      
      // if (this.selectedEmail) {
      //   let email = this.selectedEmail
      //   //let password = this.registerData.password1
      //   this.$store.dispatch('code', { email, password, code})
      //  .then(() => this.$router.push('/home'))
      //  .catch(err => //console.log(err))
      // }
      
      
    },
    findCompany(){
      //api
      //console.log(this.selectedInn)
      // api.get('api/company/find?inn=' + this.selectedInn, {
      //       headers: {
      //         'Authorization': localStorage.getItem('access_token')
      //       }
      //     }).
      //       then(response => {
      //         //console.log('Получили данные по компании')
      //         this.company = response.data
      //         //console.log(this.company)

      //       }).catch(error => {
      //         //console.log(error)
      //       })
      
      
      var url = "https://suggestions.dadata.ru/suggestions/api/4_1/rs/findById/party";
      var token = "add0edb65bd1fb92dc50a3da6f904e29d57f4775";
      var query = this.selectedInn;

      var options = {
          method: "POST",
          mode: "cors",
          headers: {
              "Content-Type": "application/json",
              "Accept": "application/json",
              "Authorization": "Token " + token
          },
          body: JSON.stringify({query: query})
      }

      fetch(url, options)
      .then(response => response.text())
      .then(result => {
        this.company = JSON.parse(result)?.suggestions
        console.log(this.company)
        
        this.company = this.company[0]
        //console.log(this.company)
        this.selectedValue = this.company.value
        this.selectedOgrn = this.company.data.ogrn
        this.selectedAddress = this.company.data.address.value
        this.selectedOkveds = this.company.data.okved
        console.log(this.selectedOkveds)
        }
      )
      .catch(error => console.log("error", error));
          
    
    },
    createCompany2(){
      api.get('api/company/craate', {
            headers: {
              'Authorization': localStorage.getItem('access_token')
            }
          }).
            then(response => {
              console.log('Получили данные')
              this.paymentsAll = response.data
              console.log(this.paymentsAll)
  
  
  
  
            }).catch(error => {
              console.log(error)
            })
    },

    createCompany(){
      //api
      var send = {
        name: this.selectedValue,
        inn: this.selectedInn,
        ogrn: this.selectedOgrn,
        address: this.selectedAddress,
        main_okved: this.selectedOkveds,
        kpp: this.selectedOkveds,
        fio: this.selectedOkveds,
        phone: '79059051122',
        bank: this.selectedOgrn,
        bik: '047047047',
        rs: this.selectedOgrn,
        ks: this.selectedOgrn

      }
      if (this.selectedInn == null) {
        alert('Компания не указана или не выбран ИНН организации')
      } else {
        api({
            method: 'post',
            url: '/api/company/create',
            headers: {
              'Authorization': localStorage.getItem('access_token')
            },
            data: send
          })
            .then(function (response) {
              alert(response.data.message)
              //console.log(response);
              
  
            })
            .catch(function (error) {
  
              alert(error.response.data.message);
              console.log(error);
  
            });
      }
    },
    
  }
  
  }
</script>
<style scoped>
.v-list-item:hover {
  cursor: pointer;
}
</style>





