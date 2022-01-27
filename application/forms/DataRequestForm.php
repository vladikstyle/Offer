<?php

namespace app\forms;

use app\base\Model;
use app\components\data\DataExportManager;
use app\models\DataRequest;
use app\models\User;
use Yii;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\forms
 */
class DataRequestForm extends Model
{
    /**
     * @var string
     */
    public $format;
    /**
     * @var User
     */
    protected $user;

    public function init()
    {
        parent::init();
        if (!isset($this->format)) {
            $this->format = DataExportManager::FORMAT_HTML;
        }
    }

    /**
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['format', 'in', 'range' => array_keys($this->getFormatList())],
            ['format', 'checkFlood'],
        ];
    }

    /**
     * @return bool
     */
    public function checkFlood()
    {
        $recentRequestsCount = DataRequest::find()
            ->where(['user_id' => $this->user->id])
            ->andWhere('(unix_timestamp() - created_at) < 86400')
            ->count();

        if ($recentRequestsCount >= Yii::$app->params['maxDataExportsPerDay']) {
            $this->addError('format', Yii::t('app', 'Sorry, you have already requested {0} data exports today', $recentRequestsCount));
            return false;
        }

        return true;
    }

    /**
     * @return array
     */
    public function getFormatList()
    {
        return DataExportManager::getFormatsList();
    }
}
