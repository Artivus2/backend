<?php

namespace app\models;

use Yii;

/**
 * @SWG\Definition(required={"login", "email"})
 *
 * @SWG\Property(property="id", type="integer", description="Идентификатор")
 * @SWG\Property(property="email", type="string", description="Электронная почта", example="mail@mail.com")
 * @SWG\Property(property="login", type="string", description="login", example="fancy_login")
 * @SWG\Property(property="currency_id", type="integer", description="Идентификатор используемой валюты")
 * @SWG\Property(property="chart_id", type="integer", description="Идентификатор используемой криптовалюты")
 * @SWG\Property(property="verify_status", type="integer")
 * @SWG\Property(property="image", type="file")
 * @SWG\Property(property="telegram", type="string")
 * @SWG\Property(property="token", type="string")
 * @SWG\Property(property="first_name", type="string", description="Имя", example="Иван")
 * @SWG\Property(property="last_name", type="string", description="Фамилия", example="Иванов")
 * @SWG\Property(property="patronymic", type="string", description="Отчество", example="Иванович")
 * @SWG\Property(property="avatar", type="integer", description="Аватар")
 * @SWG\Property(property="last_ip", type="string", description="IP Адрес", example="102.1.34.543")
 */
class User extends \yii\db\ActiveRecord implements \yii\web\IdentityInterface
{

    public $image;

    public $currentToken;

    public function behaviors()
    {
        return [
            'image' => [
                'class' => 'rico\yii2images\behaviors\ImageBehave',
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['password', 'login'], 'required'],
            ['login', 'unique'],
            ['email', 'unique'],
            //['phone', 'validatePhone'],
            // ['phone', 'match', 'pattern' => '/^\+7\([0-9]{3}\)\s[0-9]{3}\-[0-9]{2}\-[0-9]{2}$/', 'message' => 'Пример: +7(999) 999-99-99'],
            [['currency_id', 'verify_status', 'chart_id','avatar'], 'integer'],
            [['image'], 'file', 'extensions' => 'png, jpg, jpeg'],
            [['email', 'password', 'token', 'telegram', 'first_name', 'last_name', 'patronymic','last_ip'], 'string', 'max' => 255],
        ];
    }

    public function validatePhone($attribute, $params)
    {
        if (!$this->hasErrors()) {
            if($this->phone) {
                $phone = preg_replace('/[^0-9]/', '', $this->phone);
                $check = User::findOne(["phone" => $phone]);
                if(isset($check) && $check->id != $this->id) return $this->addError($attribute, 'Номер телефона занят!');
            }
        }
    }
    public function afterFind()
    {
        if($this->phone) $this->phone = $this->maskPhone($this->phone);
    }

    static function maskPhone($number) {
        return sprintf("+%s(%s) %s-%s-%s",
            substr($number, 0, 1),
            substr($number, 1, 3),
            substr($number, 4, 3),
            substr($number, 7, 2),
            substr($number, 9)
        );
    }

    public function beforeSave($insert)
    {
        $this->phone = preg_replace('/[^0-9]/', '', $this->phone);

        if (parent::beforeSave($insert)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'phone' => 'Номер телефона',
            'email' => 'Электронная почта',
            'password' => 'Пароль',
            'image' => 'Аватар',
            'login' => 'Логин',
            'telegram' => 'Телеграм',
            'chart_id' => 'Криптовалюта',
            'currency_id' => 'Валюта',
            'verify_status' => 'Верификация',
            'token' => 'Token',
            'created_at' => 'Дата создания',
            'last_ip' => 'Последний IP адрес',
        ];
    }

    public function getChart()
    {
        return $this->hasOne(Chart::class, ['id' => 'chart_id']);
    }

    public function getAffiliates()
    {
        return $this->hasMany(Affiliate::class, ['user_id' => 'id']);
    }


    public function getCurrency()
    {
        return $this->hasOne(Currency::class, ['id' => 'currency_id']);
    }

    public function getCompany()
    {
        return $this->hasMany(Company::class, ['user_id' => 'id']);
    }

    public function getWallet()
    {
        return $this->hasMany(Wallet::class, ['user_id' => 'id']);
    }

    public function getVerify()
    {
        return $this->hasMany(UserVerify::class, ['user_id' => 'id']);
    }
    
    public function getimages()
    {
        return $this->hasMany(Image::class, ['itemId' => 'id']);
    }

    public function getAuthTokens()
    {
        return $this->hasMany(AuthToken::class, ['user_id' => 'id']);
    }

    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        if ($token === null)
            return null;

        $authToken = AuthToken::find()->where(['token' => $token])->andWhere(['>', 'expired_at', time()])->one();
        if (!$authToken) {
            return null;
        }

        return $authToken;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAuthKey()
    {
        return $this->token;
    }

    public function validateAuthKey($authKey)
    {
        return $this->token === $authKey;
    }

    public function validatePassword($password)
    {
        return Yii::$app->getSecurity()->validatePassword($password, $this->password);
    }

  	public function upload()
    {
  			$path = 'uploads/' . $this->image->baseName . '.' . $this->image->extension;
  			$this->image->saveAs($path);
  			$this->attachImage($path);
  			@unlink($path);
  	}

    public function setVerificationCode($code)
    {
        $this->verification_code = $code;
    }

    
    public function setVerificationCodeExpiration($time)
    {
        $this->verification_code_expiration = $time;
    }

    public function removeVerificationCode()
    {
        $this->verification_code = null;
        $this->verification_code_expiration = null;
    }

    public function validateVerificationCode($code)
    {
        return $this->verification_code == $code && $this->verification_code_expiration > time();
    }

    public function setToken($fcmToken) {
        $date = new \DateTime();
        $date->modify("+1440 minutes");

        $authToken = new AuthToken();
        $authToken->user_id = $this->id;
        $authToken->token = Yii::$app->security->generateRandomString();
        $authToken->fcm_token = !is_null($fcmToken) ? $fcmToken : null;
        $authToken->expired_at = $date->getTimestamp();
        $authToken->save();
        return $authToken;
    }

    public function block() {
        $date = new \DateTime();
        $date->modify("+30 minutes");
        $this->blocked_before = $date->getTimestamp();
        $this->save();
        AuthToken::deleteAll(['user_id' => $this->id]);
    }
}
