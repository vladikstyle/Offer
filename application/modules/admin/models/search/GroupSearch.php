<?php

namespace app\modules\admin\models\search;

use app\models\Group;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\modules\admin\models\search
 */
class GroupSearch extends Group
{
    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['alias', 'title', 'description'], 'string', 'max' => 255],
            [['is_verified'], 'boolean'],
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
     * @throws \yii\base\InvalidConfigException
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
        $query->filterWhere(['is_verified' => $this->is_verified]);

        $query->andFilterWhere(['like', 'lower(alias)', strtolower($this->title)]);
        $query->andFilterWhere(['like', 'lower(title)', strtolower($this->title)]);

        $query->andFilterWhere(['like', 'lower(description)', strtolower($this->description)]);

        return $dataProvider;
    }
}
