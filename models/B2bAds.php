<?php

namespace app\models;

use Yii;
/**
 * @SWG\Definition()
 *
 * @SWG\Property(property="b2b_ads_id", type="integer", description="ID ордера")
 * @SWG\Property(property="uuid", type="string", description="uuid ордера")
 * @SWG\Property(property="type", type="integer", description="Покупка (2) / продажа (1)")
 * @SWG\Property(property="company_id", type="integer", description="ID компании = привязанного user_id к компании")
 * @SWG\Property(property="company", type="string", description="название компании")
 * @SWG\Property(property="b_status", type="integer", description="Статус предприятия")
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
 * @SWG\Property(property="history", type="string", description="История сделок")
 * @SWG\Property(property="count_payments_order_type", type="integer", description="Количество способов оплаты в ордере")
 * @SWG\Property(property="user_orders_count", type="integer", description="Количество исполненных ордеров пользователя на бирже")
 * @SWG\Property(property="date", type="string", description="Дата размещения на бирже")
 * @SWG\Property(property="status", type="integer", description="Статус ордера")
 * @SWG\Property(property="status_history", type="integer", description="Статус ордера в истории")
 * @SWG\Property(property="main_okved", type="string", description="Оквед ордера")
 * @SWG\Property(property="can_delete", type="integer", description="Возможность удаления ордера")
 * @SWG\Property(property="image", type="string", description="Платежный документ (при наличии оплаты)")
 * @SWG\Property(property="description", type="string", description="Условия сделки")
 * @SWG\Property(property="discount", type="string", description="% дисконт")
 */

class B2bAds extends \yii\db\ActiveRecord
{
    public $file;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'b2b_ads';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['company_id', 'chart_id', 'wallet_id', 'min_limit', 'max_limit', 'amount', 'start_amount', 'course', 'date'], 'required'],
            [['company_id', 'chart_id', 'wallet_id', 'type', 'date', 'status', 'currency_id','main_okved'], 'integer'],
            [['min_limit', 'amount','start_amount','course', 'max_limit','discount'], 'number'],
            [['description'], 'string', 'max' => 255]
            
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'company_id']);
    }

    public function getCompany()
    {
        return $this->hasOne(Company::class, ['user_id' => 'company_id']);
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
        return $this->hasMany(B2bPayment::class, ['b2b_ads_id' => 'id']);
    }
    public function getStatusType()
    {
        return $this->hasOne(StatusType::class, ['status_id' => 'status']);
    }

    public function getOkved()
    {
        return $this->hasOne(Okveds::class, ['id' => 'main_okved']);
    }

    // public function getBank()
    // {
    //     return $this->hasOne(Banks::class, ['id' => 'bank_id']);
    // }

    

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_id' => 'Company ID',
            'chart_id' => 'Chart ID',
            'currency_id' => 'Currency ID',
            'wallet_id' => 'Wallet ID',
            'type' => 'Type',
            'min_limit' => 'Min limit',
            'max_limit' => 'Max limit',
            'amount' => 'amount',
            'start_amount' => 'Start amount',
            'course' => 'Course',
            'date' => 'Date',
            'duration' => 'Duration',
            'status' => 'Status',
            'main_okved' => 'Оквед',
            'uuid' => 'uuid',
            'description' => 'Description',
            'discount' => 'Discount'
        ];
    }

}
