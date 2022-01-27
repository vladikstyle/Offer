<?php

namespace youdate\helpers;

use app\helpers\Html;
use app\models\DataRequest;
use app\models\Sex;
use app\payments\TransactionInfo;
use Yii;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package youdate\helpers
 */
class HtmlHelper
{
    /**
     * @param $sex
     * @param bool $plural
     * @return null|string
     */
    public static function sexToIcon($sex, $plural = false)
    {
        $base = "<i class=\"%s\" aria-hidden=\"true\"></i>";
        return sprintf($base, self::sexToCssClass($sex ?? null, $plural));
    }

    /**
     * @param Sex|null $sex
     * @param bool $plural
     * @return string
     */
    public static function sexToCssClass($sex, $plural = false)
    {
        $defaultIcon = $plural ? 'fa fa-users' : 'fa fa-user';
        return $sex instanceof Sex ? $sex->icon : $defaultIcon;
    }

    /**
     * @param $distanceVariant
     * @return string
     */
    public static function distanceToLabel($distanceVariant)
    {
        if (!$distanceVariant) {
            return Icon::fa('globe');
        }

        return Yii::t('youdate', '{distance} km', ['distance' => $distanceVariant]);
    }

    /**
     * @param $transactionInfo TransactionInfo|null
     * @return string
     */
    public static function transactionIcon($transactionInfo)
    {
        if ($transactionInfo == null) {
            return '<div class="transaction-type mr-2">' . Icon::fa('question') . '</div>';
        }

        $icon = 'question';
        $color = 'gray';
        switch ($transactionInfo->getType()) {
            case TransactionInfo::TYPE_GIFT:
                $icon = 'gift';
                $color = 'blue';
                break;
            case TransactionInfo::TYPE_PAYMENT:
                $icon = 'money';
                $color = 'green';
                break;
            case TransactionInfo::TYPE_PREMIUM:
                $icon = 'star';
                $color = 'orange';
                break;
            case TransactionInfo::TYPE_BOOST:
                $icon = 'arrow-up';
                $color = 'azure';
                break;
            case TransactionInfo::TYPE_SPOTLIGHT:
                $icon = 'bolt';
                $color = 'indigo';
                break;
        }

        return "<div class=\"transaction-type bg-{$color} mr-2\">" . Icon::fa($icon) . "</div>";
    }

    /**
     * @param $status
     * @return string
     */
    public static function dataRequestStatusToBadge($status)
    {
        switch ($status) {
            case DataRequest::STATUS_QUEUED:
                $text = Yii::t('youdate', 'Queued');
                $class = 'warning';
                break;
            case DataRequest::STATUS_PROCESSING:
                $text = Yii::t('youdate', 'Processing');
                $class = 'info';
                break;
            case DataRequest::STATUS_DONE:
                $text = Yii::t('youdate', 'Done');
                $class = 'primary';
                break;
            default:
                $text = Yii::t('youdate', 'Unknown');
                $class = 'default';
        }

        return Html::tag('span', $text, ['class' => ('badge badge-' . $class)]);
    }
}
