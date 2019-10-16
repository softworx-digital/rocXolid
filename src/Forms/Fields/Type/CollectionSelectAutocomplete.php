<?php

namespace Softworx\RocXolid\Forms\Fields\Type;

use Illuminate\Support\Collection;
use Softworx\RocXolid\Forms\Fields\Type\CollectionSelect;
use Softworx\RocXolid\Filters\StartsWith;

class CollectionSelectAutocomplete extends CollectionSelect
{
    protected $collection_model = null;

    protected $collection_model_column = null;

    protected $collection_model_method = null;

    protected $collection_filters = [];

    protected $collection_loaded = false;

    protected $default_options = [
        'type-template' => 'collection-select-autocomplete',
        // field wrapper
        'wrapper' => false,
        // component helper classes
        'helper-classes' => [
            'error-class' => 'has-error',
            'success-class' => 'has-success',
        ],
        // field label
        'label' => false,
        // null option
        'show-null-option' => false,
        // field HTML attributes
        'attributes' => [
            'class' => 'form-control autocomplete',
            'data-live-search' => true,
        ],
    ];
    /*
        protected function init()
        {
    dump($this->getOptions());
            $this->setOption('attributes.data-abs-ajax-url', $this->getForm()->getController()->getRoute('autocomplete', $this->getForm()->getModel(), ['f' => $this->getName()]));
    dd($this->getOptions());
            return $this;
        }
    */
    public function setCollection($option)
    {
        if ($option instanceof Collection) {
            $this->collection = $option;
            $this->collection_loaded = true;
        } else {
            $this->collection = new Collection();
            $this->collection_model = ($option['model'] instanceof Model) ? $option['model'] : new $option['model'];
            $this->collection_model_column = $option['column'];
            $this->collection_model_method = isset($option['method']) ? $option['method'] : null;

            if (isset($option['filters'])) {
                $this->collection_filters = $option['filters'];
            }
        }

        return $this;
    }

    public function getCollection()
    {
        if (!$this->collection_loaded && $this->shouldLoad()) {
            $query = $model = $this->collection_model;

            foreach ($this->collection_filters as $filter) {
                $query = (new $filter['class']())->apply($query, $model, $filter['data']);
            }

            $this->collection = $query->take(10)->pluck(sprintf('%s.%s', $this->collection_model->getTable(), $this->collection_model_column), sprintf('%s.id', $this->collection_model->getTable()));
        } else {
            $value = (($this->getValue() instanceof Collection) && $this->getValue()->isEmpty()) ? null : $this->getValue();

            $this->collection = $this->collection_model->where(sprintf('%s.id', $this->collection_model->getTable()), $value)->take(10)->pluck(sprintf('%s.%s', $this->collection_model->getTable(), $this->collection_model_column), sprintf('%s.id', $this->collection_model->getTable()));
        }

        if (!is_null($this->collection_model_method) && method_exists($this->collection_model, $this->collection_model_method)) {
            $this->collection = $this->collection->map(function (&$item, $id) {
                return $this->collection_model->find($id)->{$this->collection_model_method}();
            });
        }

        $collection = $this->collection;

        if ($this->show_null_option) {
            $collection->prepend($this->translate($this->getOption('component.label.title')), '');
        }

        return $collection;
    }

    public function shouldLoad()
    {
        foreach ($this->collection_filters as $filter) {
            if ($filter['class'] == StartsWith::class) {
                return true;
            }
        }

        return false;
    }

    public function addFilter($filter)
    {
        $this->collection_filters[] = $filter;

        return $this;
    }
}