<?php

namespace app\jobs;

use app\helpers\Url;
use app\models\DataRequest;
use Yii;
use yii\base\BaseObject;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\jobs
 */
class DataRequestJob extends BaseObject implements \yii\queue\JobInterface
{
    /**
     * @var DataRequest
     */
    public $dataRequest;
    /**
     * @var string
     */
    public $format;

    /**
     * @inheritdoc
     */
    public function execute($queue)
    {
        $this->dataRequest->status = DataRequest::STATUS_PROCESSING;
        $this->dataRequest->save();
        Yii::$app->dataExportManager->createArchive($this->dataRequest, $this->format);
        $this->dataRequest->status = DataRequest::STATUS_DONE;
        $this->dataRequest->save();

        Yii::$app->appMailer->sendMessage(
            $this->dataRequest->user->email,
            Yii::t('app', 'Data archive is available for download'),
            'data-export-available', [
                'downloadUrl' => Url::to(['/settings/download-data', 'code' => $this->dataRequest->code], true),
            ]
        );
    }
}
