<?php

namespace Softworx\RocXolid\Forms\Fields;

use Illuminate\Support\Collection;
use Softworx\RocXolid\Contracts\Valueable;
use Softworx\RocXolid\Contracts\ErrorMessageable;
use Softworx\RocXolid\Contracts\Optionable;
use Softworx\RocXolid\Contracts\Translatable;
use Softworx\RocXolid\Forms\Contracts\Form;
use Softworx\RocXolid\Forms\Contracts\FormField;
use Softworx\RocXolid\Forms\Contracts\FormFieldable;
use Softworx\RocXolid\Traits\Valueable as ValueableTrait;
use Softworx\RocXolid\Traits\ErrorMessageable as ErrorMessageableTrait;
use Softworx\RocXolid\Traits\MethodOptionable as MethodOptionableTrait;
use Softworx\RocXolid\Traits\Translatable as TranslatableTrait;
use Softworx\RocXolid\Forms\Fields\Traits\ComponentOptionsSetter as ComponentOptionsSetterTrait;

abstract class AbstractFormField implements FormField, Valueable, ErrorMessageable, Optionable, Translatable
{
    use ValueableTrait;
    use ErrorMessageableTrait;
    use MethodOptionableTrait;
    use TranslatableTrait;
    use ComponentOptionsSetterTrait;

    /**
     * Name of the field.
     *
     * @var string
     */
    protected $name;
    /**
     * Type of the field.
     *
     * @var string
     */
    protected $type;
    /**
     * @var Form
     */
    protected $form;
    /**
     * @var FormFieldable
     */
    protected $parent;
    /**
     * 'template' => 'rocXolid::form.field.text',
     * 'type-template' => 'text',
     * 'attributes' => [
     *     'class' => 'form-control'
     * ],
     * 'wrapper' => [
     *     'attributes' => [
     *         'class' => 'form-group'
     *     ]
     * ],
     * 'label' => [
     *     'title' => 'name',
     *     'attributes' => [
     *         'class' => 'control-label col-md-2 col-sm-2 col-xs-12',
     *         'for' => 'name'
     *     ],
     * ],
     * 'validation' => [
     *     'rules' => [
     *         'required',
     *         'max:255',
     *         'min:2',
     *         'active_url',
     *     ],
     *     'error' => [
     *         'attributes' => [
     *             'class' => 'has-error'
     *         ]
     *     ]
     * ],
     */
    protected $default_options = [];

    /**
     * @param string $name
     * @param string $type
     * @param Form $form
     * @param FormFieldable $parent
     * @param array $options
     */
    public function __construct($name, $type, Form $form, FormFieldable $parent, array $options = [])
    {
        $options = array_replace_recursive($this->default_options, $options);

        $this
            ->setName($name)
            ->setType($type)
            ->setForm($form)
            ->setParent($parent)
            ->setOptions($options)
            ->init();
    }

    protected function init()
    {
        return $this;
    }

    /**
     * Set system name of the field.
     *
     * @param string $name
     * @return $this
     */
    protected function setName($name): FormField
    {
        $this->name = $name;

        return $this;
    }

    protected function setType($type): FormField
    {
        $this->type = $type;

        return $this;
    }

    protected function setForm($form): FormField
    {
        $this->form = $form;

        return $this;
    }

    protected function setParent($parent): FormField
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get system name of the field.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get system name of the field.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return Form
     */
    public function getForm(): Form
    {
        return $this->form;
    }

    /**
     * @return FormFieldable
     */
    public function getParent(): FormFieldable
    {
        return $this->parent;
    }

    public function isArray()
    {
        return $this->getOption('component.array', false);
    }

    public function updateParent()
    {
        // @todo - zavolat parent update + spravne setnuty parent ?
        // to ale chce, aby aj field mal spravne setnutu referenciu na parenta, co zatial nema, zatial je to len form
        if ($group_name = $this->getOption('component.group', false)) {
            if ($this->form->getFormFieldGroup($group_name)->getOption('component.array', false)) {
                $this->form->getFormFieldGroup($group_name)->setGroupCount($this->getValues()->count());
            }
        }

        return $this;
    }

    // @todo - zrejme inak, zatial takto
    public function updateComponent($index = 0)
    {
        if ($this->hasErrorMessages($index)) {
            $key = $this->isArray()
                 ? sprintf('wrapper-%s', $index)
                 : 'wrapper';

            $this->setComponentOptions($key, [
                'attributes' => [
                    'class' => sprintf(
                        '%s %s',
                        $this->getOption('component.wrapper.attributes.class', false),
                        $this->getOption('component.helper-classes.error-class', 'has-error')
                    )
                ]
            ]);
        }

        return $this;
    }

    /**
     * Get HTML name of the field.
     *
     * @return string
     */
    public function getFieldName($index = 0)
    {
        if ($this->isArray()) {
            return sprintf('%s[%s][%s]', self::ARRAY_DATA_PARAM, $index, $this->name);
        } else {
            return sprintf('%s[%s]', self::SINGLE_DATA_PARAM, $this->name);
        }
    }

    /**
     * Get HTML value of the field.
     *
     * @return string
     */
    public function getFieldValue($index = 0)
    {
        if ($this->isArray()) {
            return $this->getIndexValue($index);
        } else {
            return $this->getValue();
        }
    }

    protected function adjustValueBeforeSet($value)
    {
        return $value;
    }

    public function isFieldValue($value, $index = 0)
    {
        return ($this->getFieldValue($index) == $value);
    }

    /**
     * Get validation rule key for the field.
     *
     * @return string
     */
    public function getRuleKey()
    {
        if ($this->isArray()) {
            return sprintf('%s.*.%s', self::ARRAY_DATA_PARAM, $this->name);
        } else {
            return sprintf('%s.%s', self::SINGLE_DATA_PARAM, $this->name);
        }
    }

    protected function setValidation($validation): FormField
    {
        $this->mergeOptions([
            'validation' => $validation
        ]);

        return $this;
    }

    protected function makeRoute($route_name)
    {
        return route($route_name);
    }

    // @todo - toto do separatnej parser classy / viac class, ktore to budu handlovat - pozriet ako sa riesia validation messages
    // Forms\Fields\Support\... - navrhnut strukturu
    // resp cele nejako inak - domysliet
    /*
    protected function processDomDataAttributeValuess($attribute, $value)
    {
        switch ($attribute)
        {
            case 'duplicate-form-row':
                if ($value == ':form-first')
                {
                    return sprintf('#%s fieldset > :first-child', $this->form->getOption('component.id'));
                }
                else
                {
                    throw new \InvalidArgumentException(sprintf('Invalid value [%s] for DOM data attribute [%s]', $value, $attribute));
                }
            default:
                throw new \InvalidArgumentException(sprintf('Invalid attribute [%s] for DOM data', $attribute));
        }
    }
    */
}