<?php

namespace app\models\query;

use app\models\Group;
use app\models\User;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\models\query
 */
class GroupQuery extends \yii\db\ActiveQuery
{
    /**
     * @inheritdoc
     * @return Group[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return Group|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * @param $alias
     * @return $this
     */
    public function byAlias($alias)
    {
        return $this->andWhere([
            'group.alias' => $alias,
        ]);
    }

    /**
     * @return GroupQuery
     */
    public function notBlocked()
    {
        return $this->andWhere(['<>', 'group.visibility', Group::VISIBILITY_BLOCKED]);
    }
}
