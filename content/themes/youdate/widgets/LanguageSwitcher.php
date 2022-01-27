<?php

namespace youdate\widgets;

use app\helpers\Html;
use app\helpers\Url;
use app\models\Language;
use Yii;
use yii\base\Widget;
use yii\db\Expression;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package youdate\widgets
 */
class LanguageSwitcher extends Widget
{
    public function run()
    {
        /** @var Language $currentLanguage */
        $currentLanguage = Language::find()
            ->where(['language_id' => Yii::$app->language])
            ->andWhere(['in', 'status', [Language::STATUS_ACTIVE, Language::STATUS_BETA]])
            ->one();

        if ($currentLanguage === null) {
            return '';
        }

        $availableLanguages = Language::find()
            ->where(['in', 'status', [Language::STATUS_ACTIVE, Language::STATUS_BETA]])
            ->orderBy(new Expression(sprintf("field(name_ascii, '%s') desc, country", $currentLanguage->name_ascii)))
            ->all();

        return $this->render('language-switcher', [
            'currentLanguage' => $currentLanguage,
            'availableLanguages' => $availableLanguages,
        ]);
    }
}
