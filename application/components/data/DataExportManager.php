<?php

namespace app\components\data;

use app\jobs\DataRequestJob;
use app\models\DataRequest;
use Yii;
use yii\base\Component;
use yii\db\Exception;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\components\data
 */
class DataExportManager extends Component
{
    const FORMAT_HTML = 'html';
    const FORMAT_JSON = 'json';

    /**
     * @param $user
     * @param $format
     * @return bool
     * @throws Exception
     * @throws \yii\base\Exception
     */
    public function createRequest($user, $format)
    {
        /** @var DataRequest $dataRequest */
        $dataRequest = Yii::createObject(DataRequest::class);
        $dataRequest->user_id = $user->id;
        $dataRequest->code = Yii::$app->security->generateRandomString(24);
        $dataRequest->status = DataRequest::STATUS_QUEUED;

        if (!$dataRequest->save()) {
            throw new Exception('Could not create data request entry');
        }

        $jobId = Yii::$app->queue->push(new DataRequestJob(['dataRequest' => $dataRequest, 'format' => $format]));

        return $jobId !== null;
    }

    /**
     * @param DataRequest $dataRequest
     * @param $format
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function createArchive($dataRequest, $format)
    {
        $exporter = $this->getExporter($format);
        $exporter->setUser($dataRequest->user);
        $exporter->setFilename($dataRequest->code . '.zip');

        $language = Yii::$app->language;
        Yii::$app->language = $dataRequest->user->profile->getLanguage();

        $status = $exporter->create();
        Yii::$app->language = $language;

        return $status;
    }

    /**
     * @param $dataRequest
     * @return null|string
     */
    public function getFilePath($dataRequest)
    {
        $filePath = Yii::getAlias('@app/runtime/data-exports/') . $dataRequest->code . '.zip';
        if (file_exists($filePath)) {
            return $filePath;
        }

        return null;
    }

    /**
     * @param $format
     * @return BaseDataExport|object
     * @throws \yii\base\InvalidConfigException
     */
    protected function getExporter($format)
    {
        switch ($format) {
            case self::FORMAT_JSON:
                $className = JsonDataExport::class;
                break;
            case self::FORMAT_HTML:
            default:
                $className = HtmlDataExport::class;
                break;
        }

        return Yii::createObject($className);
    }

    /**
     * @return array
     */
    public static function getFormatsList()
    {
        return [
            self::FORMAT_HTML => Yii::t('app', 'HTML'),
            self::FORMAT_JSON => Yii::t('app', 'JSON'),
        ];
    }
}
