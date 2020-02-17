<?php

/**
 * This is the model class for table "user".
 *
 * The followings are the available columns in table 'user':
 * @property string $id
 * @property string $username
 * @property string $password
 * @property string $salt
 * @property string $activationKey
 * @property integer $createtime
 * @property integer $lastvisit
 * @property integer $lastaction
 * @property integer $lastpasswordchange
 * @property integer $superuser
 * @property integer $status
 * @property integer $avatar_id
 * @property integer $is_activated
 *
 * The followings are the available model relations:
 * @property PrivacyField[] $privacyFields
 */
class User extends CActiveRecord
{
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_BANNED = -1;
    const STATUS_REMOVED = -2;

    public $new_password;
    public $new_password2;

    public $friends_cache = [];

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'user';
	}

    public function afterFind()
    {

        $this->birth=date('d.m.Y',strtotime($this->birth));

        return parent::afterFind();
    }

    public function beforeSave()
    {
        $this->birth=date('Y-m-d',strtotime($this->birth));

        if(strlen($this->username)==0) {
            $this->username=null;
        }

        if($this->isNewRecord) {
            $ip = self::GetRealIp();
            $this->register_ip = $ip;
        }

        return parent::beforeSave();
    }
	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('password, salt', 'required','on'=>'register'),
			//array('createtime, lastvisit, lastaction, lastpasswordchange, superuser, status, avatar_id, is_activated', 'numerical', 'integerOnly'=>true),
			array('username', 'length', 'max'=>60),
            array('username', 'match', 'pattern' => '/^[A-z0-9]+$/u', 'message' => '{attribute} может содержать только английские буквы и цифры'),

            array('firstname, lastname', 'match', 'pattern' => '/^[A-zА-яЁё\s]+$/u', 'message' => 'Имя может содержать только русские и английские буквы'),
            array('name', 'match', 'pattern' => '/^[A-zА-яЁё\s\d]+$/u', 'message' => 'Имя может содержать только русские и английские буквы'),

            array('email','email'),

			array('password, salt, bgimg,birth', 'length', 'max'=>128),
			array('last_login_ip, register_ip', 'length', 'max'=>20),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.

            array('status', 'in', 'range' => array(0, 1, 2, 3, -1, -2)),
            array('superuser', 'in', 'range' => array(0, 1, 2)),
            array('gender', 'in', 'range' => array(0, 1, 2)),
            array('createtime, lastpasswordchange, superuser, status', 'required'),
            array('avatar', 'safe'),
            array('password', 'required', 'on' => array('insert', 'register')),
            array('salt', 'required', 'on' => array('insert', 'register')),
            array('createtime, lastvisit, lastpasswordchange, lastaction, superuser, status, referral_id, email_confirmed', 'numerical', 'integerOnly' => true),

            array('username', 'username', 'setOnEmpty' => true, 'value' => null),
            array('username', 'unique'),
            array('email', 'unique'),

            array('new_password, new_password2', 'required' ,'on'=>'passwordupdate'),
            array('new_password2', 'compare', 'compareAttribute'=>'new_password', 'message'=>'Подтверждение пароля не совпадает'),

			array('id, username, password, salt, createtime, lastvisit, lastaction, lastpasswordchange, superuser, status, avatar_id, is_activated', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			//'privacyFields' => array(self::MANY_MANY, 'PrivacyField', 'profile_privacy(user_id, privacy_field_id)'),
            'videos'=>array(self::HAS_MANY,'YumProfileVideo','user_id'),
            'profile'=>array(self::HAS_ONE,'Profile','user_id'),
            'profile_detail'=>array(self::HAS_ONE,'ProfileDetail','user_id'),
            'profile_contact'=>array(self::HAS_MANY,'ProfileContact','user_id'),
            'avatar'=>[self::BELONGS_TO,'Avatars','avatar_id']
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'username' => 'Адрес профиля',
			'password' => 'Пароль',
			'salt' => 'Salt',
			'activationKey' => 'Activation Key',
			'createtime' => 'Createtime',
			'lastvisit' => 'Lastvisit',
			'lastaction' => 'Lastaction',
			'lastpasswordchange' => 'Lastpasswordchange',
			'superuser' => 'Superuser',
			'status' => 'Status',
			'avatar_id' => 'Avatar',
			'is_activated' => 'Is Activated',
            'gender'=>'Пол',
            'birth'=>'День рождения',
            'firstname'=>'Имя',
            'lastname'=>'Фамилия',
            'name'=>'Отображаемое имя'
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id,true);
		$criteria->compare('username',$this->username,true);
		$criteria->compare('password',$this->password,true);
		$criteria->compare('salt',$this->salt,true);
		//$criteria->compare('activationKey',$this->activationKey,true);
		$criteria->compare('createtime',$this->createtime);
		$criteria->compare('lastvisit',$this->lastvisit);
		$criteria->compare('lastaction',$this->lastaction);
		$criteria->compare('lastpasswordchange',$this->lastpasswordchange);
		$criteria->compare('superuser',$this->superuser);
		$criteria->compare('status',$this->status);
		$criteria->compare('avatar_id',$this->avatar_id);
		$criteria->compare('is_activated',$this->is_activated);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return User the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    public function username($attribute, $params=array())
    {
        $urls = array('user','areas', 'bank', 'wacky','hyena','wackyhyena', 'fields', 'market', 'mercenaries', 'neighbors', 'site', 'users', 'news', 'rules',
            'statistic', 'help', 'reg','auth','recovery','site','feed', 'login','upload', 'register', 'recovery', 'settings', 'logout', 'matrix', 'blog', 'admin','administrator','manager','blog');

        $this->username=strtolower($this->username);

        $value = $this->$attribute;

        if(!$value) return;

        if(strlen($value)<6) {
            $this->addError($attribute, 'Короткий адрес должен иметь минимум 6 символов');
            return;
        }

        if (!preg_match('/^([A-z\d]+)$/u', $value))
        {
            $this->addError($attribute, 'Недопустимые символы');
            return;
        }

        if(preg_match('/^u([0-9]+)$/', $value) && !$this->isNewRecord)
            $this->addError($attribute, 'недопустимый адрес');
        elseif(in_array($value, $urls))
            $this->addError($attribute, 'данный адрес занят');
    }

    public function logout()
    {
        if (!Yii::app()->user->isGuest) {
            $this->lastaction = time();
            $this->save('lastaction');
        }
    }

    public function isActive()
    {
        return $this->status == YumUser::STATUS_ACTIVE;
    }

    public function setLastAction()
    {
        if (!Yii::app()->user->isGuest && !$this->isNewRecord) {

            if(isset(Yii::app()->session['last_action']) && Yii::app()->session['last_action'] == time()) {
                if(isset(Yii::app()->session['request'])) {
                    Yii::app()->session['request'] += 1;
                } else {
                    Yii::app()->session['request'] = 1;
                }
            } else {
                Yii::app()->session['request'] = 1;
            }
            Yii::app()->session['last_action'] = time();



            //now check actual
            $cc=Yii::app()->request->cookies['last_action'];
            if($cc) $cc=$cc->value;
            else
            {
                $cc=0;
                Yii::app()->request->cookies['last_action'] = new CHttpCookie('last_action', 0);
            }
            if((time()-$cc)>300)
            {

                Yii::app()->request->cookies['last_action']=new CHttpCookie('last_action', time());

                $this->lastaction = time();
                return $this->save(false, array('lastaction'));
            }
        }
    }

    public function setPassword($password, $salt = null)
    {
        if ($password != '') {
            $this->password = CEncrypt::encrypt($password, $salt);
            $this->lastpasswordchange = date('Y-m-d H:i:s');

            $this->salt = $salt;
            if (!$this->isNewRecord)
                return $this->save(false,['password','salt','lastpasswordchange']);
            else
                return $this;
        }
    }

    public function getBalance()
    {
        return $this->profile_status->balance;
    }

    public function getMiniAvatar()
    {
        return $this->getAvatar(true,'_small',true);
    }

    public function getAvatarAuto($_cavatar_text = false) {
	    if($this->hasAvatar()) return $this->getAvatar(true, '_small', false);
	    else return $this->getCavatar($_cavatar_text);
    }

    public function hasAvatar() {
	    return $this->avatar_id;
    }

    public function getCavatar($text = false) {

	    if(!$text) $text = mb_substr($this->firstname,0,1,'UTF-8') . mb_substr($this->lastname,0,1,'UTF-8');

	    return '<div class="profile-img-circle"><span>'
            .$text.
            '</span></div>';
    }

    public function getAvatar($thumb = false, $thumb_type = 'none',$path_only=false)
    {
        $no_ava_path=$thumb ? '/static/img/logo@2x.png' : '/static/img/logo@2x.png';
        $thumbHtml = $path_only ? $no_ava_path : CHtml::image($no_ava_path);

        if(!$this->avatar_id)
            return $thumbHtml;

        $model = Yii::app()->cache->get('user_avatar_'.$this->id);
        if($model==false){
            //$model = Avatars::model()->findByPk($this->avatar_id);
            $model = $this->avatar;

            if($model==null) return $no_ava_path;
            Yii::app()->cache->set('user_avatar_'.$this->id, $model);
        }


        if($thumb && in_array($thumb_type, array('_big', '_medium', '_small')))
        {
           $path = Yii::app()->baseUrl . '/avatars/u' . $this->id . '/' . md5($model->id . '_' . $model->src . $thumb_type) . '.jpg';
        }
        else
            $path = Yii::app()->baseUrl . '/' . $model->src;

        if($model->absolute_url==1)
            $path=$model->src;

        if($path_only) return $path;

        return CHtml::image($path);
    }

    public function getUrl()
    {
        if($this->username)
            return '/'.$this->username;//Yii::app()->createUrl('/user/view', array('username'=>$this->username));
        else
            return Yii::app()->createUrl('/user/view', array('id'=>$this->id));
    }

    public function isOnline()
    {
        return $this->lastaction > (time() - 900);
    }

    public function getOnlineStatus()
    {
        if($this->isOnline()) return 'Online';

        if($this->lastaction < (time()-7776000)) return '';

        if($this->lastaction!=0){
            $day='d MMMM';
            if(Yii::app()->dateFormatter->format('dMy',$this->lastaction)==Yii::app()->dateFormatter->format('dMy',time()))
                $day='\'сегодня\'';
            elseif(Yii::app()->dateFormatter->format('dMy',$this->lastaction)==Yii::app()->dateFormatter->format('dMy',strtotime('-1 day')))
                $day='\'вчера\'';
            $the_date=Yii::app()->dateFormatter->format($day.' в HH:mm',$this->lastaction);
            //$the_date=Yii::app()->dateFormatter->format('dMy',$model->lastaction);
        } else $the_date='';


        if($this->gender==1) $pre='заходила';
        else $pre='заходил';


        return $pre.' '.$the_date;

    }


    public function getName() {
	    return $this->name();
    }

    public function name()
    {
        //if($this->name) return $this->name;
        return $this->firstname.' '.$this->lastname;
    }

    public function link()
    {
        if($this->username&&strlen($this->username)>1)
        {
            return '/'.$this->username;
        }
        else
        {
            return '/u'.$this->id;//array('user/view','id'=>$this->id);
        }
    }

    public function getUser($id)
    {
        $cache_id = 'user_' . $id;
        $data = Yii::app()->cache->get($cache_id);
        if ($data == false) {
            //$data = YumUser::model()->with('profile')->findByPk((int) $id);
            $data = $this->with('profile')->findByPk($id);

            if ($data)
                Yii::app()->cache->set($cache_id, $data, 600);
        }

        //Yii::app()->cache->delete($cache_id);

        return $data;
    }

    public function subscribeTo($user_id)
    {
        //validation
        $user_id=intval($user_id);
        if($user_id==$this->id) return false;
        $user = User::model()->findByPk($user_id);
        if($user==null) return false;
        $subscribe = Subscribers::model()->findByAttributes(['user_id'=>$user_id,'subscriber_id'=>$this->id]);
        if($subscribe!=null) return false;
        $subscribe = new Subscribers();
        $subscribe->user_id=$user->id;
        $subscribe->subscriber_id=$this->id;
        if($subscribe->save()) {
            $pd = $user->profile;
            $pd->saveCounters(['subscribers_count'=>1]);
        }
        return $pd;
    }

    public function unsubscribeFrom($user_id)
    {
        //validation
        $user_id=intval($user_id);
        if($user_id==$this->id) return false;
        $user = User::model()->findByPk($user_id);
        if($user==null) return false;
        $subscribe = Subscribers::model()->findByAttributes(['user_id'=>$user_id,'subscriber_id'=>$this->id]);
        if($subscribe==null) return false;
        $subscribe->delete();
        $pd = $user->profile;
        $pd->saveCounters(['subscribers_count'=>-1]);
        return $pd;
    }

    public function isFollowing()
    {
        $subscribe = Subscribers::model()->findByAttributes(['user_id'=>$this->id,'subscriber_id'=>Yii::app()->user->id]);
        return $subscribe!=null;
    }

    public function sendConfirmEmail()
    {
        $message = new YiiMailMessage;
        $url = 'https://numcheck.ru/user/confirm/id/'.$this->id.'/code/'.md5($this->email.$this->id.'wh@@x'.$this->id);
        $message->view = 'welcome';
        $message->setBody('<h3>Завершение регистрации</h3><p>Пожалуйста, подтвердите вашу почту, пройдя по ссылке <a href="'.$url.'">'.$url.'</a>', 'text/html');
        $message->subject = 'Завершение регистрации на NumCheck.ru';

        $message->addTo($this->email);
        $message->setFrom(array('welcome@numcheck.ru'=>'NumCheck'));

        Yii::app()->mail->send($message);
    }

    public function addRating($val)
    {
        return $this->profile->addRating($val);
    }

    public function getBgImage()
    {
        if($this->bgimg) {
            $path = '/files/'.$this->id.'/'.$this->bgimg;
        } else {
            $path='/images/bg_empty.jpg';
        }

        return $path;
    }

    public static function getTopUsers() {
        $criteria = new CDbCriteria();
        $criteria->order='profile.posts_count DESC';
        $criteria->join='left join profile on profile.user_id=t.id ';
        $criteria->condition='rating>0 and posts_count>0 and is_mc=1';
        $criteria->params[':uid']=Yii::app()->user->id;
        $criteria->limit=10;
        $users = User::model()->findAll($criteria);

        return $users;
    }

    public function isFriend($user_id) {
	    return Friends::model()->exists('(user1 = :user1 and user2 = :user2) or (user1 = :user2 and user2 = :user1)',
            [':user1' => $user_id, ':user2' => $this->id]);
    }

    public function setFriendModel($friend) {
	    $user_id = $friend->user1 == $this->id ? $friend->user2: $friend->user1;

	    $this->friends_cache[$user_id] = $friend;
    }

    public function getFriendModel($user_id) {
	    if(isset($this->friends_cache[$user_id])) return $this->friends_cache[$user_id];

        $friend = Friends::getFriendModel($user_id, $this->id);
        $this->setFriendModel($friend);
        return $friend;
    }

    public function getOverallBalance() {

	    $user_id = intval($this->id);

	    $to_me = Yii::app()->db->createCommand('select sum(balance) as total from friends where user1 = '.$user_id)->queryRow();
	    $from_me = Yii::app()->db->createCommand('select sum(balance) as total from friends where user2 = '.$user_id)->queryRow();

	    return $to_me['total'] - $from_me['total'];
    }

    public function getFriends($ids) {

    }

    public function getAllFriends() {
        $usersIds = [];
        $friends = Friends::model()->findAll([
            'select'=>'user2, user1, balance, last_balance_change',
            'condition'=>'user2 = :id or user1 = :id',
            'params'=>[':id'=>$this->id]
        ]);

        $friendsById = [];

        foreach ($friends as $_friend) {
            if($_friend->user1 == $this->id) $user_id = $_friend->user2;
            else $user_id = $_friend->user1;

            $usersIds[] = $user_id;
            $friendsById[$user_id] = $_friend;
        }
        unset($friends);

        $criteria = new CDbCriteria;
        $criteria->addInCondition('id',$usersIds);
        $criteria->select = 'firstname, lastname, id, avatar_id';

        $users = User::model()->findAll($criteria);

        foreach ($users as &$_user) {
            $_user->setFriendModel($friendsById[$_user->id]);
        }

        return $users;
    }

    public function getPopularFriends() {

	    $query = 'select count(from_user) as total, from_user from transactions where to_user = '.intval($this->id).' group by from_user';
	    $res = Yii::app()->db->createCommand($query)->queryAll();

	    $ids = [];
	    foreach ($res as $_res) {
	        $ids[$_res['from_user']] = [$_res['total'], $_res['from_user']];
        }

	    $query = 'select count(to_user) as total, to_user from transactions where from_user = '.intval($this->id).' group by to_user';
	    $res = Yii::app()->db->createCommand($query)->queryAll();

	    foreach ($res as $_res) {
	        if(isset($ids[$_res['to_user']])) {
                $ids[$_res['to_user']][0] += $_res['total'];
            } else {
                $ids[$_res['to_user']] = [$_res['total'], $_res['to_user']];
            }
        }

        //sort
        $sorted = [];

        foreach ($ids as $_key => $_val) {
            $sorted[$_key] = $_val[0];

        }
        array_multisort($sorted, SORT_DESC, $ids);

        $idsAmount = array_slice($ids, 0, 3);

        $usersIds = [];
        foreach ($idsAmount as $item) {
            $usersIds[] = $item[1];
        }

        $criteria = new CDbCriteria;
        $criteria->addInCondition('id',$usersIds);
        $criteria->select = 'firstname, lastname, id, avatar_id';

        $users = User::model()->findAll($criteria);

        return $users;
    }

    public function countUnconfirmedIncomingChecks() {

        $criteria = new CDbCriteria;
        $criteria->select = 'id';
        $criteria->addCondition('to_user = :user and status = 0');
        $criteria->params[':user'] = Yii::app()->user->id;

        $transactions = Transactions::model()->findAll($criteria);

        return count($transactions);
    }

    public function me($t = false) {
	    return [
            'avatar_html'=>$this->getAvatarAuto($t),
            'firstname'=>$this->firstname,
            'lastname'=>$this->lastname,
            'id'=>$this->id,
        ];
    }

    public static function toArray($users, $scopes = []) {

	    $usersArr = [];
        foreach ($users as $_user) {

            $item = [
                'avatar_html'=>$_user->getAvatarAuto(),
                'firstname'=>$_user->firstname,
                'lastname'=>$_user->lastname,
                'id'=>$_user->id,
            ];

            if(isset($scopes['withFriend'])) {
                $friend = $_user->getFriendModel($scopes['withFriend']);
                $item['last_balance_change'] = $friend->last_balance_change == '' ? '' : date('d.m.Y H:i',strtotime($friend->last_balance_change));
                $item['balance'] = round($friend->getBalance());
            }

            $usersArr[] = $item;
        }

        return $usersArr;


    }

    public static function GetRealIp(){
	    if(!empty($_SERVER['HTTP_CLIENT_IP'])){$ip=$_SERVER['HTTP_CLIENT_IP'];}elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){$ip=$_SERVER['HTTP_X_FORWARDED_FOR'];}else{$ip=$_SERVER['REMOTE_ADDR'];}return $ip;
	}
}
