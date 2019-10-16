<?php

namespace Softworx\RocXolid\Components\Tables;

use Softworx\RocXolid\Components\Contracts\TableButtonable as ComponentTableButtonable;
use Softworx\RocXolid\Repositories\Contracts\Column as TableButtonContract;
use Softworx\RocXolid\Models\Contracts\Crudable as CrudableModel;
use Softworx\RocXolid\Components\General\Button;

// @TODO - zatial sa dava buttonanchor implementujuci Column (tuto aliasnuty ako TableButtonContract) - toto doladit / rozdelit
class TableButton extends Button implements ComponentTableButtonable
{
    protected $button;

    public function setButton(TableButtonContract $button): ComponentTableButtonable
    {
        $this->button = $button;

        $this->setOptions($this->button->getOption('component'));

        return $this;
    }

    public function getButton(): TableButtonContract
    {
        if (is_null($this->button)) {
            throw new \RuntimeException(sprintf('Table button is not set yet to [%s] component', get_class($this)));
        }

        return $this->button;
    }

    public function setPreRenderProperties(...$elements)
    {
        $table = $elements[0];
        $model = $elements[1];
        $controller = $table->getRepository()->getController();

        if ($this->hasOption('controller-method')) {
            if ($this->getOption('ajax', false)) {
                $this->mergeOptions([
                    'attributes' => [
                        'data-ajax-url' => $controller->getRoute($this->getOption('controller-method'), $model)
                    ]
                ]);
            } else {
                $this->setOption('url', $controller->getRoute($this->getOption('controller-method'), $model));
            }
        } elseif ($this->hasOption('tel')) {
            $this->setOption('url', sprintf('tel:%s', $model->{$this->getOption('tel')}));
        } elseif ($this->hasOption('mailto')) {
            $this->setOption('url', sprintf('mailto:%s', $model->{$this->getOption('mailto')}));
        }

        if ($this->hasOption('attributes') && ($title = $this->getOption('attributes.title-key', false))) {
            $this->mergeOptions([
                'attributes' => [
                    'title' => $this->translate($title)
                ]
            ]);
        }

        return $this;
    }

    protected function getTranslationKey($key, $use_repository_param)
    {
        if (!$use_repository_param) {
            return sprintf('table-button.%s', $key);
        } elseif ($this->getButton() && $this->getButton()->getRepository()) {
            return sprintf('%s.table-button.%s', $this->getButton()->getRepository()->getTranslationParam(), $key);
        } else {//if ($this->getController() && $this->getController()->getRepository())
            return '---field--- (' . __METHOD__ . ')';
        }

        return $key;
    }
}