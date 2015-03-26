<?php
use yii\helpers\Html;

echo Html::activeTextInput($model, $attribute, array_merge([
    'maxlength' => true,
    'class' => 'form-control',
], $inputOptions));

