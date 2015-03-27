<?php

namespace bupy7\dynafields;

use yii\helpers\Html;
use yii\base\Widget;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\widgets\Pjax;

/**
 * Widget for display dynamic fields.
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
    }
    
    /**
     * @inheritdoc
     */
    public function run()
    {
        Pjax::begin($this->pjaxOptions);
        
        $form = clone $this->form;
        $form->fieldConfig['template'] = str_replace(
            '{input}',
            '<div class="input-group">{input}<span class="input-group-btn">{button}</span></div>',
            $form->fieldConfig['template']
        );
        $button = Html::a(Html::tag('span', '', [
            'class' => 'glyphicon glyphicon-plus',
        ]), array_merge((array)$this->urlAdd), [
            'class' => 'btn btn-default',
            'id' => $this->id . '-add'
        ]);
        $field = $form->field($this->models[0], "[0]{$this->attribute}", $this->fieldOptions);
        $field = call_user_func_array([$field, $this->inputMethod], $this->inputMethodArgs);  
        echo str_replace('{button}', $button, $field);
        
        $form->fieldConfig['template'] = str_replace(
            '{label}', 
            Html::tag('label', '', $form->fieldConfig['labelOptions']), 
            $form->fieldConfig['template']
        );
        for ($i = 1; $i != count($this->models); $i++) {
            $button = Html::a(Html::tag('span', '', [
                    'class' => 'glyphicon glyphicon-minus',
                ]), array_merge((array)$this->urlRemove, ['id' => $this->models[$i]->{$this->primaryKey}]), [
                    'class' => 'btn btn-default',
                    'id' => $this->id . '-remove',
                ]);
            $field = $form->field($this->models[$i], "[{$i}]{$this->attribute}", $this->fieldOptions);
            $field = call_user_func_array([$field, $this->inputMethod], $this->inputMethodArgs);
            echo str_replace('{button}', $button, $field);
        }
        
        $this->form->attributes = $form->attributes;

        Pjax::end();
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
