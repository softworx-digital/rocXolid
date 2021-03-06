<?php

namespace Softworx\RocXolid\Forms\Fields\Type;

use Softworx\RocXolid\Forms\Contracts\FormField;
use Softworx\RocXolid\Forms\Fields\AbstractFormField;
use Softworx\RocXolid\Components\General\Button;

class FormFieldGroupAddable extends AbstractFormField
{
    const DEFAULT_NAME = '__addable__';

    private $group_count = 1;

    protected $default_options = [
        'template' => 'addable',
        'array' => true,
        // field wrapper
        'wrapper' => [
            'attributes' => [
                'class' => 'form-field-group-addable-wrapper'
            ]
        ],
        // row wrapper
        'row' => [
            'attributes' => [
                'class' => 'row form-field-group-addable'
            ]
        ],
        // field label
        'label' => false,
        // field HTML attributes
        'attributes' => [
            'class' => 'form-field-group form-inline col-xs-10'
        ],
        // buttons
        'button-add' => [
            'label' => [
                'icon' => 'fa fa-plus',
                // 'title' => 'add',
            ],
            'attributes' => [
                'class' => 'btn btn-primary',
                'data-add-form-field-group' => '.form-field-group-addable',
                'data-add-form-field-group-container' => '.form-field-group-addable-wrapper'
            ],
        ],
        'button-remove' => [
            'label' => [
                'icon' => 'fa fa-minus',
                // 'title' => 'remove',
            ],
            'attributes' => [
                'class' => 'btn btn-danger',
                'data-remove-form-field-group' => '.form-field-group-addable',
                'data-remove-form-field-group-container' => '.form-field-group-addable-wrapper'
            ],
        ]
    ];

    public function getGroupCount()
    {
        return max(1, $this->group_count);
    }

    public function setGroupCount($group_count)
    {
        $this->group_count = $group_count;

        return $this;
    }

    protected function setRow($options): FormField
    {
        return $this->setComponentOptions('row', $options);
    }

    protected function setButtonAdd($button_options): FormField
    {
        return $this->setComponentOptions('button-add', app(Button::class)->setOptions($button_options));
    }

    protected function setButtonRemove($button_options): FormField
    {
        return $this->setComponentOptions('button-remove', app(Button::class)->setOptions($button_options));
    }
}
