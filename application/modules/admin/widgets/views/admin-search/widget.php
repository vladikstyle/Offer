<?php

use app\helpers\Html;
use app\helpers\Url;

/** @var $queryParameter string **/
/** @var $query string */
?>
<form class="navbar-form navbar-search pull-left hidden-xs header-search" action="<?= Url::to(['search/index']) ?>">
    <div class="form-group">
        <div class="input-group">
            <input type="text"
                   autocomplete="off"
                   name="<?= $queryParameter ?>"
                   value="<?= Html::encode($query) ?>"
                   class="form-control header-search-query"
                   data-load-results-url="<?= Url::to(['search/get-results']) ?>"
                   placeholder="<?= Yii::t('app', 'Search') ?>">
            <span class="input-group-btn">
                <button type="submit" class="btn btn-primary header-search-btn">
                    <i class="icon fa fa-search"></i>
                </button>
            </span>
        </div>
    </div>
    <div class="header-search-results hidden">
        <div class="results"></div>
        <div class="loader">
            <i class="fa fa-refresh fa-spin" aria-hidden="true"></i>
        </div>
    </div>
</form>
