<?php

/**
 * This is the model class for table "friends".
 *
 * The followings are the available columns in table 'friends':
 * @property string $id
 * @property integer $user1
 * @property integer $user2
 * @property integer $source
 * @property string $created_at
 */
class Friends extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'friends';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user1, user2, source', 'numerical', 'integerOnly'=>true),
			array('created_at, last_balance_change', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, user1, user2, source, created_at', 'safe', 'on'=>'search'),
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
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'user1' => 'User1',
			'user2' => 'User2',
			'source' => 'Source',
			'created_at' => 'Created At',
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
		$criteria->compare('user1',$this->user1);
		$criteria->compare('user2',$this->user2);
		$criteria->compare('source',$this->source);
		$criteria->compare('created_at',$this->created_at,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Friends the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public static function searchMy($name) {



        //preg_replace("/([^a-zA-ZА-Яа-яЁё0-9\s0-9\-]+)/","",$name);


        if(strlen($name) < 1) return [];

        //$name = strtolower($name);

        $friends = Friends::model()->findAll([
            'select'=>'user1, user2, balance, last_balance_change',
            'condition'=>'(user1 = :id and user2 <> 0) or (user2 = :id and user1 <> 0)',
            'params' => [':id' => Yii::app()->user->id]
        ]);

        $friendsIds = [];
        foreach ($friends as $_friend) {
            if($_friend->user1 != Yii::app()->user->id) $friendsIds[] = $_friend->user1;
            else $friendsIds[] = $_friend->user2;
        }

        $name = addcslashes($name,'%_');

        $criteria = new CDbCriteria();
        $criteria->select = 'id, firstname, lastname, avatar_id';
        $criteria->addInCondition('id',$friendsIds);
        $criteria->addCondition('LOWER(firstname) like :name or LOWER(lastname) like :name2');
        $criteria->params[':name'] = '%'.$name.'%';
        $criteria->params[':name2'] = '%'.$name.'%';
        /*
        $users = User::model()->findAll([
            'select' => 'id, firstname, lastname, avatar_id',
            //'condition' => 'id in (:ids) and (LOWER(firstname) like :name  or LOWER(lastname) like :name )',
            'condition' => 'id in (:ids)',
            'params'=>[':ids'=>implode(',',$friendsIds)]
//            'params'=>[':ids'=>implode(',',$friendsIds), ':name' => '%'.$name.'%']
        ]); */
        $users = User::model()->findAll($criteria);



        /*$usersArray = [];

        foreach ($users as $_user) {

            $item = [
                'id' => $_user->id,
                'firstname' => $_user->firstname,
                'lastname' => $_user->lastname,
                'avatar'=> $_user->getAvatar(false,'_small',true),
                'avatar_html' => $_user->hasAvatar() ? $_user->getAvatar(false,'_small',false) : $_user->getCavatar(),
            ];

            $usersArray[] = $item;
        }*/


        return $users;
    }

    public function getBalance($user_id = 0) {
	    if($user_id == 0) $user_id = Yii::app()->user->id;

	    if($this->user1 == $user_id) return $this->balance;
	    else return -$this->balance;
    }

    public static function getFriendModel($user1, $user2) {
        return Friends::model()->find('(user1 = :user1 and user2 = :user2) or (user1 = :user2 and user2 = :user1)',
            [':user1' => $user1, ':user2' => $user2]);
    }

    public static function parseFriendsIds($friends, $me, $scopes = []) {
	    $ids = [];
	    foreach ($friends as $_friend) {
	        if($_friend->user1 == $me) $ids[] = $_friend->user2;
	        else $ids[] = $_friend->user1;
        }

        if(isset($scopes['withMe']) && $scopes['withMe']) $ids[] = $me;


        return $ids;
    }
}
