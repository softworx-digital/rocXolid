<?php

namespace Softworx\RocXolid\Components\Tables;

use Softworx\RocXolid\Repositories\Contracts\Repository;
use Softworx\RocXolid\Components\AbstractOptionableComponent;
use Softworx\RocXolid\Components\Contracts\Repositoryable;
use Softworx\RocXolid\Components\Contracts\TableFilterable as ComponentTableFilterable;
use Softworx\RocXolid\Tables\Filters\Contracts\Filter;

class TableFilter extends AbstractOptionableComponent implements ComponentTableFilterable
{
    const ARRAY_TEMPLATE_NAME = 'array';

    protected $table_filter;

    public static function buildInTable(Repositoryable $table, Filter $table_filter)
    {
        return static::build()
            ->setTranslationPackage($table->getTranslationPackage())
            ->setTranslationParam($table->getTranslationParam())
            ->setTableFilter($table_filter);
    }

    public function setTableFilter(Filter $table_filter): ComponentTableFilterable
    {
        $this->table_filter = $table_filter;

        $this->setOptions($this->table_filter->getOption('component'));

        // @todo kinda "hotfixed", you can do better
        if ($placeholder = $this->getOption('attributes.placeholder', false)) {
            $this->mergeOptions([
                'attributes' => [
                    'placeholder' => $this->translate(sprintf('filter.%s', $placeholder), [], true)
                ]
            ]);
        }

        // @todo kinda "hotfixed", you can do better
        if ($title = $this->getOption('attributes.title', false)) {
            $this->mergeOptions([
                'attributes' => [
                    'title' => $this->translate(sprintf('filter.%s', $title), [], true)
                ]
            ]);
        }

        // @todo kinda "hotfixed", you can do better
        if ($view_package = $this->getOption('view-package', false)) {
            $this->setViewPackage($view_package);
        }

        return $this;
    }

    public function getTableFilter(): Filter
    {
        if (is_null($this->table_filter)) {
            throw new \RuntimeException(sprintf('Table filter is not set yet to [%s] component', get_class($this)));
        }

        return $this->table_filter;
    }

    public function getDefaultTemplateName(): string
    {
        return $this->getTableFilter()->isArray()
             ? static::ARRAY_TEMPLATE_NAME
             : parent::getDefaultTemplateName();
    }

    // @todo zmenit na value z requestu alebo session
    public function getModelValue()
    {
        if (is_null($this->getOption('model', null))) {
            throw new \RuntimeException(sprintf('Model is not set yet to [%s] component', get_class($this)));
        }

        $attribute = $this->getTableFilter()->getName();

        return $this->getOption('model')->$attribute;
    }

    public function getTranslationKey(string $key): string
    {
        return sprintf('field.%s', $key);
    }
}
