<?php

namespace app\models\query;

use app\models\Group;
use app\models\GroupUser;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\models\query
 */
class GroupUserQuery extends \yii\db\ActiveQuery
{
    /**
     * @inheritdoc
     * @return GroupUser[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return GroupUser|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * @param Group $group
     * @return GroupUserQuery
     */
    public function whereGroup(Group $group)
    {
        return $this->andWhere(['group_user.group_id' => $group->id]);
    }

    /**
     * @param $status
     * @return GroupUserQuery
     */
    public function whereStatus($status)
    {
        return $this->andWhere(['group_user.status' => $status]);
    }

    /**
     * @return GroupUserQuery
     */
    public function withoutBanned()
    {
        return $this->andWhere(['<>', 'group_user.status', GroupUser::STATUS_BANNED]);
    }
}
