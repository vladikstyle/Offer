<?php

use app\helpers\Html;

/** @var $title string */
/** @var $results array */
/** @var $fallback array */

?>
<div class="header-search-group">
    <?php if (isset($title)): ?>
        <div class="group-label">
            <?= Html::encode($title) ?>
        </div>
    <?php endif; ?>
    <div class="items">
        <?php if (count($results)): ?>
            <?php foreach ($results as $result): ?>
                <a href="<?= $result['url'] ?>">
                    <?php if (isset($result['image'])): ?>
                        <?= Html::img($result['image'], ['class' => 'img-rounded']) ?>
                    <?php else: ?>
                        <div class="no-image">
                            <i class="fa fa-image"></i>
                        </div>
                    <?php endif; ?>
                    <span><?= Html::encode($result['text']) ?></span>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <?= $fallback ?>
        <?php endif; ?>
    </div>
</div>
