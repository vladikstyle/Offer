<?php

namespace app\modules\admin\actions\translations;

use app\modules\admin\components\Action;
use app\modules\admin\controllers\LanguageController;
use app\modules\admin\traits\TranslationsComponentTrait;
use app\modules\admin\models\search\LanguageSourceSearch;
use Yii;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\modules\admin\actions\translations
 * @property LanguageController $controller
 */
class TranslateAction extends Action
{
    use TranslationsComponentTrait;

    /**
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function run()
    {
        $searchModel = new LanguageSourceSearch([
            'searchEmptyCommand' => $this->getTranslations()->searchEmptyCommand,
        ]);
        $dataProvider = $searchModel->search($this->controller->request->getQueryParams());

        return $this->controller->render('translate', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'searchEmptyCommand' => $this->getTranslations()->searchEmptyCommand,
            'language_id' => $this->controller->request->get('language_id', ''),
        ]);
    }
}
