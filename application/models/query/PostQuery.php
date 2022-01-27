<?php

namespace app\models\query;

use app\models\Post;
use app\models\User;
use hauntd\vote\behaviors\VoteQueryBehavior;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\models\query
 * @method PostQuery withVoteAggregate($entity)
 * @method PostQuery withUserVote($entity)
 */
class PostQuery extends \yii\db\ActiveQuery
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
     * @return Post[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return Post|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * @param User $user
     * @return PostQuery
     */
    public function whereUser(User $user)
    {
        return $this->andWhere(['post.user_id' => $user->id]);
    }
}
