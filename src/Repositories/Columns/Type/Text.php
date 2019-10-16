<?php

namespace Softworx\RocXolid\Repositories\Columns\Type;

// contracts
use Softworx\RocXolid\Repositories\Contracts\Column;
// column types
use Softworx\RocXolid\Repositories\Columns\AbstractColumn;

/**
 *
 */
class Text extends AbstractColumn
{
    protected $default_options = [
        'type-template' => 'text',
        /*
        // field wrapper
        'wrapper' => false,
        // column HTML attributes
        'attributes' => [
            'class' => 'form-control'
        ],
        */
    ];

    protected function setShorten($max): Column
    {
        return $this->setComponentOptions('shorten', $max);
    }
}