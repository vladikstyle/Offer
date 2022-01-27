<?php

namespace app\modules\admin\forms;

use app\models\Language;
use app\models\LanguageSource;
use app\models\LanguageTranslate;
use Yii;
use yii\base\Exception;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;
use yii\web\UploadedFile;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\modules\admin\forms
 */
class LanguageImportForm extends Model
{
    /**
     * @var UploadedFile The file to import (json)
     */
    public $importFile;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                ['importFile'],
                'file',
                'skipOnEmpty' => false,
                'mimeTypes' => [
                    'application/json',
                    'text/plain', // json is sometimes incorrectly marked as text/plain
                ],
                'enableClientValidation' => false,
            ],
        ];
    }

    /**
     * @return array
     * @throws BadRequestHttpException
     * @throws Exception
     */
    public function import()
    {
        $result = [
            'languages' => ['new' => 0, 'updated' => 0],
            'languageSources' => ['new' => 0, 'updated' => 0],
            'languageTranslations' => ['new' => 0, 'updated' => 0],
        ];

        $data = $this->parseImportFile();

        /** @var Language[] $languages */
        $languages = Language::find()->indexBy('language_id')->all();

        foreach ($data['languages'] as $importedLanguage) {
            if (isset($languages[$importedLanguage['language_id']])) {
                $language = $languages[$importedLanguage['language_id']];
            } else {
                $language = new Language();
            }

            //cast integers
            $importedLanguage['status'] = (int) $importedLanguage['status'];

            $language->attributes = $importedLanguage;
            if (count($language->getDirtyAttributes())) {
                $saveType = $language->isNewRecord ? 'new' : 'updated';
                if ($language->save()) {
                    ++$result['languages'][$saveType];
                } else {
                    $this->throwInvalidModelException($language);
                }
            }
        }

        /** @var LanguageSource[] $languageSources */
        $languageSources = LanguageSource::find()->indexBy('id')->all();

        /** @var LanguageTranslate[] $languageTranslations */
        $languageTranslations = LanguageTranslate::find()->all();

        /*
         *  Create 2 dimensional array for current and imported translation, first index by LanguageSource->id
         *  and than indexed by LanguageTranslate->language.
         *  E.g.: [
         *      id => [
         *          language => LanguageTranslate (for $languageTranslations) / Array (for $importedLanguageTranslations)
         *          ...
         *      ]
         *      ...
         * ]
         */
        $languageTranslations = ArrayHelper::map($languageTranslations, 'language', function ($languageTranslation) {
            return $languageTranslation;
        }, 'id');
        $importedLanguageTranslations = ArrayHelper::map($data['languageTranslations'], 'language', function ($languageTranslation) {
            return $languageTranslation;
        }, 'id');

        foreach ($data['languageSources'] as $importedLanguageSource) {
            $languageSource = null;

            //check if id exist and if category and messages are matching
            if (isset($languageSources[$importedLanguageSource['id']]) &&
                ($languageSources[$importedLanguageSource['id']]->category == $importedLanguageSource['category']) &&
                ($languageSources[$importedLanguageSource['id']]->message == $importedLanguageSource['message'])
            ) {
                $languageSource = $languageSources[$importedLanguageSource['id']];
            }

            if (is_null($languageSource)) {
                //no match by id, search by message
                foreach ($languageSources as $languageSourceSearch) {
                    if (($languageSourceSearch->category == $importedLanguageSource['category']) &&
                        ($languageSourceSearch->message == $importedLanguageSource['message'])
                    ) {
                        $languageSource = $languageSourceSearch;
                        break;
                    }
                }
            }

            if (is_null($languageSource)) {
                //still no match, create new
                $languageSource = new LanguageSource([
                    'category' => $importedLanguageSource['category'],
                    'message' => $importedLanguageSource['message'],
                ]);

                if ($languageSource->save()) {
                    ++$result['languageSources']['new'];
                } else {
                    $this->throwInvalidModelException($languageSource);
                }
            }

            //do we have translations for the current source?
            if (isset($importedLanguageTranslations[$importedLanguageSource['id']])) {
                //loop through the translations for the current source
                foreach ($importedLanguageTranslations[$importedLanguageSource['id']] as $importedLanguageTranslation) {
                    $languageTranslate = null;

                    //is there already a translation for this source
                    if (isset($languageTranslations[$languageSource->id]) &&
                        isset($languageTranslations[$languageSource->id][$importedLanguageTranslation['language']])
                    ) {
                        $languageTranslate = $languageTranslations[$languageSource->id][$importedLanguageTranslation['language']];
                    }

                    //no translation found, create a new one
                    if (is_null($languageTranslate)) {
                        $languageTranslate = new LanguageTranslate();
                    }

                    $languageTranslate->attributes = $importedLanguageTranslation;

                    //overwrite the id because the $languageSource->id might be different from the $importedLanguageTranslation['id']
                    $languageTranslate->id = $languageSource->id;

                    if (count($languageTranslate->getDirtyAttributes())) {
                        $saveType = $languageTranslate->isNewRecord ? 'new' : 'updated';
                        if ($languageTranslate->save()) {
                            ++$result['languageTranslations'][$saveType];
                        } else {
                            $this->throwInvalidModelException($languageTranslate);
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @return mixed|array
     */
    protected function parseImportFile()
    {
        $importFileContent = file_get_contents($this->importFile->tempName);

        return Json::decode($importFileContent);
    }

    /**
     * @param \yii\base\Model $model
     * @throws Exception
     */
    protected function throwInvalidModelException($model)
    {
        $errorMessage = Yii::t('app', 'Invalid model "{model}":', ['model' => get_class($model)]);
        foreach ($model->getErrors() as $attribute => $errors) {
            $errorMessage .= "\n $attribute: " . join(', ', $errors);
        }
        throw new Exception($errorMessage);
    }
}
