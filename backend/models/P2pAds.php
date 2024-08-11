<?php

namespace app\models;

use Yii;
/**
 * @SWG\Definition()
 *
 * @SWG\Property(property="p2p_ads_id", type="integer", description="ID ордера")
 * @SWG\Property(property="uuid", type="string", description="uuid ордера")
 * @SWG\Property(property="type", type="integer", description="Покупка (2) / продажа (1)")
 * @SWG\Property(property="user_id", type="integer", description="ID пользователя")
 * @SWG\Property(property="user", type="string", description="Логин пользователя")
 * @SWG\Property(property="first_name", type="string", description="Фамилия пользователя")
 * @SWG\Property(property="last_name", type="string", description="Имя пользователя")
 * @SWG\Property(property="patronymic", type="string", description="Отчество пользователя")
 * @SWG\Property(property="verify_status", type="integer", description="Статус верификации")
 * @SWG\Property(property="chart_id", type="integer", description="Идентификатор используемой криптовалюты")
 * @SWG\Property(property="chart", type="string", description="Наименование используемой криптовалюты")
 * @SWG\Property(property="currency_id", type="integer", description="Идентификатор используемой валюты")
 * @SWG\Property(property="currency", type="string", description="Наименование используемой валюты")
 * @SWG\Property(property="course", type="number", description="Курс")
 * @SWG\Property(property="start_amount", type="number", description="Общая сумма ордера")
 * @SWG\Property(property="amount", type="number", description="Оставшаяся сумма ордера")
 * @SWG\Property(property="min_limit", type="number", description="MIN лимит")
 * @SWG\Property(property="max_limit", type="number", description="MAX лимит")
 * @SWG\Property(property="duration", type="integer", description="Период оплаты")
 * @SWG\Property(property="payments", type="string", description="Способ(ы) оплаты")
 * @SWG\Property(property="history", type="string", description="История сделок")
 * @SWG\Property(property="count_payments_order_type", type="integer", description="Количество способов оплаты в ордере")
 * @SWG\Property(property="user_orders_count", type="integer", description="Количество исполненных ордеров пользователя на бирже")
 * @SWG\Property(property="date", type="string", description="Дата размещения на бирже")
 * @SWG\Property(property="status", type="integer", description="Статус ордера")
 * @SWG\Property(property="status_history", type="integer", description="Статус ордера в истории")
 * @SWG\Property(property="can_delete", type="integer", description="Возможность удаления ордера")
 * @SWG\Property(property="description", type="string", description="Условия сделки") 
 */

class P2pAds extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'p2p_ads';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'chart_id', 'wallet_id', 'min_limit', 'max_limit', 'amount', 'start_amount', 'course', 'date','duration'], 'required'],
            [['user_id', 'chart_id', 'wallet_id', 'type', 'date', 'status', 'currency_id'], 'integer'],
            [['min_limit', 'amount','start_amount','course', 'max_limit'], 'number'],
            [['description'], 'string', 'max' => 255]
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getCurrency()
    {
        return $this->hasOne(Currency::class, ['id' => 'currency_id']);
    }

    public function getChart()
    {
        return $this->hasOne(Chart::class, ['id' => 'chart_id']);
    }

    public function getWallet()
    {
        return $this->hasOne(Wallet::class, ['id' => 'wallet_id']);
    }

    public function getPayments()
    {
        return $this->hasMany(P2pPayment::class, ['p2p_ads_id' => 'id']);
    }
    public function getStatusType()
    {
        return $this->hasOne(StatusType::class, ['status_id' => 'status']);
    }

    

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'id Пользователя',
            'chart_id' => 'Криптовалюта',
            'currency_id' => 'Валюта',
            'wallet_id' => 'Wallet ID',
            'type' => 'Тип',
            'min_limit' => 'Мин Лимит',
            'max_limit' => 'Макс Лимит',
            'amount' => 'Остаток',
            'start_amount' => 'Общий обьем',
            'course' => 'Курс',
            'date' => 'Дата',
            'duration' => 'Период',
            'status' => 'Статус',
            'uuid' => 'uuid',
            'description' => 'Description'
        ];
    }
}
