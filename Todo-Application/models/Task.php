<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "task".
 *
 * @property int $id
 * @property string $name
 * @property string|null $due_date
 * @property int|null $is_complete
 * @property string $created_at
 */
class Task extends \yii\db\ActiveRecord
{
    // ✅ Auto-insert current timestamp into `created_at`
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => false, // or 'updated_at' if you add that column
                'value' => new \yii\db\Expression('NOW()'),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'task';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['due_date'], 'default', 'value' => null],
            [['is_complete'], 'default', 'value' => 0],
            [['name'], 'required'],
            [['due_date', 'created_at'], 'safe'],
            [['is_complete'], 'integer'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Task Name',
            'due_date' => 'Due Date',
            'is_complete' => 'Commplete',
            'created_at' => 'Created At',
        ];
    }
}
