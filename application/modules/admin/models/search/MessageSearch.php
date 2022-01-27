<?php

namespace app\modules\admin\models\search;

use app\models\Message;
use app\models\User;
use app\traits\SettingsTrait;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\modules\admin\models\search
 */
class MessageSearch extends Message
{
    use SettingsTrait;

    /**
     * @var int
     */
    public $fromUserId;
    /**
     * @var int
     */
    public $toUserId;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['fromUserId', 'toUserId'], 'integer'],
            [['text'], 'string', 'max' => 255],
        ];
    }

    /**
     * @return array
     */
    public function scenarios()
    {
        return Model::scenarios();
    }

    /**
     * @param $params
     * @return ActiveDataProvider
     * @throws \Exception
     */
    public function search($params)
    {
        $query = self::find()->joinWith(['sender']);

        $messagesOnlyActiveUsers = $this->settings->get('admin', 'adminMessagesOnlyActiveUsers');
        if ($messagesOnlyActiveUsers) {
            $query->andWhere('sender.blocked_at is null');
        }

        $messagesOnlyReportedUsers = $this->settings->get('admin', 'adminMessagesOnlyReportedUsers');
        if ($messagesOnlyReportedUsers) {
            $query
                ->addSelect(['m.*', 'report.reported_user_id'])
                ->leftJoin('report', 'report.reported_user_id = m.from_user_id')
                ->andWhere('report.is_viewed = 0')
                ->andWhere('report.reported_user_id is not null');
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere(['m.from_user_id' => $this->fromUserId]);
        $query->andFilterWhere(['m.to_user_id' => $this->toUserId]);
        $query->andFilterWhere(['like', 'lower(text)', strtolower($this->text)]);

        return $dataProvider;
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'fromUserId' => Yii::t('app', 'Sender'),
            'toUserId' => Yii::t('app', 'Receiver'),
        ]);
    }

    /**
     * @param $attribute
     * @return array|null
     */
    public function getUserSelection($attribute)
    {
        if (!isset($this->$attribute)) {
            return null;
        }

        $user = User::findOne(['id' => $this->$attribute]);

        return $user == null ? null : ['id' => $user->id, 'text' => $user->username];
    }

    /**
     * @return string
     */
    public function formName()
    {
        return '';
    }
}
