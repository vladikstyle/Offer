<?php

namespace youdate\widgets;

use app\forms\UserSearchForm;
use app\models\ProfileField;
use app\models\User;
use Yii;
use yii\base\Widget;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package youdate\widgets
 */
class DirectorySearchForm extends Widget
{
    /**
     * @var string
     */
    public $view = 'directory-search-form/widget';
    /**
     * @var User logged-in user
     */
    public $user;
    /**
     * @var UserSearchForm
     */
    public $model;
    /**
     * @var array
     */
    public $countries;
    /**
     * @var array
     */
    public $currentCity = ['value' => null, 'title' => null];
    /**
     * @var ProfileField[]
     */
    public $profileFields = [];

    public function run()
    {
        return $this->render($this->view, [
            'model' => $this->model,
            'user' => $this->user,
            'countries' => $this->countries,
            'currentCity' => $this->currentCity,
            'profileFields' => $this->profileFields,
        ]);
    }
}
