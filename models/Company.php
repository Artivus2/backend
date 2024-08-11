<?php

namespace app\models;

use Yii;

/**
 * @SWG\Definition(required={"id", "inn", "ogrn", "name", "address","kpp","phone","fio","status"})
 *
 * @SWG\Property(property="id", type="integer")
 * @SWG\Property(property="inn", type="string")
 * @SWG\Property(property="ogrn", type="string")
 * @SWG\Property(property="name", type="string")
 * @SWG\Property(property="address", type="string")
 * @SWG\Property(property="main_okved", type="integer")
 * @SWG\Property(property="status", type="string")
 * @SWG\Property(property="kpp", type="string")
 * @SWG\Property(property="fio", type="string")
 * @SWG\Property(property="phone", type="string")
 */
class Company extends \yii\db\ActiveRecord
{
    public $okveds;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'company';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'inn', 'ogrn', 'name', 'address','phone','kpp','fio'], 'required'],
            [['user_id','main_okved'], 'integer'],
            [['inn','phone'], 'string', 'min' => 10],
            [['ogrn'], 'string', 'min' => 13],
            [['name', 'address'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'inn' => 'Inn',
            'ogrn' => 'Ogrn',
            'name' => 'Name',
            'address' => 'Address',
            'main_okved' => 'Okved',
            'kpp' => 'КПП',
            'fio' => 'ФИО',
            'phone' => 'Телефон',
            // 'bank' => 'Банк',
            // 'bik' => 'БИК',
            // 'rs' => 'Расч счет',
            // 'ks' => 'Корр счет'
        ];
    }

    public function getOkved()
    {
        return $this->hasOne(Okveds::class, ['id' => 'main_okved']);
    }

    public function getBankList()
    {
        return $this->hasMany(B2bPayment::class, ['user_id' => 'company_id']);
    }


}
