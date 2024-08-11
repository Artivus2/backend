<template>)
  <v-app-bar class="d-none d-md-flex d-lg-flex d-xl-flex"
  
  >
    
    <v-img
          width="40px"
         style="position: absolute; left: 10%; top: 11px;"
          src="../../assets/greenavi-logo2.png"
          >
          </v-img>  
          <div 
         class="greenavi"
         style="position: absolute; left: 12%; top: 20px;"
         >GREENAVI</div> 
   <div class="menu">
      <v-list-item class="menu-button2 menu-text2" to="" link>
         Купить криптовалюту
      </v-list-item>      
      
      <v-list-item class="menu-button2 menu-text2" to="/p2p" link>
            P2P Торговля
            <template v-slot:append>
              <v-btn
                size="small"
                variant="text"
                icon="mdi-chevron-down"
                color="#2666FF"
              ></v-btn>
              </template>
      </v-list-item>
      <v-list-item class="menu-button2 menu-text2" to="/b2b" link>
            B2B
            <div class="menu-button3 menu-text3">
               New
            </div>
      </v-list-item>
      
      <v-list-item class="menu-button2 menu-text2" to="" link>      
            Новости
            <template v-slot:append>
              <v-btn
                size="small"
                variant="text"
                icon="mdi-chevron-down"
                color="#2666FF"
              ></v-btn>
              </template>
      </v-list-item>
      <v-list-item class="menu-button2 menu-text2" to="" link>
            Поддержка
            <template v-slot:append>
              <v-btn
                size="small"
                variant="text"
                icon="mdi-chevron-down"
                color="#2666FF"
              ></v-btn>
              </template>
      </v-list-item>

      
   </div>
   <div class="auto-layout" v-if="isLoggedIn">
      <v-list-item class="login text-login" to="/profile" link>
          Профиль
      </v-list-item>
        
      <v-list-item class="login text-login" @click.stop="logout">
      
          Выйти
        </v-list-item>  
        
   </div>
   <div class="auto-layout" v-else>
         <div class="login" type="submit" @submit.prevent="false" @click.stop="dialogLogin = true" >
          <div class="text-login">
          Войти      
          </div>
        </div>
        <div class="register" type="submit" @submit.prevent="false" @click="register = true" >
          <div class="text-register">
            Регистрация
          </div>
        </div>
   </div>
   <div class='block'><p class="price-run">{{charts.symbol}}  {{ charts.price }}</p>
   </div>  
  
   </v-app-bar>
   <v-app-bar class="d-flex d-sm-none"
   style="top: 0px"
   >
   <v-img
         width="40px"
         style="position: absolute; left: 2%; top: 11px;"
         src="../../assets/greenavi-logo2.png"
         >
         </v-img> 
         <div 
         class="greenavi"
         style="position: absolute; left: 12%; top: 22px;"
         >GREENAVI</div> 
         <v-app-bar-nav-icon class="humburger"
         style="position: absolute; right: 2%; top: 10px; "
         color="#2666FF"
         @click.stop="drawer = !drawer"
         ></v-app-bar-nav-icon>

         
        </v-app-bar>
        <v-navigation-drawer
        v-model="drawer"
        location="top"
        style="top: 65px; min-height: 400px;"
        temporary
        class="d-flex d-sm-none"
      >
       <v-list>
         <v-list-item  style="cursor: pointer;" to="/" link>
            <v-icon icon="mdi-monitor-dashboard"></v-icon> Главная
         </v-list-item>
         <v-list-item  style="cursor: pointer;" to="/" link>
            <v-icon icon="mdi-view-dashboard"></v-icon> Купить криптовалюту
         </v-list-item>
         <v-list-item  style="cursor: pointer;" to="/" link>
            <v-icon icon="mdi-view-dashboard-outline"></v-icon> Новости
         </v-list-item>
         <v-list-item  style="cursor: pointer;" to="/" link>
            <v-icon icon="mdi-view-dashboard-variant"></v-icon> Поддержка
         </v-list-item>
         <v-divider></v-divider>
         <v-list-item  style="cursor: pointer;" @click.stop="dialogLogin = true">
            <v-icon icon="mdi-login"></v-icon> Войти
         </v-list-item>
         <v-list-item style="cursor: pointer;" @click.stop="register = true">
            <v-icon icon="mdi-account-plus"></v-icon> Зарегистрироваться
         </v-list-item>
        </v-list>
      </v-navigation-drawer>
   

<!--авторизация-->

<v-form ref="form" @submit.prevent="submitForm">
<v-dialog v-model="dialogLogin">
   <div class="login-layout">
      <div class="login1">
         <div class="login-inside">Авторизация</div>
         <div class="login-inside"><img @click="dialogLogin = false" style="cursor: pointer;" src="../../assets/iconoir_cancel.svg"/></div>
      </div>
      <div class="login2 login2-inside">
      
         Для пользования площадкой необходимо зарегистрировать отдельный аккаунт на GREENAVI.COM
      
      </div>
      <div>
      <input class="text-input1" type="email" placeholder="Электронная почта" v-model="loginData.email">
               
      </div>
      <div>
      <input class="input-password" type="password" placeholder="Пароль" v-model="loginData.password"/>
      
      
      </div>
      <div class="frame73">
         <div class="frame71">
            <input class="component8" type="checkbox" v-model="rememberMe"/>
            <div class="text1">Запомнить меня</div>
         </div>
         <div  class="frame75">
            <div class="text2">Забыли пароль?</div>
         </div>
      </div>
      
      <v-btn class="login-button" type="submit" @click="validateLogin">Авторизоваться</v-btn>
      <div  class="frame75 frame-register" @click="register = true; dialogLogin = false;">
            <div class="text2">Регистрация</div>
         </div>
      
   </div>
</v-dialog>
</v-form>


            <!--рега-->
<v-form ref="formreg" @submit.prevent="submitRegister">
<v-dialog v-model="register">
   <div class="login-layout">
      <div class="login1">
         <div class="login-inside">Регистрация</div>
         <div class="login-inside"><img @click="register = false" style="cursor: pointer;" src="../../assets/iconoir_cancel.svg"/></div>
      </div>
      <input class="text-input1" type="email" placeholder="Логин" v-model="registerData.loginregister">
      <input class="text-input1" type="email" placeholder="Электронная почта" v-model="registerData.emailregister">
      <input class="input-password" type="password" placeholder="Пароль" v-model="registerData.password1"/>
      <div class="frame73">
         <div class="frame71">
            <input class="component8" type="checkbox" v-model="registerData.password2"/>
            <div class="text1">Сгенерировать надежный пароль</div>
         </div>     
      </div>      
      <v-btn class="login-button" type="submit" @click="validateRegister">Зарегистрироваться</v-btn>
      <div  class="frame75 frame-register" style="cursor: pointer;" @click="register = false; dialogLogin = true;">
            <div class="text2">Авторизация</div>
         </div>
   </div>
</v-dialog>
</v-form>


 


<!-- код -->
 <v-dialog v-model="code" persistent width="auto" @keydown.esc="code = false; otp=''">
   <div class="frame419-layout">
      <div class="frame419">
            <div class="frame74-text2">Подтверждение e-mail</div>
            <div class=""><img @click="code = false" style="cursor: pointer;" src="../../assets/iconoir_cancel.svg"/></div>
      </div>
  <div class="d-none d-sm-flex svgemail">
            <svg width="140" height="140" viewBox="0 0 140 140" fill="none" xmlns="http://www.w3.org/2000/svg">
         <g clip-path="url(#clip0_301_7914)">
         <path fill-rule="evenodd" clip-rule="evenodd" d="M22.3011 60.8854C31.4145 55.057 43.159 54.849 53.7356 57.1207C62.474 58.9976 67.0412 68.3416 75.0917 72.2239C84.4137 76.7193 97.2047 73.518 103.973 81.3472C111.19 89.6945 114.142 102.643 110.347 113.004C106.628 123.16 94.8565 127.295 85.2946 132.349C77.8299 136.295 69.8742 139.474 61.4543 138.846C53.872 138.282 47.914 133.101 41.4587 129.084C35.1801 125.176 29.4722 121.15 24.3886 115.779C16.4613 107.404 4.09616 100.671 3.65795 89.1474C3.21833 77.5869 12.555 67.1184 22.3011 60.8854Z" fill="#2666FF" fill-opacity="0.15"/>
         <path fill-rule="evenodd" clip-rule="evenodd" d="M50.3363 20.5191C59.4496 14.6907 71.1942 14.4827 81.7707 16.7544C90.5092 18.6312 95.0763 27.9753 103.127 31.8575C112.449 36.353 125.24 33.1517 132.008 40.9809C139.225 49.3281 142.177 62.2768 138.382 72.638C134.663 82.7937 122.892 86.9289 113.33 91.9828C105.865 95.9282 97.9093 99.1073 89.4894 98.4801C81.9072 97.9153 75.9491 92.7347 69.4939 88.7173C63.2153 84.8099 57.5074 80.7834 52.4237 75.4126C44.4965 67.0375 32.1313 60.3046 31.6931 48.781C31.2535 37.2206 40.5901 26.7521 50.3363 20.5191Z" fill="#2666FF" fill-opacity="0.15"/>
         <path d="M16 38L43 58L66.4286 75.3545C68.5502 76.9261 71.4498 76.9261 73.5714 75.3545L97 58L124 38V102H16V38Z" fill="#2666FF" fill-opacity="0.6"/>
         <path d="M124 38H16L43 58L66.4286 75.3545C68.5502 76.9261 71.4498 76.9261 73.5714 75.3545L97 58L124 38Z" fill="#2666FF" fill-opacity="0.6"/>
         <path d="M16 38V102M16 38L43 58M16 38H104M124 102H16M124 102L97 58M124 102V58M16 102L43 58M43 58L66.4286 75.3545C68.5502 76.9261 71.4498 76.9261 73.5714 75.3545L97 58M97 58L107.8 50" stroke="white" stroke-width="2" stroke-linejoin="round"/>
         <circle cx="124" cy="38" r="15" fill="#01142C" stroke="white" stroke-width="2"/>
         <path fill-rule="evenodd" clip-rule="evenodd" d="M136.548 38L116.938 28.8486L121.899 38L116.938 47.1514L136.548 38Z" fill="white"/>
         <line x1="3.84766" y1="109.952" x2="28.7275" y2="109.952" stroke="white" stroke-width="2"/>
         <line y1="22.9297" x2="24.8799" y2="22.9297" stroke="white" stroke-width="2"/>
         <line y1="115.07" x2="57.3535" y2="115.07" stroke="white" stroke-width="2"/>
         <line x1="3.84766" y1="28.0486" x2="38.0508" y2="28.0486" stroke="white" stroke-width="2"/>
         </g>
         <defs>
         <clipPath id="clip0_301_7914">
         <rect width="140" height="140" fill="white"/>
         </clipPath>
         </defs>
         </svg>
   </div>
   <div class="text-frame419">
      На указанный адрес электронной почты отправлено письмо. Введите 6-тизначный код из письма для подтверждения вашей почты
   </div>

    <v-otp-input ref="needfocus" v-model="otp" type="number" @finish="sendcode" :rules="[
      (value) => value.length === 6 || 'OTP should be 6 digits'
    ] "
    variant="underlined"
    style="order: 3"
    ></v-otp-input>


<div class="frame74">
   <div class="frame74-text" style="cursor: pointer;">
      Письмо не пришло. Повторить отправку
   </div>
</div>

  </div>
</v-dialog>




</template>

<script>
import axios from 'axios'
import { VOtpInput } from 'vuetify/labs/VOtpInput';
import api from '../../api'
export default{
   computed : {
    isLoggedIn : function(){ 
      //console.log(this.$store.getters.isLoggedIn)
      return this.$store.getters.isLoggedIn
    },
  },
   components: {
      VOtpInput,
    },
   mounted(){
      axios.get('https://api.binance.com/api/v3/ticker/price?symbol=BTCUSDT').
        then(response => {
          //console.log('Получили данные по доступным криптовалютам')
          this.charts = response.data
          //console.log(this.charts)
          //localStorage.setItem('p2p_data', JSON.stringify(response.data))
  
          ////console.log(this.charts)
        }).catch(error => {
          //console.log(error)
        })

      
    this.$store.dispatch('getProfile')
      .then((response) => {
      this.profileData = response.data
      localStorage.setItem('profileGetter', 1)
      localStorage.setItem('profile', JSON.stringify(this.profileData))
      })
      .catch(err => console.log(err))
  
   },
   data() {
      return {
        otp: '',
        type: 1,
        code: false,
        // darkMode: false,
        // toggleTheme() {
        //   this.theme.global.name.value = darkMode.value ? "dark" : "light"
        // },
        emailforgot: '',
        visible: false,
        visiblereg: false,
        drawer: false,
        forgot: false,
        rememberMe: false,
        register: false,
        registerIP: false,
        loading: false,
        dialogLogin: false,
        profileData: null,
        charts: [],
        errormes: false,
        message: '',
        loginData: [
          {
            email: null,
            phone: null,
            password: null,
            rememberMe: true
          }
        ],
        registerData: [
          {
            Inn: null,
            emailregister: null,
            password1: null,
            password2: false,
            login: null,
            referal: null,
            checkbox: null
          }
        ],
        items: [
        {icon: 'mdi-bank', text: 'Купить криптовалюту', route: '/dashboard/trade'},
        {icon: 'mdi-swap-horizontal', text: 'P2P Торговля', route: '/p2p'},
        {icon: 'mdi-arrow-left-right-bold-outline', text: 'B2B', route: '/dashboard/b2b'},
        {icon: 'mdi-wallet', text: 'Процессинг', route: ''},
        {icon: 'mdi-human', text: 'Статьи', route: ''},
        {icon: 'mdi-human', text: 'Поддежка', route: ''},
        
        
    ]
      }
   },
   methods: {
      logout: function () {
      this.$store.dispatch('logout')
      .then(() => {
        this.$router.push('/')
      })
    },
      submitForm() {
        if (this.loginData.email.length >= 5 && this.loginData.password.length >= 5) {
          if (this.$refs.form.validate()) {
            this.otp=''
            
  
          }
        } else { return "Не верный логин" }
      },
      submitRegister() {
  
      },
      sendcode() {
        ////console.log(this.otp)
        this.code = false
        
        let code = this.otp      
        if (this.register) {
          let path = '/'  
          let email = this.registerData.emailregister
          let password = this.registerData.password1
          this.$store.dispatch('code', { email, password, code})
         .then(() => this.$router.push('/'))
         .catch(err => console.log(err))
        } else {
          let path = '/'
          let email = this.loginData.email
          let password = this.loginData.password
          this.$store.dispatch('code', { email, password, code})
         .then(() => {
            this.$router.push(path)
            this.dialogLogin = false
            alert('Успешный вход')
         })
         .catch(err => console.log(err))
        }
        
        ////console.log(this.register + 'Рега или логин')
        
      
        
      },
      // validateForgot() {
      //   if (/^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/.test(this.emailforgot)) {
      //     ////console.log(this.emailforgot)
      //     this.forgot = false
      //     api.post('api/user/change-password', {
      //       email: this.emailforgot,
      //       login: this.registerData.login,
      //       password: this.registerData.password1,
      //       referal: this.registerData.referal 
      //     }).
      //   then(response =>{
      //     ////console.log(response.data)
      //     alert('На вашу почту отправлены данные для восстановления')
      //     this.code = true
      //     this.otp = ''
      //   }).catch(error => {
      //     ////console.log(error)
      //     alert('Указанная вами почта отсутствует')
      //   });
  
  
      //   }
      // },
      validateRegister() {
        
        let flag = false
        this.errormes = true
        this.message = '' 
        
        
        if (/^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/.test(this.registerData.emailregister) && this.registerData.emailregister !== null) 
        {
          ////console.log(this.registerData.emailregister + ' Успех')
          flag = true
        } else {
          flag = false
          
          this.message = "Указана не корректная почта"
        }
  
        if (this.registerData.login.length < 5 || this.registerData.login == null && flag) {
          this.message = "Логин не соответствует минимальным требованиям"
          //добавить рег для логина и валится на длине
          flag = false
        } else {
          ////console.log("Валидация логина успешно")
        }
        
        ////console.log(this.registerData.password1 + ' : ' + this.registerData.password2)
        // if (this.registerData.password1 == this.registerData.password2 && this.registerData.password1.length >= 5 && flag) {
        if (this.registerData.password1.length >= 5 && flag) {
          ////console.log("Валидация пароля успешно")
          flag = true
        } else {
          this.message = "Пароли не соответствуют требованиям (не менее 5 символов или цифр)"
          flag = false
        }
  
        
        // if (this.registerData.checkbox == false || this.registerData.checkbox == null && flag) {
        //   this.message = "Необходимо принять соглашение"
        //   flag = false
        // } else {
        //   ////console.log("Соглашение принято")
        // }
      
  
        if (flag) {
          ////console.log("Отправка данных на сервер" + this.registerData.emailregister + this.registerData.password1)
          
          api.post('api/user/register', {
            email: this.registerData.emailregister,
            login: this.registerData.loginregister,
            password: this.registerData.password1,
            referal: this.registerData.referal 
          }).
        then(response =>{
          ////console.log(response.data)
          
          this.code = true
          this.otp = ''
        }).catch(error => {
          //console.log(error)
          alert('Не получилось зарегистрироваться')
        });
          //axios send code
        } else {
          this.message = "Данные указаны некорректно"
        }
  
  
        
      },
      validateLogin() {
        //const regex = /^[a-zA-Z0-9]+$/
        
        if (/^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/.test(this.loginData.email) && this.loginData.password !== null) {
         //console.log('Логинимся')  
          console.log(this.loginData.email)
          api.post('api/user/login', {
            email: this.loginData.email,
            phone: null,
            password: this.loginData.password,
            rememberMe: this.loginData.rememberMe
          }).
        then(response =>{
          console.log(response.data)
          this.code = true
        }).catch(error => {
          console.log(error)
          alert('Не верные данные')
        });
  
  
        } else {
          alert("Данные указаны некорректно")
          ////console.log('Должен быть указан валидный E-MAIL' + this.loginData.email)
        }
      },
      required(v) {
  
        return !!v || 'Поле обязательно для заполнения'
      },
    },
}

</script>
<style scoped>
/* Frame 427 */

/* Auto layout */
.auto-layout {
display: flex;
flex-direction: row;
align-items: flex-start;
padding: 0px;
gap: 10px;
position: absolute;
right: 245px;
top: 11px;

width: 245px;
height: 37px;



filter: drop-shadow(0px 4px 4px rgba(0, 0, 0, 0.25));
}

/* Button */
.login {
box-sizing: border-box;


/* Auto layout */
display: flex;
flex-direction: row;
justify-content: center;
align-items: center;
padding: 9px 16px;
width: 110px;
height: 37px;
border: 1px solid #2666FF;
border-radius: 8px;

/* Inside auto layout */


}

.login:hover {
  background: #02309F;
}
.register:hover {
  background: #02309F;
}

/* Войти */
.text-login {

color: rgba(255, 255, 255, 0.8);


/* Inside auto layout */

}

/* Button */

.register{
box-sizing: border-box;


/* Auto layout */
display: flex;
flex-direction: row;
justify-content: center;
align-items: center;
padding: 9px 16px;

width: 125px;
height: 37px;

background: rgba(2, 48, 159, 0.15);
border: 1px solid #2666FF;
border-radius: 8px;

/* Inside auto layout */
flex: none;
order: 1;
flex-grow: 0;
}

/* Регистрация */
.text-register {
width: 93px;
height: 19px;


font-style: normal;
font-weight: 400;
font-size: 16px;
line-height: 19px;
/* identical to box height */
display: flex;
align-items: center;

color: rgba(255, 255, 255, 0.8);


/* Inside auto layout */
flex: none;
order: 0;
flex-grow: 0;
}








.body{
  background-color: #01142C;
}

/* Header */
.header {
position: absolute;
width: 1440px;
height: 59px;
left: 240px;
top: 0px;
background-color: #01142C;
}


.menu-layout {
/* menu */

/* Auto layout */
display: flex;
flex-direction: row;
align-items: flex-start;
padding: 0px;
gap: 30px;

position: absolute;
width: 749px;
height: 20px;
left: calc(50% - 749px/2 + 0.5px);
top: 20px;
}




/* GREEnavi */
.greenavi{
font-family: 'Playfair Display';
font-style: normal;
font-weight: 600;
font-size: 20px;
line-height: 27px;
/* identical to box height */
letter-spacing: 0.02em;

color: #1BAB84;
}





/* menu */
.menu{
/* Auto layout */
display: flex;
flex-direction: row;
align-items: flex-start;
padding: 0px;
gap: 30px;

position: absolute;
width: 749px;
height: 20px;
left: calc(50% - 749px/2 + 0.5px);
top: 10px;
}


/* menu-button */

/* Auto layout */
.menu-button1 {
display: flex;
flex-direction: row;
align-items: flex-start;
padding: 0px;
gap: 4px;

width: 187px;
height: 20px;


/* Inside auto layout */
flex: none;
order: 0;
flex-grow: 0;

}
/* Heading 2 ⏵ Купить криптовалюту */

.menu-text1 {
width: 153px;
height: 19px;

font-family: 'Roboto Flex';
font-style: normal;
font-weight: 500;
font-size: 16px;
line-height: 19px;
/* identical to box height */
display: flex;
align-items: center;

color: rgba(233, 240, 255, 0.8);
}



.arrow-down {
/* iconamoon:arrow-down-2-thin */

position: absolute;



/* Inside auto layout */
/* flex: none;
order: 1;
flex-grow: 0; */


/* Vector */


/* left: calc(50% - 11px/2 - 0.5px);
top: calc(50% - 5px/2 - 0.5px); */

/* border: 1px solid #2666FF; */
}

/* p2p-торговля */

/* Auto layout */
.menu-button2 {
display: flex;
flex-direction: row;
align-items: flex-start;
padding: 0px;
isolation: isolate;
cursor: pointer;



/* Inside auto layout */
flex: none;
order: 1;
flex-grow: 0;
}

/* P2P-торговля */

.menu-text2 {

font-family: 'Roboto Flex';
font-style: normal;
font-weight: 500;
font-size: 16px;
line-height: 19px;
/* identical to box height */
display: flex;
align-items: center;

color: rgba(233, 240, 255, 0.8);


/* Inside auto layout */

}

/* pseudo */
.menu-button3 {
box-sizing: border-box;


top: -9px;

/* stroke-green */
border: 1px solid #64CB8C;
border-radius: 8px;

/* Inside auto layout */
flex: none;
order: 1;
flex-grow: 0;
z-index: 1;

}
/* new */
.menu-text3{
position: absolute;
width: 24px;
height: 14px;
left: 24px;
top: 6px;

font-family: 'Roboto Flex';
font-style: normal;
font-weight: 400;
font-size: 8px;
line-height: 9px;
display: flex;
align-items: center;
text-transform: uppercase;

/* stroke-green */
color: #64CB8C;

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

/* Бегущая строка */


.text-run{
/* Bitcoin */

width: 30px;
height: 30px;

position: absolute;
left: 0%;
right: 0.01%;
top: 0.01%;
bottom: -0.01%;



background: #F7931A;



/* Bitcoin (BTC) */

width: 84px;
height: 17px;

/* small-text-bold */
font-family: 'Roboto';
font-style: normal;
font-weight: 600;
font-size: 14px;
line-height: 120%;
/* identical to box height, or 17px */

color: #E9F0FF;

}

/* $ 00.000,00 */

.price-run{
width: 73px;
height: 17px;

/* small text */
font-family: 'Roboto';
font-style: normal;
font-weight: 400;
font-size: 14px;
line-height: 120%;
/* identical to box height, or 17px */

color: #E9F0FF;
}

.coursedown-run{
/* red */
border: 1.5px solid #D34176;


/* -00.00% */

width: 50px;
height: 17px;

/* small text */
font-family: 'Roboto';
font-style: normal;
font-weight: 400;
font-size: 14px;
line-height: 120%;
/* identical to box height, or 17px */

/* red */
color: #D34176;

}






/* Rectangle 28 */
.rectangle1-run{
position: absolute;
width: 20px;
height: 60px;
left: 238px;
top: 0px;

background: linear-gradient(90deg, #01142C 0%, rgba(5, 0, 34, 0) 100%);

}
/* Rectangle 29 */
.rectangle2-run{
position: absolute;
width: 20px;
height: 60px;
right: 208px;
top: 0px;

background: linear-gradient(90deg, #01142C 0%, rgba(5, 0, 34, 0) 100%);
transform: matrix(-1, 0, 0, 1, 0, 0);
}


/* Vector */
.courseup-run {
position: absolute;
left: 14.59%;
right: 14.58%;
top: 12.5%;
bottom: 12.5%;

/* stroke-green */
border: 1.5px solid #64CB8C;

}


/* iconamoon:menu-burger-horizontal-thin */
.hamburger {
position: absolute;
width: 40px;
height: 40px;
left: 288px;
top: 3px;



/* Vector */

position: absolute;
left: 12.5%;
right: 12.5%;
top: 25%;
bottom: 25%;


}



















/* Авторизация */

/* Auto layout */
.login-layout {
display: flex;
flex-direction: column;

align-items: center;
padding: 20px;
gap: 20px;

position: absolute;
width: 400px;
height: 423px;
left: calc(50% - 400px/2);
top: calc(50% - 423px/2 - 0.5px);

background: #01142C;
border: 2px solid #2666FF;
border-radius: 20px;
}

/* Frame 72 */

/* Auto layout */
.login1 {
display: flex;
flex-direction: row;
justify-content: space-between;
align-items: center;
padding: 0px;


width: 600px;
height: 40px;

flex: none;
order: 0;

flex-grow: 0;
}

/* Heading 3 ⏵ Авторизация */
.login-inside{

font-family: 'Roboto Slab';
font-style: normal;
font-weight: 700;
font-size: 24px;
line-height: 32px;
/* identical to box height */
display: flex;
align-items: center;

color: #FFFFFF;


/* iconoir:cancel */

margin: 0 auto;
width: 40px;
height: 40px;


/* border: 2px solid #2666FF; */
}

/* p.SignInModal_warning__wAYqT */
.login2{
box-sizing: border-box;

/* Auto layout */
display: flex;
flex-direction: column;
justify-content: center;
align-items: center;
padding: 6px;
gap: 10px;
max-width: 360px;

height: 63px;

background: rgba(245, 155, 19, 0.1);
border: 1px solid #F59B13;
border-radius: 4px;

/* Inside auto layout */
flex: none;
order: 1;

flex-grow: 0;

width: 365px;
height: 51px;

font-family: 'Roboto Slab';
font-style: normal;
font-weight: 400;
font-size: 14px;
line-height: 120%;
/* or 17px */
display: flex;
align-items: center;
text-align: center;

color: #F59B13;


/* Inside auto layout */
flex: none;
order: 0;
flex-grow: 0;
}

/* Форма ввода текста */
.text-input1 {
box-sizing: border-box;

/* Auto layout */
display: flex;
flex-direction: row;
align-items: flex-start;
padding: 10px;

width: 360px;
height: 41px;

border: 1px solid #2666FF;
border-radius: 6px;

/* Inside auto layout */
flex: none;
order: 2;
align-self: stretch;
flex-grow: 0;
}

.placeholder {

/* Электронная почта */

width: 160px;
height: 21px;

font-family: 'Roboto Slab';
font-style: normal;
font-weight: 400;
font-size: 16px;
line-height: 21px;
display: flex;
align-items: center;

color: rgba(255, 255, 255, 0.6);


/* Inside auto layout */
flex: none;
order: 0;
flex-grow: 0;


/* iconamoon:arrow-down-2-thin */

display: none;
width: 20px;
height: 20px;


/* Inside auto layout */
flex: none;
order: 1;
flex-grow: 0;

}
/* Форма ввода пароля */
.input-password {
box-sizing: border-box;

/* Auto layout */
display: flex;
flex-direction: row;
justify-content: space-between;
align-items: flex-start;
padding: 10px;

width: 360px;
height: 41px;

border: 1px solid #2666FF;
border-radius: 6px;

/* Inside auto layout */
flex: none;
order: 3;
align-self: stretch;
flex-grow: 0;

}
/* Пароль */

.text-input2 {
margin: 0 auto;
width: 60px;
height: 21px;

font-family: 'Roboto Slab';
font-style: normal;
font-weight: 400;
font-size: 16px;
line-height: 21px;
display: flex;
align-items: center;

color: rgba(255, 255, 255, 0.6);


/* Inside auto layout */
flex: none;
order: 0;
flex-grow: 0;


/* iconoir:eye-close */

margin: 0 auto;
width: 20px;
height: 20px;


/* Inside auto layout */
flex: none;
order: 1;
flex-grow: 0;


/* Vector */

position: absolute;
left: 12.5%;
right: 12.5%;
top: 33.33%;
bottom: 27.08%;

border: 2px solid #2666FF;
}










/* Frame 73 */
.frame73 {
/* Auto layout */
display: flex;
flex-direction: row;
justify-content: space-between;
align-items: center;
padding: 0px;
gap: 20px;

width: 360px;
height: 20px;


/* Inside auto layout */
flex: none;
order: 4;
align-self: stretch;
flex-grow: 0;

}

/* Frame 71 */
.frame71 {
/* Auto layout */
display: flex;
flex-direction: row;
align-items: center;
padding: 0px;
gap: 4px;

margin: 0 auto;
width: 134.08px;
height: 20px;


/* Inside auto layout */
flex: none;
order: 0;
flex-grow: 0;
}


.component8{
/* Component 8 */

box-sizing: border-box;

width: 20px;
height: 20px;

background: rgba(91, 19, 245, 0.1);
border: 1px solid #2666FF;
border-radius: 4px;

/* Inside auto layout */
flex: none;
order: 0;
flex-grow: 0;
}

.text1{

/* Запомнить меня */

width: 110.08px;
height: 16px;

font-family: 'Roboto Slab';
font-style: normal;
font-weight: 400;
font-size: 12px;
line-height: 120%;
/* or 14px */
display: flex;
align-items: center;

color: rgba(255, 255, 255, 0.6);


/* Inside auto layout */
flex: none;
order: 1;
flex-grow: 0;

}

/* Frame 75 */
.frame75{
/* Auto layout */
display: flex;
flex-direction: row;
justify-content: space-between;
align-items: center;
padding: 0px;
gap: 10px;

margin: 0 auto;
width: 110px;
height: 16px;


/* Inside auto layout */
flex: none;
order: 1;
flex-grow: 0;

}

.text2 {
/* Забыли пароль? */

margin: 0 auto;
height: 16px;

font-family: 'Roboto Slab';
font-style: normal;
font-weight: 400;
font-size: 12px;
line-height: 120%;
/* or 14px */
display: flex;
align-items: center;
text-align: right;

color: #2666FF;
cursor: pointer;


/* Inside auto layout */
flex: none;
order: 0;
flex-grow: 0;
}






.login-button{
/* Button */

/* Auto layout */
display: flex;
flex-direction: row;
justify-content: center;
align-items: center;
padding: 10px 20px;
gap: 10px;

width: 260px;
height: 40px;

background: #2666FF;
border-radius: 8px;

/* Inside auto layout */
flex: none;
order: 5;
flex-grow: 0;
}

.frame-register {
   order: 6;
   cursor: pointer;
}


.frame419-layout {
   display: flex;
flex-direction: column;

align-items: center;
padding: 20px;


position: absolute;
width: 400px;
height: 640px;
left: calc(50% - 400px/2);
top: calc(50% - 640px/2 - 0.5px);

background: #01142C;
border: 2px solid #2666FF;
border-radius: 20px;
}

.svgemail {
   order: 2;
}

.frame419 {
   margin: 0 auto;
max-width: 360px;
height: 32px;

font-family: 'Roboto Slab';
font-style: normal;
font-weight: 700;
font-size: 24px;
line-height: 32px;
/* identical to box height */
display: flex;
align-items: center;

color: #FFFFFF;


/* Inside auto layout */
flex: none;
order: 2;
flex-grow: 0;
}



/* На указанный адрес электронной почты отправлено письмо. Введите 6-тизначный код из письма для подтверждения вашей почты */
.text-frame419{
width: 360px;
height: 104px;

font-family: 'Roboto Slab';
font-style: normal;
font-weight: 400;
font-size: 18px;
line-height: 145%;
/* or 26px */
text-align: center;

color: rgba(255, 255, 255, 0.8);


/* Inside auto layout */
flex: none;
order: 2;
align-self: stretch;
flex-grow: 0;
}



/* Frame 74 */
.frame74{
/* Auto layout */
display: flex;
flex-direction: row;
justify-content: center;
align-items: center;
padding: 0px;
gap: 10px;

width: 356px;
height: 17px;


/* Inside auto layout */
flex: none;
order: 4;
flex-grow: 0;

}
/* Письмо не пришло. Повторить отправку */

.frame74-text{
width: 292px;
height: 17px;

font-family: 'Roboto Slab';
font-style: normal;
font-weight: 400;
font-size: 14px;
line-height: 120%;
/* identical to box height, or 17px */
display: flex;
align-items: center;
text-align: center;

color: #2666FF;


/* Inside auto layout */
flex: none;
order: 0;
flex-grow: 0;
}

.frame74-text2{
width: 292px;
height: 17px;

font-family: 'Roboto Slab';
font-style: normal;
font-weight: 400;
font-size: 24px;
line-height: 120%;
/* identical to box height, or 17px */
display: flex;
align-items: center;
text-align: center;

/* Inside auto layout */
flex: none;
order: 0;
flex-grow: 0;
}




</style>


