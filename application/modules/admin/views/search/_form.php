<?php

use app\helpers\Html;
use app\helpers\Url;

/** @var $queryParameter string **/
/** @var $query string */
?>
<form action="<?= Url::to(['search/index']) ?>">
    <div class="form-group">
        <div class="input-group">
            <input type="text"
                   autocomplete="off"
                   name="<?= $queryParameter ?>"
                   value="<?= Html::encode($query) ?>"
                   class="form-control"
                   placeholder="<?= Yii::t('app', 'Search query') ?>">
            <span class="input-group-btn">
                <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
            </span>
        </div>
    </div>
</form>
