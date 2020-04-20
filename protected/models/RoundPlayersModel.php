<?php

/**
 * This is the model class for table "round_players".
 *
 * The followings are the available columns in table 'round_players':
 * @property string $id
 * @property string $round_id
 * @property integer $user_id
 * @property string $user_name
 * @property string $color
 * @property string $bet
 * @property string $chance
 * @property string $percent_start
 * @property string $percent_end
 * @property string $date_created
 * @property integer $status
 */
class RoundPlayersModel extends CActiveRecord {

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'round_players';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('round_id, user_id, color, date_created', 'required'),
            array('user_id, status', 'numerical', 'integerOnly' => true),
            array('round_id', 'length', 'max' => 20),
            array('color', 'length', 'max' => 11),
            array('bet, chance, percent_start, percent_end', 'length', 'max' => 18),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('id, round_id, user_id,user_name, color, bet, chance, percent_start, percent_end, date_created, status', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'id' => 'ID',
            'round_id' => 'Round',
            'user_id' => 'User',
            'user_name' => 'UserName',
            'color' => 'Color',
            'bet' => 'Bet',
            'chance' => 'Chance',
            'percent_start' => 'Percent Start',
            'percent_end' => 'Percent End',
            'date_created' => 'Date Created',
            'status' => 'Status',
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
    public function search() {
        // @todo Please modify the following code to remove attributes that should not be searched.

        $criteria = new CDbCriteria;

        $criteria->compare('id', $this->id, true);
        $criteria->compare('round_id', $this->round_id, true);
        $criteria->compare('user_id', $this->user_id);
        $criteria->compare('user_name', $this->user_name);
        $criteria->compare('color', $this->color, true);
        $criteria->compare('bet', $this->bet, true);
        $criteria->compare('chance', $this->chance, true);
        $criteria->compare('percent_start', $this->percent_start, true);
        $criteria->compare('percent_end', $this->percent_end, true);
        $criteria->compare('date_created', $this->date_created, true);
        $criteria->compare('status', $this->status);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return RoundPlayersModel the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

}
