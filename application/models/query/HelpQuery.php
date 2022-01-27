<?php

namespace app\models\query;

use omgdef\multilingual\MultilingualTrait;
use yii\db\ActiveQuery;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\models\query
 */
class HelpQuery extends ActiveQuery
{
    use MultilingualTrait;

    /**
     * @return HelpQuery
     */
    public function active()
    {
        return $this->andWhere(['help.is_active' => true]);
    }

    /**
     * @return HelpQuery
     */
    public function sorted()
    {
        return $this->orderBy('help.sort_order');
    }
}
