<?php

namespace app\modules\admin\components;

use app\models\Order;
use app\models\User;
use Carbon\Carbon;
use yii\base\BaseObject;
use yii\db\Query;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\modules\admin\components
 */
class Charts extends BaseObject
{
    /**
     * @var int
     */
    public $daysPeriod = 30;

    /**
     * @param $daysCount
     * @return \Generator|Carbon
     */
    public function getDaysIterator($daysCount)
    {
        for ($day = $daysCount; $day--; $day > 0) {
            yield Carbon::now()->subDays($day);
        }
    }

    /**
     * @return array
     */
    public function getDailyLabels()
    {
        $labels = [];
        foreach ($this->getDaysIterator($this->daysPeriod) as $date) {
            $labels[] = $date->format('d/m');
        }

        return $labels;
    }

    /**
     * @return array|null
     */
    public function getUsersData()
    {
        $data = (new Query())
            ->select([
                'count(*) as count',
                "from_unixtime(created_at, '%Y-%m-%d') as day",
            ])
            ->from(User::tableName())
            ->orderBy('day desc')
            ->groupBy('day')
            ->indexBy('day')
            ->limit(30)
            ->all();

        return $this->prepareChartData($data);
    }

    /**
     * @return array|null
     */
    public function getProfitData()
    {
        $data = (new Query())
            ->select([
                'sum(total_price) as count',
                "from_unixtime(updated_at, '%Y-%m-%d') as day",
            ])
            ->from(Order::tableName())
            ->orderBy('day desc')
            ->groupBy('day')
            ->indexBy('day')
            ->limit(30)
            ->where(['status' => Order::STATUS_COMPLETED])
            ->all();

        return $this->prepareChartData($data);
    }

    /**
     * @param $data
     * @param string $attribute
     * @return array
     */
    protected function prepareChartData($data, $attribute = 'count')
    {
        $chartData = [];

        foreach ($this->getDaysIterator($this->daysPeriod) as $date) {
            $dataIndex = $date->format('Y-m-d');
            $chartIndex = $date->format('d/m');
            $chartData[$chartIndex] = isset($data[$dataIndex]) ? (int) $data[$dataIndex][$attribute] : 0;
        }

        return array_values($chartData);
    }
}
