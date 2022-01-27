<?php

namespace youdate\widgets;

use app\helpers\Html;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package youdate\widgets
 */
class Gallery extends \dosamigos\gallery\Gallery
{
    /**
     * @return string
     */
    public function renderTemplate()
    {
        $template[] = '<div class="slides"></div>';
        $template[] = '<h3 class="title"></h3>';
        $template[] = '<a class="prev text-light">‹</a>';
        $template[] = '<a class="next text-light">›</a>';
        $template[] = '<a class="close text-light"></a>';
        $template[] = '<a class="play-pause"></a>';
        $template[] = '<ol class="indicator"></ol>';

        return Html::tag('div', implode("\n", $template), $this->templateOptions);
    }
}
