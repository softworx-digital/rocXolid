<?php

namespace Softworx\RocXolid\Tables\Filters\Traits;

use Softworx\RocXolid\Tables\Filters\Contracts\Filter;

/**
 * @todo refactor (add type hints & doc)
 */
trait ComponentOptionsSetter
{
    protected function setViewPackage($view_package): Filter
    {
        return $this->setComponentOptions('view-package', $view_package);
    }

    protected function setTemplate($template): Filter
    {
        return $this->setComponentOptions('template', $template);
    }

    protected function setTypeTemplate($template): Filter
    {
        return $this->setComponentOptions('type-template', $template);
    }

    protected function setArray($is): Filter
    {
        return $this->setComponentOptions('array', $is);
    }

    protected function setAttributes($attributes): Filter
    {
        return $this->setComponentOptions('attributes', $attributes);
    }

    protected function setWrapper($wrapper): Filter
    {
        return $this->setComponentOptions('wrapper', $wrapper);
    }

    protected function setPlaceholder($placeholder): Filter
    {
        return $this->setComponentOptions('attributes', [ 'placeholder' => $placeholder['title'] ]);
    }

    protected function setOrderable($orderable): Filter
    {
        return $this->setComponentOptions('orderable', $orderable);
    }

    protected function setLabel($label): Filter
    {
        return $this->setComponentOptions('label', $label);
    }

    protected function setResetButton(bool $reset_button): Filter
    {
        return $this->setComponentOptions('reset-button', $reset_button);
    }

    protected function setDisabled(): Filter
    {
        return $this->setComponentOptions('attributes', [ 'disabled' => 'disabled' ]);
    }

    protected function setEnabled(): Filter
    {
        $this->removeOption('component.attribute.disabled');

        return $this;
    }

    protected function setHelperClasses($classes): Filter
    {
        $this->setComponentOptions('helper-classes', $classes);

        return $this;
    }

    protected function setDomData($data): Filter
    {
        $dom_data = [];

        foreach ($data as $attribute => $value) {
            $dom_data[sprintf('data-%s', $attribute)] = $this->processDomDataAttributeValues($attribute, $value);
        }

        return $this->setComponentOptions('attributes', $dom_data);
    }

    protected function setComponentOptions($what, $value)
    {
        $this->mergeOptions([
            'component' => [
                $what => $value
            ]
        ]);

        return $this;
    }
}
