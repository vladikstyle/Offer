<?php

namespace app\models\query;

use app\models\GroupPost;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\models\query
 */
class GroupPostQuery extends \yii\db\ActiveQuery
{
    /**
     * @inheritdoc
     * @return GroupPost[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return GroupPost|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
