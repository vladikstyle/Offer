<?php

namespace app\models\query;

use app\models\News;
use hauntd\vote\behaviors\VoteQueryBehavior;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\models\query
 * @method NewsQuery withVoteAggregate($entity)
 * @method NewsQuery withUserVote($entity)
 */
class NewsQuery extends \yii\db\ActiveQuery
{
    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'vote' => VoteQueryBehavior::class,
        ];
    }

    /**
     * @inheritdoc
     * @return News[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return News|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * @return NewsQuery
     */
    public function latest()
    {
        return $this->orderBy('created_at desc');
    }

    /**
     * @return NewsQuery
     */
    public function published()
    {
        return $this->andWhere(['news.status' => News::STATUS_PUBLISHED]);
    }

    /**
     * @return NewsQuery
     */
    public function important()
    {
        return $this->andWhere(['news.is_important' => 1]);
    }
}
