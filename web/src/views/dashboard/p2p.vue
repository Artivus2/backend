<template>

<v-row style="margin-left: 180px; min-width: 1651px;" class="d-none d-sm-flex">
<v-col>
  <v-tabs
  v-model="type"
  
  @click="getTypes"
  >
  <v-tab :value="1">
    <div class="text-login">Покупка</div></v-tab>
    
      
    
  <v-tab :value="2">
    <div class="text-login">Продажа</div></v-tab>
  

  <v-tab :value="3">
    <div class="text-login">В обработке</div></v-tab>
  
  
  <v-tab :value="4">
    <div class="text-login">Мои ордера</div></v-tab>
  
  <v-tab :value="5">
    <div class="text-login">Все</div></v-tab>
  </v-tabs>
</v-col>
<v-col>
  <v-select :items="currency" 
    item-title="name" item-value="id"     
    
    v-model="selectedCurrency" :onchange="getCurrency"
    class="v-select text-login"
    
   >
   <template #item="{ item, props }">
        <v-list-item v-bind="props">
            <template #title>
                <span class="selectcur"><img style="width: 30px; height: 30px;" :src="item.raw.icon" /> &nbsp {{ item.raw.name}}</span>
            </template>
        </v-list-item>
    </template>

  </v-select>
</v-col>
<v-col>


  <v-select :items="charts" 
    item-title="symbol" item-value="id"     
    
    v-model="selectedChartsfromChips" :onchange="getChartChip"
    class="v-select text-login"
    
   >
   <template #item="{ item, props }">
        <v-list-item v-bind="props">
            <template #title>
                <span class="selectcur"><img style="width: 30px; height: 30px;" :src="item.raw.icon" /> &nbsp {{ item.raw.symbol}}</span>
            </template>
        </v-list-item>
    </template>

  </v-select>
</v-col>
<v-col>
  <v-select v-model="selectedPayments" :items="paymentsAll" item-title="name" item-value="id"
    chips multiple closable-chips :onchange="getPayments"
    density="compact"
    hide-details
    class="v-select text-login"
    >
    
    </v-select>
    
  </v-col>
  <v-col>
    <v-select v-model="selectedDurations" :items="durations" item-title="name" item-value="id"
      solo persistent-hint auto-select-first="exact" hide-details
      density="compact" :onchange="getDurations"
      
      class="v-select text-login"
      >
      
    </v-select>

  </v-col>
  <v-col>
    <v-select v-model="selectedRefresh" :items="refresh" item-title="name" item-value="id"
      solo hide-details auto-select-first="exact" @click="getFulllist()"
      density="compact"
      
      class="v-select text-login"
      >
    </v-select>
  </v-col>
</v-row>





<v-row style="margin-left: 180px;" class="d-none d-sm-flex">
  <v-window v-model="type">
    <v-window-item :value="1" class="overflowx">

    
    <v-row class="main-p2p" v-for="(orders, id) in ordersall">



      
      
      <v-row v-if="showdetails[id]">
      
          <v-col>
            <v-row>
              <v-col>
                Цена
              </v-col>
              <v-col>
                {{orders.course}}
              </v-col>
            </v-row>
            <v-row>
              <v-tabs v-model="curtype">
                <v-tab :value="1">
                  <div class="text-login">За фиат</div></v-tab>
                <v-tab :value="2" disabled>
                  <div class="text-login">За криптовалюту</div></v-tab>
                </v-tabs>
            </v-row>
            <v-row>
              <v-col>
            <v-text-field type="number" hide-details color="white"
              :label="orders.type == 2 ? orders.currency : orders.chart"
              v-model="selectedSum" variant="outlined"/>
          </v-col>
          <v-col >
            <v-btn @click="changeMax">{{ orders.type == 2 ? 'Макс' : 'Все'
            }}</v-btn>
          </v-col>
          <!-- <v-select v-model="selectedPayment" :items="selectedOrder.payments" hide-details 
            label="Способы оплаты продавца" item-title="name"
            item-value="id" solo auto-select-first="exact" 
            density="compact">
          </v-select> -->
            </v-row>
            <v-row class="openorder-limit">
              Лимит 
            </v-row>
            <v-row class="openorder-limit">
              Количество в получении
            </v-row>
            <v-row class="openorder-limit">
              Сумма в фиатной валюте
            </v-row>
            <v-row>
                <v-btn class="buy-button" @click="confirmtradebegin(id)">
                <div class="buy-button-text">
                  Купить
                </div>
                </v-btn>
                
                

               
            </v-row>
            <v-row class="openorder-limit">
              Способы оплаты
            </v-row>
            <v-row class="openorder-limit">
              Срок оплаты
            </v-row>
          </v-col>
          <v-divider vertical></v-divider>
          <v-col>
            Отзывы
          </v-col>

      
      </v-row>
      
      
      <v-row v-else>
          <v-col> 
          <v-row class="div-circle">
              <div class="circle">
                  <div class="letter-in-avatar">
                    {{orders.user[0]}} 
                  </div>
                  <div class="status"></div>
              </div>
          </v-row> 
        </v-col>
        <v-col style="min-width: 250px;">
          <v-row class="letter-white">
            {{ orders.user }} , {{ orders.order_id }}
          </v-row>  
          <v-row class="letter-blue">
            {{ orders.userOrdersCount }} исполнено | {{ orders.user_orders_count_complete_percent * 100 }} %
          </v-row>  
          <v-row class="letter-blue">
            В сети
          </v-row>  
        </v-col>
        <v-col style="min-width: 130px;">
          <div class="letter-white2 dopvcol">{{orders.course}} </div>
        </v-col>
        <v-col style="min-width: 120px;">
          <div class="letter-rub dopvcol2">{{ orders.currency }}</div>
        </v-col>
        <v-col style="min-width: 120px;" class="dopvcol3">
          <v-row class="letter-blue">
            Доступно
          </v-row>
          <v-row class="letter-blue">
            Лимиты
          </v-row>
        </v-col>
        <v-col style="min-width: 250px;" class="dopvcol3">
          <v-row class="letter-sum">
            {{ orders.amount }} {{ orders.chart }}
          </v-row>
          <v-row class="letter-sum">
            {{ orders.min_limit }} - {{ orders.max_limit }} {{ orders.currency }}
          </v-row>
        </v-col>
        <v-col style="min-width: 20px;"  class="dopvcol2">
                <h3 class="letter-rub" v-for="payment in orders.payments">
                      | {{ payment.name }}</h3>
        </v-col>
        <v-col style="min-width: 250px;">
          <v-btn class="buy-button" @click.stop="openOrder(id)">
          <div class="buy-button-text">
            Купить
          </div>
          </v-btn>
        </v-col>
      </v-row>
    </v-row>
    
    
      </v-window-item>  

       
 

<v-window-item :value="2" class="overflowx">
  <v-row class="main-p2p" v-for="(orders, id) in ordersall">



      
      
  <v-row v-if="showdetails[id]">

      <v-col>
        <v-row>
          <v-col>
            Цена
          </v-col>
          <v-col>
            {{orders.course}}
          </v-col>
        </v-row>
        <v-row>
          <v-tabs v-model="curtype">
            <v-tab :value="1">
              <div class="text-login">За фиат</div></v-tab>
            <v-tab :value="2" disabled>
              <div class="text-login">За криптовалюту</div></v-tab>
            </v-tabs>
        </v-row>
        <v-row>
          <v-col>
        <input type="number" style="color: white; border: 1px aliceblue;"
          :label="orders.currency"
          v-model="selectedSum"/>
      </v-col>
      <v-col >
        <v-btn @click="changeMax">{{ orders.type == 1 ? 'Макс' : 'Все'
        }}</v-btn>
      </v-col>
      <v-select v-model="selectedPayment" :items="selectedOrder.payments" hide-details 
            label="Способы оплаты продавца" item-title="name"
            item-value="id" solo auto-select-first="exact" 
            density="compact">
          </v-select>
        </v-row>
        <v-row class="openorder-limit">
          Лимит 
        </v-row>
        <v-row class="openorder-limit">
          Количество в получении
        </v-row>
        <v-row class="openorder-limit">
          Сумма в фиатной валюте
        </v-row>
        <v-row>
            <v-btn class="sell-button" @click="confirmtradebegin(id)">
            <div class="sell-button-text">
              Продать
            </div>
            </v-btn>
            
                <!-- <v-btn class="" @click="cancelTrade(id)">
                <div class="">
                  Отменить остатки
                </div>
                </v-btn> -->
            
        </v-row>
        <v-row class="openorder-limit">
          Способы оплаты
        </v-row>
        <v-row class="openorder-limit">
          Срок оплаты
        </v-row>
      </v-col>
      <v-divider vertical></v-divider>
      <v-col>
        Отзывы
      </v-col>


  </v-row>


  <v-row v-else>
    <v-col> 
    <v-row class="div-circle">
        <div class="circle">
            <div class="letter-in-avatar">
              {{orders.user[0]}}
            </div>
            <div class="status"></div>
        </div>
    </v-row> 
  </v-col>
  <v-col style="min-width: 250px;">
    <v-row class="letter-white">
      {{ orders.user }} , {{ orders.order_id }}
    </v-row>  
    <v-row class="letter-blue">
      {{ orders.userOrdersCount }} исполнено | {{ orders.user_orders_count_complete_percent * 100 }} %
    </v-row>  
    <v-row class="letter-blue">
      В сети
    </v-row>  
  </v-col>
  <v-col style="min-width: 130px;">
    <div class="letter-white2 dopvcol">{{orders.course}} </div>
  </v-col>
  <v-col style="min-width: 120px;">
    <div class="letter-rub dopvcol2">{{ orders.currency }}</div>
  </v-col>
  <v-col style="min-width: 120px;" class="dopvcol3">
    <v-row class="letter-blue">
      Доступно
    </v-row>
    <v-row class="letter-blue">
      Лимиты
    </v-row>
  </v-col>
  <v-col style="min-width: 250px;" class="dopvcol3">
    <v-row class="letter-sum">
      {{ orders.amount }} {{ orders.chart }}
    </v-row>
    <v-row class="letter-sum">
      {{ orders.min_limit }} - {{ orders.max_limit }} {{ orders.currency }}
    </v-row>
  </v-col>
  <v-col style="min-width: 20px;"  class="dopvcol2">
          <h3 class="letter-rub" v-for="payment in orders.payments">
                | {{ payment.name }}</h3>
  </v-col>
  <v-col style="min-width: 250px;">
    <v-btn class="sell-button" @click.stop="openOrder(id)">
    <div class="sell-button-text">
      Продать
    </div>
    </v-btn>
  </v-col>
  </v-row>
  </v-row>
</v-window-item>
  
<v-window-item :value="3">


  <v-row class="main-p2p" v-for="(orders, id) in ordersall">
          <v-col> 
          <v-row class="div-circle">
              <div class="circle">
                  <div class="letter-in-avatar">
                    {{orders.user[0]}}
                  </div>
                  <div class="status"></div>
              </div>
          </v-row> 
        </v-col>
        <v-col style="min-width: 250px;">
          <v-row class="letter-white">
            {{ orders.user }}
          </v-row>  
          <v-row class="letter-blue">
            {{ orders.userOrdersCount }} статус | {{ orders.status_history }} %
          </v-row>  
          <v-row class="letter-blue">
            В сети
          </v-row>  
        </v-col>
        <v-col style="min-width: 130px;">
          <div class="letter-white2 dopvcol">{{orders.course}} </div>
        </v-col>
        <v-col style="min-width: 120px;">
          <div class="letter-rub dopvcol2">{{ orders.currency }}</div>
        </v-col>
        <v-col style="min-width: 120px;" class="dopvcol3">
          <v-row class="letter-blue">
            Доступно
          </v-row>
          <v-row class="letter-blue">
            Лимиты
          </v-row>
        </v-col>
        <v-col style="min-width: 250px;" class="dopvcol3">
          <v-row class="letter-sum">
            {{ orders.amount }} {{ orders.chart }}
          </v-row>
          <v-row class="letter-sum">
            {{ orders.min_limit }} - {{ orders.max_limit }} {{ orders.currency }}
          </v-row>
        </v-col>
        <v-col style="min-width: 20px;"  class="dopvcol2">
                <h3 class="letter-rub" v-for="payment in orders.payments">
                      | {{ payment.name }}</h3>
        </v-col>
        <v-col style="min-width: 250px;">
          <v-btn color="success" @click="confirmCheckpay(id)">
                 check-pay
                </v-btn>
          
            <v-btn color="error" @click="confirmPayment(id)">
                  confirm-payment
                </v-btn>
            
                <v-btn color="error" @click="confirmAppeal(id)">
                  appeal
                </v-btn>

                <v-btn color="error" @click="cancelTrade(id)">
                  cancel-trade
                </v-btn>
                
        </v-col>
      </v-row>



  
                
</v-window-item>



<v-window-item :value="4" class="overflowx">
  

  <v-row style="margin: 5px;">
              <v-col style="margin-left: 50px">
                <v-icon color="info">mdi-plus</v-icon>
                <v-btn color="info" @click="createOrder">Создать</v-btn>
  
              </v-col>
            </v-row>

  <v-row class="main-p2p" v-for="(orders, id) in ordersall">


  
  
  
      
      
  <v-row v-if="showdetails[id]">

      <v-col>
        <v-row>
          <v-col>
            Цена
          </v-col>
          <v-col>
            {{orders.course}}
          </v-col>
        </v-row>
        <v-row>

        </v-row>
        <v-row>

        </v-row>
        <v-row class="openorder-limit">
          Лимит 
        </v-row>
        <v-row class="openorder-limit">
          Количество в получении
        </v-row>
        <v-row class="openorder-limit">
          Сумма в фиатной валюте
        </v-row>
        <v-row>

          <v-btn color="success" @click="confirmPayment(id)">
             confirm-payment
          </v-btn>

          <v-btn class="error" @click="cancelOrder(id)">
            cancel-order
          </v-btn>

          <v-btn class="red" @click="deleteOrder(id)">
            remove-order
          </v-btn>

          <v-btn color="success" @click="confirmCheckpay(id)">
            check=pay
          </v-btn>
          <v-btn color="info" @click="editOrder(id)">
            редакирование
          </v-btn>

        </v-row>
        <v-row class="openorder-limit">
          Способы оплаты
        </v-row>
        <v-row class="openorder-limit">
          Срок оплаты
        </v-row>
      </v-col>
      <v-divider vertical></v-divider>
      <v-col>
      <v-btn color="orange" @click.stop="closeInfo(id)">
      Закрыть
    </v-btn>
      </v-col>


  </v-row>


  <v-row v-else>
    <v-col> 
    <v-row class="div-circle">
        <div class="circle">
            <div class="letter-in-avatar">
              {{orders.user[0]}}
            </div>
            <div class="status"></div>
        </div>
    </v-row> 
  </v-col>
  <v-col style="min-width: 250px;">
    <v-row class="letter-white">
      {{ orders.user }} , {{ orders.order_id }}
    </v-row>  
    <v-row class="letter-blue">
      {{ orders.userOrdersCount }} исполнено | {{ orders.user_orders_count_complete_percent * 100 }} %
    </v-row>  
    <v-row class="letter-blue">
      В сети
    </v-row>  
  </v-col>
  <v-col style="min-width: 130px;">
    <div class="letter-white2 dopvcol">{{orders.course}} </div>
  </v-col>
  <v-col style="min-width: 120px;">
    <div class="letter-rub dopvcol2">{{ orders.currency }}</div>
  </v-col>
  <v-col style="min-width: 120px;" class="dopvcol3">
    <v-row class="letter-blue">
      Доступно
    </v-row>
    <v-row class="letter-blue">
      Лимиты
    </v-row>
  </v-col>
  <v-col style="min-width: 250px;" class="dopvcol3">
    <v-row class="letter-sum">
      {{ orders.amount }} {{ orders.chart }}
    </v-row>
    <v-row class="letter-sum">
      {{ orders.min_limit }} - {{ orders.max_limit }} {{ orders.currency }}
    </v-row>
  </v-col>
  <v-col style="min-width: 20px;"  class="dopvcol2">
          <h3 class="letter-rub" v-for="payment in orders.payments">
                | {{ payment.name }}</h3>
  </v-col>
  <v-col style="min-width: 250px;">
    <v-btn color="orange" @click.stop="openOrder(id)">
      Информация
    </v-btn>
  </v-col>
  </v-row>
  </v-row>
</v-window-item>
  </v-window>
</v-row>























<v-dialog v-model="dialog2" max-width="400px">
      <v-card style="min-width: 350; min-height: 400; padding: 10px;">
        <v-row>
          <v-col>
            <!-- <h3 style="text-align: center;">{{ selectedOrder.type == 2 ? 'Покупка' : 'Продажа' }} {{ selectedOrder.chart  }}</h3> -->
            <h3 style="text-align: center;">Ордер № {{ selectedOrder.uuid }} ({{ selectedOrder.order_id }})</h3>
          </v-col>
        </v-row>
        <v-row>
          <v-col>
            <h5>Цена</h5>
          </v-col>
          <v-col class="text-right">
            {{ selectedOrder.course }} {{ selectedOrder.currency }}
          </v-col>
        </v-row>
        <v-divider></v-divider>
  
        <v-row>
          <v-col>
            <h5>{{selectedOrder.type == 1 ? 'Количество' : 'Остаток'}}</h5>
          </v-col>
          <v-col class="text-right">
            {{ selectedOrder.amount }} {{ selectedOrder.chart }}
          </v-col>
        </v-row>
        <v-divider></v-divider>
  
        <v-row>
          <v-col>
            <h5>Лимиты</h5>
          </v-col>
          <v-col class="text-right">
            {{ selectedOrder.min_limit }} - {{ selectedOrder.max_limit }} {{ selectedOrder.currency }}
          </v-col>
        </v-row>
        <v-divider></v-divider>
  
        <v-row>
          <v-col cols="7">
            <v-text-field style="margin-top: 10px;" type="number" hide-details
              :label="selectedOrder.type == 2 ? selectedOrder.currency : selectedOrder.chart" density="compact"
              v-model="selectedSum" variant="outlined" />
          </v-col>
          <v-col class="text-center">
            <v-btn style="margin: 10px;" @click="changeMax">{{ selectedOrder.type == 2 ? 'Макс' : 'Все'
            }}</v-btn>
          </v-col>
          <v-col class="text-right" v-if="selectedOrder.type == 1">
            <!-- Баланс: {{ selectedOrder.chart_id === wallet.filter(wallet => wallet.id).balance ? wallet : '0.00'}} -->
            Ваш баланс: {{ wallet.find(wall => wall.id === selectedOrder.chart_id) ? wallet.find(wall => wall.id ===
              selectedOrder.chart_id).balance : '0.00' }} {{ selectedOrder.chart }}
          </v-col>
        </v-row>
        <v-row style="padding: 0px;">
          <v-col>
            Я получу
          </v-col>
          <v-col class="text-right" v-if="selectedOrder.type == 2">
            {{ selectedSum / selectedOrder.course }} {{ selectedOrder.chart }}
          </v-col>
          <v-col v-else class="text-right">
            {{ selectedSum * selectedOrder.course }} {{ selectedOrder.currency }}
          </v-col>
        </v-row>
        <v-divider></v-divider>
        <v-row>
          <v-col v-if="selectedOrder.type == 2">
            <h5 style="text-align: center; color:error;">Совершите оплату в течение {{ selectedOrder.duration }} минут.
              Приобретенные монеты будут зачислены на ваш финансовый аккаунт</h5>
          </v-col>
          <v-col v-else>
            <h5 style="text-align: center;">Дождитесь, пока контрагент сделает платеж. Деньги должны поступить на указанные
              вами банковские реквизиты</h5>
          </v-col>
        </v-row>
        <v-row v-if="selectedOrder.type == 2">
          <v-select v-model="selectedPayment" :items="selectedOrder.payments" hide-details 
            label="Способы оплаты продавца" item-title="name"
            item-value="id" solo auto-select-first="exact" 
            density="compact">
          </v-select>
        </v-row>

        <v-row v-else>
          <v-select v-model="selectedPayment" :items="selectedOrder.payments" hide-details 
            label="Возможные способы оплаты ордера" item-title="name"
            item-value="id" solo auto-select-first="exact" 
            density="compact"
            :onchange="sellerPayment">
          </v-select>
        </v-row>
        <v-row>
          <v-divider></v-divider>
        </v-row>
        <v-row>
          <!-- <v-img 
            :src="orders.image"
            style="width: 30px; margin: 3px;"
            
            ></v-img> -->
          <v-col>
            <v-card-item>
  
              <v-icon :icon="selectedOrder.verify_status == 1 ? 'mdi-account-check' : 'mdi-account'" size="18"
                :color="selectedOrder.verify_status == 1 ? 'success' : 'error'"></v-icon>
  
              <!-- <v-icon
            icon="mdi-shield-account-outline"
            size="18"
            color="purple"
            
          ></v-icon> -->
  
              {{ selectedOrder.user }}
  
            </v-card-item>
          </v-col>
  
          <v-col class="text-right">
            <v-card-item>
              <v-rating :model-value="4.5" color="amber" density="compact" half-increments readonly size="small"></v-rating>
  
              <div class="ms-2">
                <h5><b>{{ selectedOrder.userOrdersCount }}</b> ордер(а,ов) </h5>
                <h5><b>{{ selectedOrder.user_orders_count_complete_percent * 100}} %</b> выполнено</h5>
              </div>
            </v-card-item>
          </v-col>
  
        </v-row>
        <v-divider></v-divider>
        <v-row>
          <v-col class="text-left">
            <h6>- Быстрая сделка</h6>
            <h6>- Обмен не дольше 2-х минут</h6>
            <h6>- Самый выгодный курс</h6>
          </v-col>
        </v-row>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn @click="close">
            Отмена
          </v-btn>
          <v-btn 
          :color="selectedOrder.type == 2 ? 'success' : 'error'"
          @click="confirmtradeopen">
            {{selectedOrder.type == 2 ? 'Купить' : 'Продать' }}
            
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

  
    <v-dialog v-model="dialogdelete" max-width="400px" persistent style="opacity: 1;">
      <v-row style="width: 90%; justify-content: center; align-items: center;">
        <v-card>
          <v-row>
            <v-col>
              <h3 style="text-align: center;">Ордер № {{ selectedOrder.uuid }}</h3>
            </v-col>
          </v-row>
          <v-divider vertical></v-divider>
          <v-row>
            <v-col>
              <h4 style="text-align: center;">Подтвердите удаление ордера</h4>
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="12" class="d-flex">
              <v-btn color="error" style="width: 110px; margin: 10px" @click="cancelDelete">
                Отмена
              </v-btn>
              <v-col></v-col>
              <v-btn 
              color="error" style="width: 110px; margin: 10px" @click="confirmDelete(id)">
                Удалить
              </v-btn>
            </v-col>
          </v-row>
        </v-card>
      </v-row>
    </v-dialog>



<!-- редактирование -->
    <v-dialog v-model="dialogEdit" min-width="360px" max-width="400px" persistent>
    <v-row style="width: 100%; justify-content: center; align-items: center;">  
    <v-card >
        
          <v-row style="width: 100%; justify-content: center; align-items: center; margin:10px;">
            <v-col>
              <h3 style="text-align: center;">редактирование ордера на {{ selectedOrder.type = 1 ? 'Покупку' : 'Продажу' }}</h3>
            </v-col>
          </v-row>
          <v-divider></v-divider>
          <v-row >
            <v-col >
          <!-- <v-tabs v-model="editType"  style="text-align:center">
            <v-tab :value="1" color="success" prepend-icon="mdi-debug-step-into" >
              <p color="success">Покупка</p>
            </v-tab>
            <v-tab :value="2" color="error" prepend-icon="mdi-debug-step-out" >
              <p color="error">Продажа</p>
            </v-tab>
            <v-tab :value="3" color="info" disabled>
              <p color="error">В один клик</p>
            </v-tab>
          
            </v-tabs> -->
   
  
        </v-col>
        </v-row>
        <v-divider></v-divider>
        <v-row >
          <v-col class="text-center" v-if="create == 2">
          
            Ваш баланс: {{ wallet.find(wall => wall.id === createChart_id) ? wallet.find(wall => wall.id ===
              createChart_id).balance : '0.00' }} 
              {{ charts.find(ch => ch.id === editChart_id) ? charts.find(ch => ch.id ===
              editChart_id).symbol : '-' }}
          </v-col>
        </v-row>


        <v-row style="width: 95%; justify-content: center; align-items: center; margin:5px;">
          <v-select prepend-icon="mdi-bitcoin" label="Криптовалюта" :items="charts" density="compact" item-title="symbol" item-value="id"
        v-model="editChart_id" style="width: 250px; margin-bottom: 5px;"
        auto-select-first="exact" hide-details>
      </v-select>
      <v-select prepend-icon="mdi-cash" label="Доступная валюта" :items="currency" density="compact" item-title="name" item-value="id"
        v-model="editCurrency_id" style="width: 250px; margin-bottom: 5px;" hide-details
        persistent-hint :onchange="getCurrency" auto-select-first="exact">
      </v-select>
  
          <v-text-field  type="number" 
          label="Количество" density="compact" hide-details append-inner-icon="mdi-arrow-top-right-bold-box" single-line
          @click:append-inner="getMaxAmount" v-model="editAmount" variant="outlined"/>
  
          <v-text-field style="margin-top: 10px;" type="number"
          label="Курс" density="compact" hide-details append-inner-icon="mdi-arrow-top-right-bold-box" single-line
          @click:append-inner="getMaxAmount" v-model="editCourse" variant="outlined"/>
          
          <v-text-field style="margin-top: 10px;" type="number"
          label="Лимит min" density="compact" hide-details append-inner-icon="mdi-arrow-top-right-bold-box" single-line
          v-model="editmin_limit" variant="outlined" />
          
          <v-text-field style="margin-top: 10px;" type="number"
          label="Лимит max" density="compact" hide-details append-inner-icon="mdi-arrow-top-right-bold-box" single-line
          v-model="editmax_limit" variant="outlined" />
  
      
      
  
      
      <v-combobox v-model="editPayments" :items="userpayment" label="Способы оплаты" item-title="name" item-value="payment_id"
        
        chips closable-chips hide-details multiple clearable 
        persistent-hint auto-select-first="exact" @input="remove"
        style="width: 250px; margin-left: 20px;" density="compact">
        <template v-slot:prepend>
            <v-btn
            @click="openaddPayment"
            >
            <v-icon>mdi-plus</v-icon>          
            </v-btn>
          </template>
      </v-combobox>
      
  
      <v-combobox v-model="editDuration" :items="myDurations" label="Периоды оплаты" item-title="name" item-value="id"
        prepend-icon="mdi-timeline-outline" solo persistent-hint auto-select-first="exact" hide-details
        :onchange="getDurations" style="width: 250px; margin-left: 20px;"
        density="compact">
      </v-combobox>
  
      
    </v-row>
    
          <v-row>
            <v-col cols="12" class="d-flex">
              <v-btn color="error" style="width: 110px; margin: 10px" @click="cancelEdit">
                Отмена
              </v-btn>
              <v-col></v-col>
              <v-btn color="success" style="width: 110px; margin: 10px" @click="confirmEdit">
                применить
              </v-btn>
            </v-col>
          </v-row>
        
        </v-card>
        </v-row>
    </v-dialog>



    <v-dialog v-model="dialogCreate" min-width="360px" max-width="400px" persistent>
    <v-row style="width: 100%; justify-content: center; align-items: center;">  
    <v-card >
        
          <v-row style="width: 100%; justify-content: center; align-items: center; margin:10px;">
            <v-col>
              <h3 style="text-align: center;">Создание ордера</h3>
            </v-col>
          </v-row>
          <v-divider></v-divider>
          <v-row >
            <v-col >
          <v-tabs v-model="create"  style="text-align:center">
            <v-tab :value="1" color="success" prepend-icon="mdi-debug-step-into" >
              <p color="success">Покупка</p>
            </v-tab>
            <v-tab :value="2" color="error" prepend-icon="mdi-debug-step-out" >
              <p color="error">Продажа</p>
            </v-tab>
            <v-tab :value="3" color="info" disabled>
              <p color="error">В один клик</p>
            </v-tab>
          
            </v-tabs>
   
  
        </v-col>
        </v-row>
        <v-divider></v-divider>
        <v-row >
          <v-col class="text-center" v-if="create == 2">
          
            Ваш баланс: {{ wallet.find(wall => wall.id === createChart_id) ? wallet.find(wall => wall.id ===
              createChart_id).balance : '0.00' }} 
              {{ charts.find(ch => ch.id === createChart_id) ? charts.find(ch => ch.id ===
              createChart_id).symbol : '-' }}
          </v-col>
        </v-row>
        <v-row style="width: 95%; justify-content: center; align-items: center; margin:5px;">
          <v-select prepend-icon="mdi-bitcoin" label="Криптовалюта" :items="charts" density="compact" item-title="symbol" item-value="id"
        v-model="createChart_id" style="width: 250px; margin-bottom: 5px;"
        auto-select-first="exact" hide-details>
      </v-select>
      <v-select prepend-icon="mdi-cash" label="Доступная валюта" :items="currency" density="compact" item-title="name" item-value="id"
        v-model="createCurrency_id" style="width: 250px; margin-bottom: 5px;" hide-details
        persistent-hint :onchange="getCurrency" auto-select-first="exact">
      </v-select>
  
          <v-text-field  type="number" 
          label="Количество" density="compact" hide-details append-inner-icon="mdi-arrow-top-right-bold-box" single-line
          @click:append-inner="getMaxAmount" v-model="createAmount" variant="outlined"/>
  
          <v-text-field style="margin-top: 10px;" type="number"
          label="Курс" density="compact" hide-details append-inner-icon="mdi-arrow-top-right-bold-box" single-line
          @click:append-inner="getMaxAmount" v-model="createCourse" variant="outlined"/>
          
          <v-text-field style="margin-top: 10px;" type="number"
          label="Лимит min" density="compact" hide-details append-inner-icon="mdi-arrow-top-right-bold-box" single-line
          v-model="createmin_limit" variant="outlined" />
          
          <v-text-field style="margin-top: 10px;" type="number"
          label="Лимит max" density="compact" hide-details append-inner-icon="mdi-arrow-top-right-bold-box" single-line
          v-model="createmax_limit" variant="outlined" />
  
      
      
  
      
      <v-combobox v-model="createPayments" :items="userpayment" label="Способы оплаты" item-title="name" item-value="payment_id"
        
        chips closable-chips hide-details multiple clearable 
        persistent-hint auto-select-first="exact" @input="remove"
        style="width: 250px; margin-left: 20px;" density="compact">
        <template v-slot:prepend>
            <v-btn
            @click="openaddPayment"
            >
            <v-icon>mdi-plus</v-icon>          
            </v-btn>
          </template>
      </v-combobox>
      
  
      <v-combobox v-model="createDuration" :items="myDurations" label="Периоды оплаты" item-title="name" item-value="id"
        prepend-icon="mdi-timeline-outline" solo persistent-hint auto-select-first="exact" hide-details
        :onchange="getDurations" style="width: 250px; margin-left: 20px;"
        density="compact">
      </v-combobox>
  
      
  
      
  
      
    </v-row>
    
          <v-row>
            <v-col cols="12" class="d-flex">
              <v-btn color="error" style="width: 110px; margin: 10px" @click="cancelCreate">
                Отмена
              </v-btn>
              <v-col></v-col>
              <v-btn color="success" style="width: 110px; margin: 10px" @click="confirmCreate">
                Создать
              </v-btn>
            </v-col>
          </v-row>
        
        </v-card>
        </v-row>
    </v-dialog>

</template>
  
  <script>
  import api from '../../api'
  import moment from 'moment'
  
  
  export default {
  
  mounted() {

    api.get('api/verify/list', {
        headers: {
          'Authorization': localStorage.getItem('access_token')
        } // мои способы оплаты
      }).
        then(response => {
          console.log('Получили данные по верификации')
          this.verifylist = response.data
          console.log(this.verifylist)
          //localStorage.setItem('userpayment', JSON.stringify(response.data))
          //console.log(this.userpayment)
  
          
          
        }).catch(error => {
          console.log(error)
        });
    
    this.showdetails = new Array(this.ordersall.length).fill(false)
    //console.log(this.showdetails)
    api.get('api/ratings/info?user_id=356', {
        headers: {
          'Authorization': localStorage.getItem('access_token')
        }
      }).
        then(response => {
          //console.log('Получили данные по доступным криптовалютам')
          this.ratings = response.data
          console.log(this.ratings)
          //localStorage.setItem('p2p_data', JSON.stringify(response.data))
  
          ////console.log(this.charts)
        }).catch(error => {
          //console.log(error)
        })
    
    //получаем список доступной критповалюты
    api.get('api/chart/list?p2p=1', {
        headers: {
          'Authorization': localStorage.getItem('access_token')
        }
      }).
        then(response => {
          //console.log('Получили данные по доступным криптовалютам')
          this.charts = response.data
          //console.log(this.charts)
          //localStorage.setItem('p2p_data', JSON.stringify(response.data))
  
          ////console.log(this.charts)
        }).catch(error => {
          //console.log(error)
        })
    
    //получаем список доступной валюты
    api.get('api/chart/currency?p2p=1', {
        headers: {
          'Authorization': localStorage.getItem('access_token')
        }
      }).
        then(response => {
          //console.log('Получили данные по валютам')
          this.currency = response.data
          //localStorage.setItem('p2p_data', JSON.stringify(response.data))
  
          //console.log(this.currency)
        }).catch(error => {
          //console.log(error)
        })
  
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
  
    //selectedItem: null,
    timestamp: "",
    type: JSON.parse(localStorage.getItem('type')) == null || localStorage.getItem('type')=='undefined' ? 1 : JSON.parse(localStorage.getItem('type')),
    tabs: 1,
    create: 1,
    curtype: 1,
    selectedCharts: 259,
    dialog: false,
    dialog2: false,
    dialogconfirmtrade: false,
    dialogCheckpay: false,
    dialogConfirmPayment: false,
    trading: false,
    verifylist: null,
    // openorder: false,
    editorder: false,
    dialogdelete: false,
    dialogCreate: false,
    dialogEdit: false,
    dialogaddPayment:false,
    dialogReasons: null,
    selectedSum: null,
    itemsPerPage: 10,
    expanded: [],
    refresh: [
      { id: 0, name: "Не обновлять" },
      { id: 1, name: "Обновить (через 5 секунд)" },
      { id: 2, name: "Обновить (через 10 секунд)" },
      { id: 3, name: "Обновить (через 20 секунд)" },
    ],
  
    selectedRefresh: 0,
    durations: [
      { id: 0, name: "Любой период" },
      { id: 1, name: "15 минут"},
      { id: 2, name: "30 минут"},
    ],
    myDurations: [
      { id: 0, name: "15 минут", timing: 15 },
      { id: 1, name: "30 минут" , timing: 30 },
    ],
    selectedDurations: 0,
    secondscount: 59,
    minutescount: 15,
    intervalID: null,
    selectedOrder: null,
    ordersall: [],
    showdetails:[],
  
    selectedChartsfromChips: null,
    selectedCharts: null,
    
    charts: [],
    currency: [],
    ratings: [],
    selectedCurrency: 1,
    paymentsAll: [],
    selectedPayments: null,
    userpayment: null,
    selectedUserPayment: null,
    selectedPayment: null,
    selectedWallet: null,
    wallet: [],
    thisWallet: null,
    maxsum: null,
    sendConfirmTrade: [],
    status: [
  
      { id: -1, name: "Ожидает предложений" },
      { id: 1, name: "запрос сделки" },
      { id: 2, name: "Оплачен" },
      { id: 3, name: "Ожидает оплаты" },
      { id: 4, name: "исполнен" },
      { id: 5, name: "Аппеляция" },
      { id: 6, name: "Отменен" },
      { id: 7, name: "Отменен системой" },
      { id: 8, name: "Заблокирован" },
      { id: 9, name: "Удален" },
      { id: 10, name: "Полностью исполнен" },
  
    ],
    createAmount: null,
    createChart_id: null,
    createCurrency_id: 1,
    createCourse: null,
    createmin_limit: null,
    createmax_limit: null,
    createPayments:[],
    createDuration: null,
    editType: null,
    editAmount: null,
    editChart_id: null,
    editCurrency_id: 1,
    editCourse: null,
    editmin_limit: null,
    editmax_limit: null,
    editPayments:null,
    editDuration: null,
    loader: false,
  
  
  
  }),
  computed: {
      sellerPayment(){
        this.loader = true
        //console.log(this.userpayment)
        //console.log(this.selectedPayment)
        
        //console.log(this.userpayment.find(so => so.payment_id === this.selectedPayment))
        if (this.selectedPayment !== null) {
          if (this.userpayment.find(so => so.payment_id === this.selectedPayment)) {
            //console.log('Выбранный способ оплаты подходит')
          } else {
            alert('Необходимо добавить реквизиты для данного способа оплаты в настройках вашего профиля')
          }
        }

        this.loader = false
      },
  
      getRefresh() {
        ////console.log(this.selectedRefresh)
        localStorage.setItem('refresh', JSON.stringify(this.selectedRefresh))
        //this.getFulllist()
        //console.log('string:chart' + this.selectedCharts + '&curr:' + this.selectedCurrency + '&pay:' + localStorage.getItem('payments'))
        
  
  
      },
      getDurations() {
  
        if (this.selectedDurations[1] || this.selectedDurations[2]) {
          //   //this.selectedDurations.splice(this.selectedDurations.indexOf(id), 0)
          localStorage.setItem('duration', JSON.stringify(this.selectedDurations))
        }
  
        //console.log(this.selectedDurations)
  
  
      },
      getTypes() {
  
        localStorage.setItem('type', JSON.stringify(this.type))
        //console.log(this.type)
        //
        this.getFulllist()
  
  
      },
      getChartChip() {
        //console.log('обновление')
        ////console.log(this.selectedChartsfromChips)
        if (this.selectedChartsfromChips !== null) {
        
        //this.selectedCharts = this.charts.at(this.selectedChartsfromChips)
        this.selectedCharts = this.selectedChartsfromChips
        //console.log(this.selectedCharts)
        localStorage.setItem('charts', JSON.stringify(this.selectedCharts))
      }
  
        // this.selectedCharts = this.selectedChartsfromChips
        // this.selectedCharts = this.charts[this.selectedCharts].indexOf(id)
        
        // localStorage.setItem('charts', this.selectedChartsfromChips)
  
        ////console.log(this.selectedCharts)
  
      },
  
   
      getPayments() {
        //console.log(this.selectedPayments)
        localStorage.setItem('payments', JSON.stringify(this.selectedPayments))
        var payments = JSON.parse(localStorage.getItem('payments'))
        //console.log(payments)
        if (payments !== null) {
  
           var keys = payments.map(function (obj) {
             return obj
           })
          var result = keys.join(',')
        
  
           //console.log(result)
         }
         localStorage.setItem('payments', JSON.stringify(result))
  
      },
      getCurrency() {
        //console.log('Тип валюты: ' + this.selectedCurrency)
        this.selectedPayments = null
        localStorage.setItem('currency', JSON.stringify(this.selectedCurrency))
        if (this.selectedCurrency != null) {
          api.get('api/payment/list?currency_id=' + this.selectedCurrency, {
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
        }
      },
  
    },
    methods: {
      formatDate(date) {
        return moment.unix(date).utc().format('YYYY-MM-DD HH:mm:ss');
      },
      getRefreshed() {
        //console.log('string:chart' + this.selectedCharts + '&curr:' + this.selectedCurrency + '&pay:' + localStorage.getItem('payments'))
      },
  
      getFulllist() {
        this.loader = true
        //this.showdetails.fill(false)
        this.type = JSON.parse(localStorage.getItem('type'))
        this.selectedCharts = JSON.parse(localStorage.getItem('charts'))
        //this.selectedCurrency = JSON.parse(localStorage.getItem('currency'))
  
        //this.selectedDurations = JSON.parse(localStorage.getItem('durations'))
  
        let str = ''
        let typereal
        let profile
  
        profile = JSON.parse(localStorage.getItem('profile'))
        if (this.type == 1) { typereal = 2 } else { typereal = 1 }
        ////console.log('в итоге: ' + this.selectedCharts)
        ////console.log('в итоге: ' + this.selectedCurrency)
        str = 'full-list?status=-1&type=' + typereal
        if (this.selectedCharts == null) {
          str += ''
        } else {
          str += '&chart_id=' + this.selectedCharts
        }
        
        if (this.selectedCurrency == null) {this.selectedCurrency = 1}
        str += '&currency_id=' + this.selectedCurrency
        if (this.type == 3) {
          // .find(wall => wall.id === selectedOrder.chart_id
          str = 'history?status_history=1,2,5&user_id='+ profile.id  //в обработке
        }
        if (this.type == 4) { //мои ордера
  
          //console.log(profile)
          str = 'full-list?status=-1,6&user_id='+ profile.id 
        }
        if (localStorage.getItem('payments')=="undefined" || this.selectedPayments == null) {
          str += ''
        } else {str += '&payments=' + this.selectedPayments }
        
        if (this.type == 5) { str='history?status_history=4,6,7,9&user_id='+profile.id } // история
        console.log('summary: ' + str)
        api.get('api/p2p/' + str, {
          headers: {
            'Authorization': localStorage.getItem('access_token')
          } // добавьте ваш заголовок авторизации
        }).
          then(response => {
            //console.log('Получаем таблицу с обновленными параметрами')
            this.ordersall = response.data
            console.log(response.data)
            //localStorage.setItem('p2p_data', JSON.stringify(response.data))
  
            ////console.log(this.orders)
            this.loader = false
          }).catch(error => {
            console.log(error)
          });
  
          
      },
  
  
      remove() {
        //console.log(this.selectedPayments)
        this.selectedPayments.splice(this.selectedPayments.indexOf(id), 1)
        this.selectedPayments = [...this.selectedPayments]
      },
      
  
      getColor(type) {
        if (type == 1) return 'success'
        else return 'error'
      },
      close() {
        this.dialog = false
        this.selectedPayment = null
  
      },
      openOrder(id) {
        // this.dialog = true
        this.selectedOrder = this.ordersall[id]
        //console.log('выбран ордер: ' + this.selectedOrder.order_id)
        this.showdetails.fill(false)
        this.showdetails[id] = ! this.showdetails[id]
  
  
        //логика по статусам
  
      },
      closeInfo(id) {
        this.showdetails[id] = ! this.showdetails[id]
      },
      confirmtradeopen() {
        this.dialogconfirmtrade = true
  
      },
      reasons() {
        this.dialogconfirmtrade = false
        this.dialog = false
        //this.dialogReasons = true
      },
      confirmtradebegin(id) {
        this.loader = true
        // this.selectedPayment = 4
        //console.log(this.selectedOrder)
        this.dialog = false
        this.dialogconfirmtrade = false
        //console.log(this.selectedSum)
        
        if (this.selectedOrder.order_id == null || this.selectedSum == null) {
          alert("Не верные данные")
          this.trading = false
          
          
        } else {
          // this.sendConfirmTrade.push(this.selectedOrder.order_id)
          // this.sendConfirmTrade.push(this.selectedSum)
          // this.sendConfirmTrade.push(this.selectedPayment.id)
          // //console.log(this.sendConfirmTrade)
          //отправляем запрос на конфирм трейд статус 1
          var send = {
            p2p_ads_id: this.selectedOrder.order_id,
            offer: this.selectedSum,
            payment: this.selectedOrder.type == 1 ? 15 : 1000
            // payment: this.selectedPayment
          }
  
          api({
            method: 'post',
            url: '/api/p2p/confirm-trade',
            headers: {
              'Authorization': localStorage.getItem('access_token')
            },
            data: send
          })
            .then(function (response) {
              alert(response.data.message)
              //console.log(response.data);
              this.trading = true
              this.timecount = this.selectedOrder.duration
              // setInterval(this.getTimer, 1000);
              this.loader = false
  
            })
            .catch(function (error) {
              alert(error.response.data.message);
              //console.log(error);
              this.trading = false
            });
  
        }
  
  
      },
      changeMax() {
        if (this.selectedOrder.type==2) {
          if (this.selectedOrder.amount * this.selectedOrder.course > this.selectedOrder.max_limit)  {
            this.selectedSum = this.selectedOrder.max_limit
          } else{
            this.selectedSum = this.selectedOrder.amount * this.selectedOrder.course
          }
        } else {
          //console.log(this.selectedOrder.amount)
          //console.log(this.selectedOrder.course)
          //console.log(this.selectedOrder.max_limit)
          if (this.selectedOrder.max_limit / this.selectedOrder.course < this.selectedOrder.amount)  {
              if (this.selectedOrder.amount < 1) {
              this.selectedSum = (this.selectedOrder.max_limit / this.selectedOrder.course).toFixed(6)
              } else {
              this.selectedSum = this.selectedOrder.max_limit / this.selectedOrder.course
              }
          } else {
            
              this.selectedSum = this.selectedOrder.amount
            

          }

        }

        
  
      },
      getTimer: function () {
  
        //console.log(this.timecount)
  
      },
      cancelOrder(id) {
        this.selectedOrder = this.ordersall[id]
        console.log(this.selectedOrder)
        if (this.selectedOrder.order_id == null) {
          alert("Не верные данные")
          
        } else {
          console.log(this.selectedOrder.order_id)
          var send = {
            p2p_ads_id: this.selectedOrder.order_id,
          }
  
          api({
            method: 'post',
            url: '/api/p2p/cancel-order',
            headers: {
              'Authorization': localStorage.getItem('access_token')
            },
            data: send
          })
            .then(function (response) {
              alert(response.data.message)
              console.log(response);
              this.loader = false
  
            })
            .catch(function (error) {
  
              alert(error.response.data.message);
              console.log(error.response.data.message);
  
            });
          }
      },
      
      deleteOrder(id) {
        this.dialogdelete = true
        this.selectedOrder = this.ordersall[id]
        // //console.log('выбран ордер: ' + this.selectedOrder.order_id)
      },
      cancelDelete() {
        this.dialogdelete = false
      },
      confirmDelete(id) {
        this.loader = true
        
        //api
        if (this.selectedOrder.order_id == null) {
          alert("Не верные данные")
          this.dialogdelete = false
        } else {
          console.log(this.selectedOrder.order_id)
          var send = {
            p2p_ads_id: this.selectedOrder.order_id,
          }
  
          api({
            method: 'post',
            url: '/api/p2p/remove-order',
            headers: {
              'Authorization': localStorage.getItem('access_token')
            },
            data: send
          })
            .then(function (response) {
              alert(response.data.message)
              console.log(response);
              this.loader = false
  
            })
            .catch(function (error) {
  
              alert(error.response.data.message);
              console.log(error);
  
            });
          this.dialogdelete = false
        }
      },
  
  
      cancelCreate (){
        this.dialogCreate = false
      },
      createOrder() {
        this.dialogCreate = true
      },

      // editOrder(id) {
      //   console.log(id)
      //   this.dialogEdit = true
        
      // },
      getMaxAmount(){
        //console.log(this.createDuration)
      },
  
      confirmCreate(){
        ////console.log(this.createDuration.timing)
        //api
        let paymentss = [4,33]
        this.loader = true
        let urlorder
        if (this.create == 1) {
          urlorder = 'create-buy'
        } else {
          urlorder = 'create-sell'
        }
        if (this.createAmount == null || this.createChart_id == null || this.createCourse == null || this.createDuration.timing == null || 
        this.createPayments == null || this.createmax_limit == null || this.createmin_limit == null) {
          alert("Введены не все данные")
        // this.dialogCreate = false
        } else {
          
          console.log(paymentss)
          var send = {
            currency_id: this.createCurrency_id,
            chart_id: this.createChart_id,
            amount: this.createAmount,
            course: this.createCourse,
            min_limit: this.createmin_limit,
            max_limit: this.createmax_limit,
            payments: paymentss,
            duration: this.createDuration.timing,
  
          }
          //console.log(send)
          api({
            method: 'post',
            url: '/api/p2p/' + urlorder,
            headers: {
              'Authorization': localStorage.getItem('access_token')
            },
            data: send
          })
            .then(function (response) {
              alert(response.data.message)
              console.log(response);
              this.loader = false
  
            })
            .catch(function (error) {
  
              alert(error.response.data.message);
              console.log(error);
  
            });
          this.dialogCreate = false
        }
      },
  
  
      editOrder(id){
        this.dialogEdit = true
        this.selectedOrder = this.ordersall[id]
        console.log(this.selectedOrder)
        this.editAmount = this.selectedOrder.amount
        this.editChart_id = this.selectedOrder.chart_id
        this.editCurrency_id = this.selectedOrder.currency_id
        this.editCourse = this.selectedOrder.course
        this.editmin_limit = this.selectedOrder.min_limit
        this.editmax_limit = this.selectedOrder.max_limit
        this.editPayments = this.selectedOrder.payments
        this.editduration = this.selectedOrder.duration
        this.editType = this.selectedOrder.type
      },
      cancelEdit(){
        this.dialogEdit = false
      },
      confirmEdit(){
        this.loader = true
        //let urlorder
        
        // if (this.editAmount == null || this.editChart_id == null || this.editCourse == null || this.editDuration.timing == null || 
        // this.editPayments == null || this.editmax_limit == null || this.editmin_limit == null) {
        //   alert("Введены не все данные")
        // // this.dialogCreate = false
        // } else {
          
          
          var send = {
            p2p_ads_id: this.selectedOrder.order_id,
            currency_id: this.editCurrency_id,
            chart_id: this.editChart_id,
            amount: this.editAmount,
            course: this.editCourse,
            min_limit: this.editmin_limit,
            max_limit: this.editmax_limit,
            payments: [4,33],
            duration: this.editDuration.timing,
  
          }
          //console.log(send)
          api({
            method: 'post',
            url: '/api/p2p/edit-order',
            headers: {
              'Authorization': localStorage.getItem('access_token')
            },
            data: send
          })
            .then(function (response) {
              alert(response.data.message)
              console.log(response);
              this.loader = false
  
            })
            .catch(function (error) {
  
              alert(error.response.data.message);
              console.log(error);
  
            });
          this.dialogCreate = false
        
      },
  
      confirmCheckpay(id) {
        //api
        this.selectedOrder = this.ordersall[id]
        this.loader = true
        //console.log(this.selectedOrder.order_id)
        if (this.selectedOrder.order_id == null) {
          alert("Не верные данные")
          this.dialogCheckpay = false
        } else {
  
          var send = {
            p2p_ads_id: this.selectedOrder.order_id,
          }
  
          api({
            method: 'post',
            url: '/api/p2p/check-pay',
            headers: {
              'Authorization': localStorage.getItem('access_token')
            },
            data: send
          })
            .then(function (response) {
              alert(response.data.message)
              //console.log(response);
              this.loader = false
  
            })
            .catch(function (error) {
  
              alert(error.response.data.message);
              //console.log(error);
  
            });
          this.dialogCheckpay = false
        }
  
      },
      opencheckpay(id){
        this.selectedOrder = this.ordersall[id]
        var now = moment()
        //console.log(this.selectedOrder)
        
        //this.minutes = now.parseZone(moment()).utcOffset() - now.diff(moment(this.selectedOrder.history.at(-1).end_date),'minutes') - 3 * 60
        //this.minutes = now.parseZone(moment(this.selectedOrder.history.at(-1).end_date)) // тоже
        //this.minutes = now.parseZone(moment()).utcOffset() //+7 часов от утс
        //this.minutes = now.tz(now,'Europe/Moscow')
        this.minutes = now.parseZone(moment()).utcOffset() - now.diff(moment(this.selectedOrder.history.at(-1).end_date),'minutes') - 3 * 60

        this.minutescount = this.minutes - 1
        this.minutescount = String(this.minutescount).padStart(2,"0")
        
        this.dialogCheckpay = true
        
        this.intervalID = setInterval(() => {
          this.secondscount--
          this.secondscount = String(this.secondscount).padStart(2,"0")
          // //console.log(this.secondscount)
          // //console.log(this.minutes)
          if (this.secondscount == 0) {
            
            
            this.minutescount--
            this.minutescount = String(this.minutescount).padStart(2,"0")
            this.secondscount = 59

            if (this.minutescount == 0){
              clearInterval(this.intervalID);
              this.dialogCheckpay = false;
            }
            
          }
        }, 1000);
      },
      cancelCheckpay(){
        
        this.dialogCheckpay = false
        //this.secondscount = 1
        //остановить таймер
        clearInterval(this.intervalID)
  
      },
  
  
  
      confirmPayment(id){
        //api
        this.loader = true
        this.selectedOrder = this.ordersall[id]
        //console.log(this.selectedOrder)
        //console.log(this.selectedOrder.order_id)
        if (this.selectedOrder.order_id == null) {
          alert("Не верные данные")
          
        } else {
  
          var send = {
            p2p_ads_id: this.selectedOrder.order_id,
            
          }
  
          api({
            method: 'post',
            url: '/api/p2p/confirm-payment',
            headers: {
              'Authorization': localStorage.getItem('access_token')
            },
            data: send
          })
            .then(function (response) {
              alert(response.data.message)
              //console.log(response);
              this.loader = false
  
            })
            .catch(function (error) {
  
              alert(error.response.data.message);
              //console.log(error);
  
            });
          
        }
  
      },
      confirmAppeal(id){
        //api 
        this.loader = true
        this.selectedOrder = this.ordersall[id]
        //console.log(this.selectedOrder.uuid)
        //console.log(this.selectedOrder.order_id)
        if (this.selectedOrder.order_id == null) {
          alert("Не верные данные")
          
        } else {
  
          var send = {
            p2p_ads_id: this.selectedOrder.order_id,
          }
  
          api({
            method: 'post',
            url: '/api/p2p/appeal',
            headers: {
              'Authorization': localStorage.getItem('access_token')
            },
            data: send
          })
            .then(function (response) {
              alert(response.data.message)
              //console.log(response);
              this.loader = false
  
            })
            .catch(function (error) {
  
              alert(error.response.data.message);
              //console.log(error);
  
            });
          }
  
      },
  
      openaddPayment(){
        this.dialogaddPayment = true
      },
      cancelAddpayment(){
        this.dialogaddPayment = false
      },
      confirmAddpayment(){
        //api
      },
      cancelTrade(){
        var send = {
            p2p_ads_id: this.selectedOrder.order_id,
            
          }
  
          api({
            method: 'post',
            url: '/api/p2p/cancel-trade',
            headers: {
              'Authorization': localStorage.getItem('access_token')
            },
            data: send
          })
            .then(function (response) {
              alert(response.data.message)
              //console.log(response);
              this.loader = false
  
            })
            .catch(function (error) {
  
              alert(error.response.data.message);
              //console.log(error);
  
            });
      }
      
  
  
    },
  }
  </script>
  <style scoped lang="scss">
  
  .v-row {
    padding: 1px;
    margin: 1px;
    
  }

  .v-col {
    padding: 1px;
    margin: 1px;
    
  }

  .v-tabs {

    

    
    font-family: RobotoFlex;
    
    box-sizing: border-box;
    align-items: center;
    border: 1px solid #2666FF;
    
    border-radius: 10px;
    display: flex;
    justify-content: space-between;
    width: 100%;
    height: 44px;
    margin-bottom: 10px;
    

/* Auto layout */



align-items: center;


height: 30px;
border: 1px solid #2666FF;
border-radius: 8px;

/* Inside auto layout */


}

// .login:hover {
//   background: #02309F;
// }

.login:active {
  background: #02309F;
}

.text-login {
font-family: 'Roboto Slab';
font-style: normal;
font-weight: 400;
font-size: 16px;
line-height: 19px;
color: rgba(255, 255, 255, 0.8);
text-transform: capitalize;


/* Inside auto layout */

}
.theme--dark.v-tabs-items {
  background-color: transparent !important;
}

.v-select {
  
  padding-right: 0px; 
  padding-top: 0px; 
  padding-bottom: 0px;
  box-sizing: border-box;


/* Auto layout */
display: flex;
flex-direction: row;
justify-content: center;
align-items: center;

max-width: 230px;
height: 30px;
border: 1px solid #2666FF;
border-radius: 8px;
    
  }

  .v-text-field ::v-deep(.v-field__outline) {
  display: none;
  
  }

  .v-text-field ::v-deep(.v-field__overlay) {
  display: none;
    }

    
  .v-text-field ::v-deep(.v-field__field) {
  width: 220px;
    }


  .v-slide ::v-deep(.v-slide-group__container) {
    width: 50px;
  }
    

  .main-p2p{
    
    
    background: rgba(38, 102, 255, 0.1);
    border: 0.5px solid #2666FF;
    border-radius: 8px;
    display: flex;
    flex-direction: row;
    justify-content: center;
    margin-right: 15px;
    padding: 9px 16px;



  }
/* Group 39583 */
.div-circle {
width: 49px;
height: 49px;
display: flex;
  justify-content: center;
  align-items: center;  
  border-radius: 50%;
background: rgba(27, 172, 133, 0.7);
}

/* Ellipse 374 */

/* U */
.letter-in-avatar {

font-family: 'Roboto Slab';
font-style: normal;
font-weight: 800;
font-size: 24px;
line-height: 32px;
align-items: center;
text-transform: capitalize;
margin-left: 13px;
margin-top: 8px;

color: #FFFFFF;
}

.status {

width: 11px;
height: 11px;

margin-left: 30px;


border-radius: 50%;
background: #2666FF;
}

.letter-white {

font-family: 'Roboto Slab';
font-style: normal;
font-weight: 800;
font-size: 24px;
line-height: 16px;
align-items: start;
color: #FFFFFF;
}


.letter-white2 {

font-family: 'Roboto Slab';
font-style: normal;
font-weight: 800;
font-size: 24px;
align-items: center;
color: #FFFFFF;
}

.letter-blue {
  font-family: 'Roboto Slab';
font-style: normal;
font-weight: 400;
font-size: 20px;
line-height: 26px;
display: flex;
align-items: center;

color: rgba(38, 102, 255, 0.6);

}


.letter-rub {
  font-family: 'Roboto Slab';
font-style: normal;
font-weight: 300;
font-size: 16px;
line-height: 21px;

align-items: right;

color: #FFFFFF;

}

.letter-sum {
font-family: 'Roboto Slab';
font-style: normal;
font-weight: 400;
font-size: 20px;
line-height: 26px;
align-items: center;
color: #FFFFFF;
}


/* Group 39575 */

.buy-button {
width: 248px;
height: 47px;
margin-top: 35px;
background: #2666FF;
border-radius: 8px;
}

/* Купить USDT */
.buy-button-text{
font-family: 'Roboto Slab';
font-style: normal;
font-weight: 500;
font-size: 16px;
line-height: 21px;
align-items: center;
text-transform: capitalize;

color: #FFFFFF;

}


.sell-button {
width: 248px;
height: 47px;
margin-top: 35px;
background: #D34176;
border-radius: 8px;
}

/* продать */
.sell-button-text{
font-family: 'Roboto Slab';
font-style: normal;
font-weight: 500;
font-size: 16px;
line-height: 21px;
align-items: center;
text-transform: capitalize;

color: #FFFFFF;

}

.dopvcol {
  margin-top: 25px;
}
.dopvcol2 {
  margin-top: 32px;
}

.dopvcol3 {
  margin-top: 10px;
}
  
.overflowx {
  max-height: 690px;
  overflow-x: auto;
  
}
::-webkit-scrollbar{
    height: 4px;
    width: 7px;
    background: rgba(38, 102, 255, 0.1);
}

/* Track */
::-webkit-scrollbar-track {
  background: rgba(38, 102, 255, 0.1);
}

/* Handle */
::-webkit-scrollbar-thumb {
  background: #2666FF;
}

/* Handle on hover */
::-webkit-scrollbar-thumb:hover {
  background: rgba(38, 102, 255, 0.1);
}

::-webkit-scrollbar-thumb:horizontal{
  background: #2666FF;
    border-radius: 10px;
}

.selectcur {
  margin-left: 10px;
}


.openorder {
  background: rgba(38, 102, 255, 0.1);
  
  border: 0.5px solid #2666FF;
  border-radius: 8px;
  display: flex;
  flex-direction: row;
  justify-content: center;
  margin-right: 15px;
  padding: 9px 16px;
  min-width:1000px;
  min-height: 440px;
  --swiper-navigation-size: 44px;
    
}


.openorder-limit {
  /* Лимит */

margin-left: 80px;

font-family: 'Roboto Slab';
font-style: normal;
font-weight: 400;
font-size: 12px;
line-height: 16px;
/* identical to box height */
display: flex;
align-items: center;
color: rgba(255, 255, 255, 0.6);
}






  </style>