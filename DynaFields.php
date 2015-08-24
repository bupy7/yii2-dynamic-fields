<?php

namespace bupy7\dynafields;

use yii\helpers\Html;
use yii\base\Widget;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\widgets\Pjax;
use yii\helpers\Json;
use yii\web\View;

/**
 * Widget for display dynamic fields, adding and removing their use Pjax.
 * 
 * Home page: https://github.com/bupy7/yii2-dynamic-fields
 * 
 * @author Vasilij Belosludcev http://mihaly4.ru
 * @version 1.0.0
 */
class DynaFields extends Widget
{
    /**
     * @var ActiveForm the form that this field is associated with.
     */
    public $form;
    /**
     * @var array Options of the 'field' method.
     * @see \yii\widgets\ActiveForm::field()
     */
    public $fieldOptions = [];
    /**
     * @var string Name of input method from \yii\widgets\ActiveField. By default 'textInput'.
     * @see \yii\widgets\ActiveField
     */
    public $inputMethod = 'textInput';
    /**
     * @var array Arguments of current $inputMethod as array. First argument is [0], second is [1] and etc.
     * Example: By default $inputMehod is 'textInput'. Then argemnts can be: [['maxlength' => true]].
     */
    public $inputMethodArgs = [];
    /**
     * @var array Models the data model that this widget is associated with.
     */
    public $models;
    /**
     * @var string Primary key of model. By default 'id'.
     */
    public $primaryKey = 'id';
    /**
     * @var string the model attribute that this widget is associated with.
     */
    public $attribute;
    /**
     * @var mixed URL of action for create new model.
     */
    public $urlAdd;
    /**
     * @var mixed URL of action for delete model.
     */
    public $urlRemove;
    /**
     * @var array Options of action button.
     */
    public $buttonOptions = ['class' => 'btn btn-default'];
    /**
     * @var string Template of input. List allow tokens: {input} and {button}. In token {button} will be inserted 
     * action button. In token {input} will be inserted input field.
     */
    public $inputTemplate = '<div class="input-group">{input}<span class="input-group-btn">{button}</span></div>';
    /**
     * @var array Options of Pjax.
     * @see \yii\widgets\Pjax
     */
    public $pjaxOptions = [
        'enablePushState' => false,
        'clientOptions' => [
            'type' => 'post',
        ],
    ];
    /**
     * @var boolean Whether set 'true' then will be displays label for each field and not only for first field.
     */
    public $labelEach = false;
    /**
     * @var boolean Whether set 'true' then will be displays hint for each field and not only for first field.
     */
    public $hintEach = false;
    
    private $_pjax;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!$this->hasModel()) {
            throw new InvalidConfigException("Either 'models' and 'attribute' properties must be specified.");
        }
        if (empty($this->urlAdd) || empty($this->urlRemove)) {
            throw new InvalidConfigException("Either 'urlAdd' and 'urlRemove' properties must be specified.");
        }
        $this->_pjax = Pjax::begin($this->pjaxOptions);
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $form = clone $this->form;
        if (empty($form->fieldConfig['template'])) {
            $form->fieldConfig['template'] = "{label}\n{input}\n{hint}\n{error}";
        }
        if (empty($form->fieldConfig['labelOptions'])) {
            $form->fieldConfig['labelOptions'] = ['class' => 'control-label'];
        }
        $keys = array_keys($this->models);
        $form->fieldConfig['template'] = str_replace('{input}', $this->inputTemplate, $form->fieldConfig['template']);
        $button = Html::a(
            Html::tag('span', '', [
                'class' => 'glyphicon glyphicon-plus',
            ]), $this->urlAdd, $this->buttonOptions
        );
        echo $this->field($form, $this->models[$keys[0]], "[{$keys[0]}]{$this->attribute}", $button);
        if (!$this->labelEach) {
            $form->fieldConfig['template'] = str_replace(
                '{label}', Html::tag('label', '', $form->fieldConfig['labelOptions']), $form->fieldConfig['template']
            );
        }
        if (!$this->hintEach) {
            $form->fieldConfig['template'] = preg_replace(
                '/\{hint\}|\{hint\}\\n|\{hint\}\s/i', '', $form->fieldConfig['template']
            );
        }
        for ($i = 1; $i != count($keys); $i++) {
            $button = Html::a(
                Html::tag('span', '', [
                    'class' => 'glyphicon glyphicon-minus',
                ]), 
                array_merge((array) $this->urlRemove, [
                    'id' => $this->models[$i]->{$this->primaryKey},
                ]), 
                $this->buttonOptions
            );
            echo $this->field($form, $this->models[$keys[$i]], "[{$keys[$i]}]{$this->attribute}", $button);
        }
        if ($this->form->enableClientScript) {
            $clientOptions = [];
            for ($i = 0; $i != count($form->attributes); $i++) {
                if (strpos($form->attributes[$i]['name'], $this->attribute) !== false) {
                    $clientOptions[] = $form->attributes[$i];
                }
            }
            $clientOptions = Json::encode($clientOptions);
            $js = <<<JS
(function($) {
    var clientOptions = {$clientOptions},
        \$form = $('#{$this->form->id}');
    for (var i = 0; i != clientOptions.length; i++) {
        \$form.yiiActiveForm('add', clientOptions[i]);
    }
}(jQuery));
JS;
            $this->view->registerJs($js, View::POS_LOAD);
        }
        Pjax::end();
    }

    /**
     * Render field of \yii\widgets\ActiveForm.
     * @param \yii\widgets\ActiveForm $form
     * @param \yii\base\Model $model The data model.
     * @param string $attribute Attribute name.
     * @param string $button Action button of field.
     * @return string
     */
    protected function field($form, $model, $attribute, $button)
    {
        $field = $form->field($model, $attribute, $this->fieldOptions);
        $field = call_user_func_array([$field, $this->inputMethod], $this->inputMethodArgs);
        return str_replace('{button}', $button, $field);
    }

    /**
     * @return boolean whether this widget is associated with a data model.
     */
    protected function hasModel()
    {
        if (is_array($this->models) && $this->attribute !== null && !empty($this->models)) {
            foreach ($this->models as $model) {
                if (!($model instanceof Model)) {
                    return false;
                }
            }
        } else {
            return false;
        }
        return true;
    }

}
