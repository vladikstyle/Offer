<?php

namespace app\modules\admin\models\search;

use app\models\News;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\modules\admin\models\search
 */
class NewsSearch extends News
{
    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['title', 'content', 'alias', 'excerpt'], 'string', 'max' => 255],
            [['status'], 'in', 'range' => array_keys($this->getStatusOptions())],
        ];
    }

    /**
     * @return array
     */
    public function scenarios()
    {
        return Model::scenarios();
    }

    /**
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = self::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> ['defaultOrder' => ['id' => SORT_DESC]],
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->filterWhere(['id' => $this->id]);
        $query->filterWhere(['status' => $this->status]);

        $query->andFilterWhere(['or',
            ['like', 'lower(title)', strtolower($this->title)],
            ['like', 'lower(content)', strtolower($this->title)],
            ['like', 'lower(alias)', strtolower($this->title)],
        ]);

        $query->andFilterWhere(['like', 'lower(excerpt)', strtolower($this->excerpt)]);

        return $dataProvider;
    }
}
