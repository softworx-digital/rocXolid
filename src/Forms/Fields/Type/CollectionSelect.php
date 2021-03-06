<?php

namespace Softworx\RocXolid\Forms\Fields\Type;

use Illuminate\Support\Collection;
// rocXolid models
use Softworx\RocXolid\Models\AbstractCrudModel; // @todo change to crudable contract
// rocXolid model scopes
use Softworx\RocXolid\Models\Scopes\Owned as OwnedScope;
// rocXolid form contracts
use Softworx\RocXolid\Forms\Contracts\FormField;
// rocXolid form fields
use Softworx\RocXolid\Forms\Fields\AbstractFormField;

/**
 * @todo refactor
 */
class CollectionSelect extends AbstractFormField
{
    protected $show_null_option = false;

    protected $collection = null;

    protected $default_options = [
        'type-template' => 'collection-select',
        // field wrapper
        'wrapper' => false,
        // component helper classes
        'helper-classes' => [
            'error-class' => 'has-error',
            'success-class' => 'has-success',
        ],
        // field label
        'label' => false,
        // field HTML attributes
        'attributes' => [
            'placeholder' => null,
            'class' => 'form-control',
            'data-live-search' => true,
            // 'title' => 'select',
        ],
    ];

    public function setCollection($option)
    {
        if ($option instanceof Collection) {
            $this->collection = $option;
        } else {
            $model = ($option['model'] instanceof AbstractCrudModel) ? $option['model'] : new $option['model'];
            $query = $model::query()->select($model->qualifyColumn('*'));
            // $query = $model::withoutGlobalScope(app(OwnedScope::class));

            if (isset($option['filters'])) {
                foreach ($option['filters'] as $filter) {
                    $query = (new $filter['class']())->apply($query, $model, $filter['data']);
                }
            }

            if (isset($option['column'])) {
                $this->collection = $query->pluck(sprintf('%s.%s', $model->getTable(), $option['column']), sprintf('%s.id', $model->getTable()));
            } elseif (isset($option['method'])) {
                if (method_exists($option['model'], $option['method'])) {
                    // @todo ->select($this->queried_model->qualifyColumn('*'))
                    $this->collection = $query->get()->mapWithKeys(function (AbstractCrudModel $model, int $index) use ($option) {
                        return [ $model->getKey() => $this->getOptionText($model, $option, $index) ];
                    });
                } else {
                    throw new \InvalidArgumentException(sprintf(
                        'Invalid method [%s] used for model [%s] collection in population of field [%s]',
                        $option['method'],
                        $option['model'],
                        $this->getFieldName()
                    ));
                }
            } else {
                throw new \InvalidArgumentException(sprintf(
                    'No column or method used for model [%s] collection in population of field [%s]',
                    $option['model'],
                    $this->getFieldName()
                ));
            }
        }

        return $this;
    }

    public function getCollection()
    {
        if (empty($this->collection)) {
            return collect([]);
        }

        return $this->collection;
    }

    /**
     * {@inheritDoc}
     */
    protected function isValueExpected(): bool
    {
        return false;
    }

    protected function getOptionText(AbstractCrudModel $model, array $option, int $index): string
    {
        $text = $model->{$option['method']}();

        switch ($option['indicate'] ?? null)
        {
            case 'index':
                $text = sprintf('%s - %s', $index + 1, $text);
                break;
        }


        return $text;
    }

    /*
        public function isFieldValue($value, $index = 0): bool
        {
            if (!$this->getFieldValue($index) instanceof Collection)
            {
                $this->setValue(collect($this->getFieldValue($index)));
            }

            return  $this->getFieldValue($index)->contains($value);
        }
    */
}
