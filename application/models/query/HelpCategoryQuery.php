<?php

namespace app\models\query;

use omgdef\multilingual\MultilingualTrait;
use yii\db\ActiveQuery;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\models\query
 */
class HelpCategoryQuery extends ActiveQuery
{
    use MultilingualTrait;

    /**
     * @return HelpCategoryQuery
     */
    public function active()
    {
        return $this->andWhere(['help_category.is_active' => true]);
    }

    /**
     * @return HelpCategoryQuery
     */
    public function sorted()
    {
        return $this->orderBy('help_category.sort_order');
    }
}
