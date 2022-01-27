<?php

namespace app\modules\admin\actions\translations;

use app\modules\admin\components\Action;
use app\modules\admin\controllers\LanguageController;
use app\modules\admin\traits\TranslationsComponentTrait;
use app\modules\admin\forms\LanguageExportForm;
use Yii;
use yii\web\JsonResponseFormatter;
use yii\web\Response;
use yii\web\XmlResponseFormatter;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\modules\admin\actions\translations
 * @property LanguageController $controller
 */
class ExportAction extends Action
{
    use TranslationsComponentTrait;

    /**
     * @return string|array
     * @throws \yii\base\InvalidConfigException
     */
    public function run()
    {
        $model = new LanguageExportForm([
            'format' => $this->getTranslations()->defaultExportFormat,
        ]);

        if ($model->load($this->controller->request->post())) {
            $fileName = Yii::t('app', 'translations') . '.' . $model->format;

            Yii::$app->response->format = $model->format;

            Yii::$app->response->formatters = [
                Response::FORMAT_JSON => [
                    'class' => JsonResponseFormatter::class,
                    'encodeOptions' => JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT,
                ],
            ];

            Yii::$app->response->setDownloadHeaders($fileName);

            return $model->getExportData();
        } else {
            if (empty($model->languages)) {
                $model->exportLanguages = $model->getDefaultExportLanguages($this->getTranslations()->defaultExportStatus);
            }

            return $this->controller->render('export', [
                'model' => $model,
            ]);
        }
    }
}
