<?php

namespace app\modules\admin\traits;

use app\models\User;

/**
 * @package app\modules\admin\traits
 */
trait UserSelectionTrait
{
    /**
     * @param string $attribute
     * @return array|null
     */
    public function getUserSelection($attribute = 'user_id')
    {
        if (!isset($this->$attribute)) {
            return null;
        }

        $user = User::findOne(['id' => $this->$attribute]);

        return $user == null ? null : ['id' => $user->id, 'text' => $user->username];
    }
}
