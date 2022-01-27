<?php

namespace app\widgets;

use app\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\widgets
 */
class Menu extends \yii\widgets\Menu
{
    /**
     * @var bool
     */
    public $sortItems = false;
    /**
     * @var string
     */
    public $linkTemplate = '<a href="{url}">{icon} {label} {badge}</a>';
    /**
     * @var string|null|bool
     */
    public $badge = false;
    /**
     * @var array
     */
    public $badgeOptions = [];

    public function sortItems()
    {
        $order = 0;
        foreach ($this->items as $k => $item) {
            if (!isset($item['order'])) {
                $this->items[$k]['order'] = $order;
            } else {
                $order = $item['order'];
            }
        }

        $orders = ArrayHelper::getColumn($this->items, 'order');
        array_multisort($orders, SORT_ASC, $this->items);
    }

    public function beforeRun()
    {
        if ($this->sortItems) {
            $this->sortItems();
        }
        return parent::beforeRun();
    }

    /**
     * @inheritdoc
     */
    protected function renderItem($item)
    {
        $replace = [];
        if (isset($item['badge']) && $item['badge']) {
            $replace['{badge}'] = Html::tag('span', $item['badge'], [
                'class' => $item['badgeClass'] ?? 'pull-right label label-primary',
            ]);
        } else {
            $replace['{badge}'] = '';
        }

        if (isset($item['url'])) {
            $template = ArrayHelper::getValue($item, 'template', $this->linkTemplate);
            $replace = !empty($item['icon']) ? array_merge($replace, [
                '{url}' => Url::to($item['url']),
                '{label}' => '<span>' . $item['label'] . '</span>',
                '{icon}' => '<i class="' . $item['icon'] . '"></i> ',
            ]) : array_merge($replace, [
                '{url}' => Url::to($item['url']),
                '{label}' => '<span>' . $item['label'] . '</span>',
                '{icon}' => null,
            ]);

            return strtr($template, $replace);
        }

        $template = ArrayHelper::getValue($item, 'template', $this->labelTemplate);
        $replace = !empty($item['icon']) ? array_merge($replace, [
            '{label}' => '<span>' . $item['label'] . '</span>',
            '{icon}' => '<i class="' . $item['icon'] . '"></i> '
        ]) : array_merge($replace, [
            '{label}' => '<span>' . $item['label'] . '</span>',
        ]);

        return strtr($template, $replace);
    }
}
