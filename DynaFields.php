<?php

namespace bupy7\dynafields;

use yii\helpers\Html;
use yii\base\Widget;
use yii\base\InvalidConfigException;
use yii\base\Model;

/**
 * Widget for display dynamic fields.
 */
class DynaFields extends Widget
{
    
    /**
     * @var string Type of the input. List allows types see to $typesMap.
     */
    public $type;
    
    /**
     * @var array List allows types for render. Where key is name map, where value is alias to view file for render.
     * Example:
     * [
     *      'type' => '@alias/to/view',
     * ]
     * Current allows types: 
     * [
     *      'text' => '_text',
     * ]
     * You can override it or add on new.
     */
    public $typesMap = [];
    
    /**
     * @var array Models the data model that this widget is associated with.
     */
    public $models;
    
    /**
     * @var string the model attribute that this widget is associated with.
     */
    public $attribute;
    
    /**
     * @var array Options of the input.
     */
    public $inputOptions = [];
    
    /**
     * @var string Label of the input.
     */
    public $label;
    
    /**
     * @var type Options of the label input.
     */
    public $labelOptions = [];
    
    /**
     * @var mixed URL of action for create new model.
     */
    public $actionUrlAdd;
    
    /**
     * @var mixed URL of action for delete model.
     */
    public $actionUrlRemove;
    
    /**
     * @var string the template that is used to arrange the label, the input field and the error message of first field.
     * Allowed tokens: '{label}', '{input}', '{button}', '{error}'.
     */
    public $templateFirst = "{label}\n{input}{button}\n{error}";
    
    /**
     * @var string the template that is used to arrange the label, the input field and the error message of 
     * seconds field.
     * Allowed tokens: '{label}', '{input}', '{button}', '{error}'.
     */
    public $templateSecond = "{input}{button}\n{error}";
    
    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!$this->hasModel()) {
            throw new InvalidConfigException("Either 'models' and 'attribute' properties must be specified.");
        }
        
        $this->typesMap = array_merge([
            'text' => '_text',
        ], $this->typesMap);
        
        Html::addCssClass($this->labelOptions, 'control-label');
    }
    
    /**
     * @inheritdoc
     */
    public function run()
    {
        for ($i = 0; $i != count($this->models); $i++) {
            $label = Html::tag('label', $this->label, $this->labelOptions);
            $input = $this->render($this->typesMap[$this->type], [
                'model' => $this->models[$i],
                'attribute' => "[{$i}]{$this->attribute}",
                'inputOptions' => $this->inputOptions,    
            ]);
            if (!$i) {
                $button = Html::button(Html::tag('span', '', [
                    'class' => 'glyphicon glyphicon-plus',
                ]), [
                    'class' => 'btn btn-default',
                ]);
            } else {
                $button = Html::button(Html::tag('span', '', [
                    'class' => 'glyphicon glyphicon-minus',
                ]), [
                    'class' => 'btn btn-default',
                ]);
            }
            $error = Html::error($this->models[$i], "[{$i}]{$this->attribute}", ['class' => 'help-block']);
            
            if (!$i) {
                echo str_replace([
                    '{label}',
                    '{input}',
                    '{button}',
                    '{error}',
                ], [
                    $label,
                    $input,
                    $button,
                    $error,
                ], $this->templateFirst);
            } else {
                echo str_replace([
                    '{label}',
                    '{input}',
                    '{button}',
                    '{error}',
                ], [
                    $label,
                    $input,
                    $button,
                    $error,
                ], $this->templateSecond);
            }
        }
    }
    
    /**
     * @return boolean whether this widget is associated with a data model.
     */
    protected function hasModel()
    {
        if (is_array($this->models) && $this->attribute !== null) {
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
