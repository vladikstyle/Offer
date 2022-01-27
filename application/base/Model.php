<?php

namespace app\base;

use app\traits\RequestResponseTrait;
use yii\helpers\ArrayHelper;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\base
 */
class Model extends \yii\base\Model
{
    use RequestResponseTrait;

    const SCENARIO_FORM = 'form';
    const SCENARIO_SEARCH = 'search';

    /***
     * @param string $modelClass
     * @param array $multipleModels
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function createMultiple($modelClass, $multipleModels = [])
    {
        /** @var \yii\base\Model $model */
        $model = new $modelClass;
        $formName = $model->formName();
        $post = $this->request->post($formName);
        $models = [];

        if (!empty($multipleModels)) {
            $keys = array_keys(ArrayHelper::map($multipleModels, 'id', 'id'));
            $multipleModels = array_combine($keys, $multipleModels);
        }

        if ($post && is_array($post)) {
            foreach ($post as $i => $item) {
                if (isset($item['id']) && !empty($item['id']) && isset($multipleModels[$item['id']])) {
                    $models[] = $multipleModels[$item['id']];
                } else {
                    $models[] = new $modelClass;
                }
            }
        }

        unset($model, $formName, $post);

        return $models;
    }
}
