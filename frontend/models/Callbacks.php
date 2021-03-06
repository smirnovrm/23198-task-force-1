<?php

namespace frontend\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "callbacks".
 *
 * @property int $id
 * @property int $task_id id задачи
 * @property int $freelancer_id id исполнителя
 * @property string|null $text Текст отклика
 * @property int $price Цена исполнителя за работу
 *
 * @property-read  Users $freelancer
 * @property-read  Tasks $task
 */
class Callbacks extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'callbacks';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['task_id', 'freelancer_id', 'price'], 'required'],
            [['task_id', 'freelancer_id', 'price'], 'integer'],
            [['text'], 'string', 'max' => 255],
            [['freelancer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::class, 'targetAttribute' => ['freelancer_id' => 'id']],
            [['task_id'], 'exist', 'skipOnError' => true, 'targetClass' => Tasks::class, 'targetAttribute' => ['task_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'task_id' => Yii::t('app', 'ID задания'),
            'freelancer_id' => Yii::t('app', 'ID исполнителя'),
            'text' => Yii::t('app', 'Текст отклика'),
            'price' => Yii::t('app', 'Цена исполнителя за работу'),
        ];
    }

    /**
     * Gets query for [[Freelancer]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFreelancer()
    {
        return $this->hasOne(Users::class, ['id' => 'freelancer_id']);
    }

    /**
     * Gets query for [[Task]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTask()
    {
        return $this->hasOne(Tasks::class, ['id' => 'task_id']);
    }
}
