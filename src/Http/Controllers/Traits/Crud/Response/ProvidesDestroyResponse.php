<?php

namespace Softworx\RocXolid\Http\Controllers\Traits\Crud\Response;

use Illuminate\Http\Response;
// rocXolid utils
use Softworx\RocXolid\Http\Requests\CrudRequest;
// rocXolid model contracts
use Softworx\RocXolid\Models\Contracts\Crudable;

/**
 * Trait to provide response to a CRUD destroy request.
 *
 * @author softworx <hello@softworx.digital>
 * @package Softworx\RocXolid
 * @version 1.0.0
 */
trait ProvidesDestroyResponse
{
    /**
     * Provide generic success response to controller destroy action.
     *
     * @param \Softworx\RocXolid\Http\Requests\CrudRequest $request Incoming request.
     * @param \Softworx\RocXolid\Models\Contracts\Crudable $model
     * @return \Illuminate\Http\Response|array
     */
    protected function destroyResponse(CrudRequest $request, Crudable $model)
    {
        return $request->ajax()
            ? $this->destroyAjaxResponse($request, $model)
            : $this->destroyNonAjaxResponse($request, $model);
    }

    /**
     * Provide success destroy response for non-AJAX requests.
     *
     * @param \Softworx\RocXolid\Http\Requests\CrudRequest $request Incoming request.
     * @param \Softworx\RocXolid\Models\Contracts\Crudable $model
     * @return \Illuminate\Http\Response
     * @todo action result notification
     */
    protected function destroyNonAjaxResponse(CrudRequest $request, Crudable $model)//: Response
    {
        return redirect($this->getRoute('index'));
    }

    /**
     * Provide success destroy response for AJAX requests.
     *
     * @param \Softworx\RocXolid\Http\Requests\CrudRequest $request Incoming request.
     * @param \Softworx\RocXolid\Models\Contracts\Crudable $model
     * @return array
     * @todo action result notification
     */
    protected function destroyAjaxResponse(CrudRequest $request, Crudable $model): array
    {
        return $this->response
            ->redirect($this->getRoute('index'))
            ->get();
    }
}
