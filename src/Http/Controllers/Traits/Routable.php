<?php

namespace Softworx\RocXolid\Http\Controllers\Traits;

trait Routable
{
    public function getRoute($route_action, ...$params)
    {
        $action = sprintf('\%s@%s', get_class($this), $route_action);

        return action($action, $params);
    }
}