<?php

namespace app\modules\admin\actions\translations;

use app\modules\admin\components\Action;
use app\modules\admin\controllers\LanguageController;
use app\models\LanguageSource;
use app\models\LanguageTranslate;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\modules\admin\actions\translations
 * @property LanguageController $controller
 */
class MessageAction extends Action
{
    /**
     * @return string
     */
    public function run()
    {
        $languageTranslate = LanguageTranslate::findOne([
            'id' => $this->controller->request->get('id', 0),
            'language' => $this->controller->request->get('language_id', ''),
        ]);

        if ($languageTranslate) {
            $translation = $languageTranslate->translation;
        } else {
            $languageSource = LanguageSource::findOne([
                'id' => $this->controller->request->get('id', 0),
            ]);

            $translation = $languageSource ? $languageSource->message : '';
        }

        return $translation;
    }
}
