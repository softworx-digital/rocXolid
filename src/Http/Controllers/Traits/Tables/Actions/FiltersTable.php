<?php

namespace Softworx\RocXolid\Http\Controllers\Traits\Tables\Actions;

use Softworx\RocXolid\Http\Requests\TableRequest;

/**
 * Action trait to handle resource data table filtering requests.
 *
 * @author softworx <hello@softworx.digital>
 * @package Softworx\RocXolid
 * @version 1.0.0
 */
trait FiltersTable
{
    /**
     * Set resource data table filtering values.
     *
     * @param \Softworx\RocXolid\Http\Requests\TableRequest $request
     * @param string $param Table parameter.
     */
    public function tableFilter(TableRequest $request, string $param)//: View
    {
        $table = $this
            ->getTable($request)
            ->setFiltering($request->getFilteringInput());

        $table_component = $this->getTableComponent($table);

        if ($request->ajax()) {
            return $this->response
                ->replace($table_component->getDomId('results'), $table_component->fetch('include.results'))
                ->get();
        } else {
            return $this
                ->getDashboard()
                ->setTableComponent($table_component)
                ->render('index');
        }
    }

    /**
     * Clear data table filtering values.
     *
     * @param \Softworx\RocXolid\Http\Requests\TableRequest $request
     * @param string $param Table parameter.
     */
    public function clearFilter(TableRequest $request, string $param)//: View
    {
        $table = $this
            ->getTable($request)
            ->clearFiltering();

        $table_component = $this->getTableComponent($table);

        if ($request->ajax()) {
            return $this->response
                ->replace($table_component->getDomId('results'), $table_component->fetch('include.results'))
                ->get();
        } else {
            return $this
                ->getDashboard()
                ->setTableComponent($table_component)
                ->render('index');
        }
    }
}
