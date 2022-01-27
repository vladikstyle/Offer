<?php

namespace youdate\widgets;

use app\models\Post;
use yii\base\Widget;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package youdate\widgets
 */
class PostWidget extends Widget
{
    /**
     * @var Post
     */
    public $post;
    /**
     * @var bool
     */
    public $canDelete = false;
    /**
     * @var string
     */
    public $reportUrl;
    /**
     * @var string
     */
    public $deleteUrl;

    /**
     * @return string
     */
    public function run()
    {
        return $this->render('post/default', [
            'post' => $this->post,
            'canDelete' => $this->canDelete,
            'reportUrl' => $this->reportUrl,
            'deleteUrl' => $this->deleteUrl,
        ]);
    }
}
