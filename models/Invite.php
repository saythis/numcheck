<?php

/**
 * This is the model class for table "invite".
 *
 * The followings are the available columns in table 'invite':
 * @property string $id
 * @property integer $user_id
 * @property string $code
 * @property integer $is_activated
 * @property string $created_at
 * @property string $activated_at
 */
class Invite extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'invite';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, activated_by, is_activated, connected_to', 'numerical', 'integerOnly'=>true),
			array('code', 'length', 'max'=>255),
			array('created_at, activated_at', 'safe'),
            array('name', 'match', 'pattern' => '/^[A-zА-яЁё\s\d]+$/u', 'message' => 'Имя может содержать только русские и английские буквы'),

            // The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, user_id, code, is_activated, created_at, activated_at', 'safe', 'on'=>'search'),
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
            'check' => [self::BELONGS_TO,'Check','connected_to'],

        );
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'user_id' => 'User',
			'code' => 'Code',
			'is_activated' => 'Is Activated',
			'created_at' => 'Created At',
			'activated_at' => 'Activated At',
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
		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('code',$this->code,true);
		$criteria->compare('is_activated',$this->is_activated);
		$criteria->compare('created_at',$this->created_at,true);
		$criteria->compare('activated_at',$this->activated_at,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Invite the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public static function getEmptyInvite($name = '') {

	    $criteria = new CDbCriteria();
	    $criteria->addCondition('connected_to = 0 and date(created_at) < date(:date) and is_activated = 0 and user_id = :id');
	    $criteria->params[':date'] = date('Y-m-d',time()-1*24*3600);
	    $criteria->params[':id'] = Yii::app()->user->id;

        $invite = Invite::model()->find($criteria);

        if($invite) {
            $invite->created_at = date('Y-m-d');
            $invite->save(false,['created_at']);
            return $invite;
        }

        return self::createNew($name);
    }

	public static function createNew($name = '') {

        $invites = Invite::model()->countByAttributes(['user_id'=>Yii::app()->user->id,'is_activated'=>0]);

        if($invites < 15) {

            while (1) {
                $code = md5(time() . Yii::app()->user->id . mt_rand(1, 999));
                $code = substr($code, mt_rand(1, 15), 12);

                $isExist = Invite::model()->exists('code = :code', [':code' => $code]);
                if (!$isExist) {

                    $inv = new Invite;
                    $inv->user_id = Yii::app()->user->id;
                    $inv->created_at = new CDbExpression('NOW()');
                    $inv->is_activated = 0;
                    //$inv->code = md5(time() . $inv->user_id . 'xx');
                    $inv->code = $code;
                    $inv->name = $name;
                    $inv->connected_to = 0;
                    $inv->save();

                    return $inv;

                    break;
                }
            }
        } else {
            return false;
        }
    }

    public function activate($user_id) {

	    if($this->is_activated == 1) return false;

	    $this->is_activated = 1;
	    $this->activated_by = $user_id;
	    $this->activated_at = new CDbExpression('NOW()');
	    $this->save();

	    $check = Check::model()->findByPk($this->connected_to);
	    if($check && $check->is_deleted != 1) {
	        $check->moveMember($this);
        }

        $friend = Friends::getFriendModel($this->user_id, $user_id);
	    if($friend) return true;

        $friends = new Friends();
	    $friends->user1 = $this->user_id;
	    $friends->user2 = $user_id;
	    $friends->source = 1;
	    $friends->created_at = new CDbExpression('NOW()');
	    $friends->balance = 0;
	    return $friends->save();
    }

    public function getUser() {

	    if($this->activated_by)
	        return User::model()->findByPk($this->activated_by);

	    else return false;
    }

}
