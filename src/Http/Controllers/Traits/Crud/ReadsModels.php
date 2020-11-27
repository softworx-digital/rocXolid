<?php

namespace Softworx\RocXolid\Http\Controllers\Traits\Crud;

// rocXolid utils
use Softworx\RocXolid\Http\Requests\CrudRequest;
// rocXolid model contracts
use Softworx\RocXolid\Models\Contracts\Crudable;

/**
 * Read resource CRUD action.
 *
 * @author softworx <hello@softworx.digital>
 * @package Softworx\RocXolid
 * @version 1.0.0
 */
trait ReadsModels
{
    /**
     * Display the specified resource.
     *
     * @Softworx\RocXolid\Annotations\AuthorizedAction(policy_ability_group="read-only",policy_ability="view",scopes="['policy.scope.all','policy.scope.owned']")
     * @param \Softworx\RocXolid\Http\Requests\CrudRequest $request Incoming request.
     */
    public function show(CrudRequest $request, Crudable $model, ?string $tab = null)//: View
    {
        $model_viewer_component = $this->getModelViewerComponent($model);

        if ($request->ajax()) {
            return $this->response
                ->modal($model_viewer_component->fetch('modal.show'))
                ->get();
        } else {
            return $this
                ->getDashboard()
                ->setModelViewerComponent($model_viewer_component)
                ->render('model', [
                    'model_viewer_template' => 'show',
                    'tab' => $tab,
                ]);
        }
    }
}
