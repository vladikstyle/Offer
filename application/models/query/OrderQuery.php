<?php

namespace app\models\query;

use app\models\User;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\models\query
 */
class OrderQuery extends \yii\db\ActiveQuery
{
    /**
     * @param null $db
     * @return \app\models\Order[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @param null $db
     * @return \app\models\Order|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * @param User $user
     * @return $this
     */
    public function whereUser(User $user)
    {
        $this->andWhere(['order.user_id' => $user->id]);
        return $this;
    }

    /**
     * @param $status
     * @return $this
     */
    public function whereStatus($status)
    {
        $this->andWhere(['order.status' => $status]);
        return $this;
    }

    /**
     * @param $guid
     * @return $this
     */
    public function whereGuid($guid)
    {
        $this->where(['order.guid' => $guid]);
        return $this;
    }
}
