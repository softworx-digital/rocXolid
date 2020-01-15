<?php

namespace Softworx\RocXolid\Http\Controllers\Traits\Crud;

use Softworx\RocXolid\Http\Requests\CrudRequest;

/**
 * Trait to display specific resource.
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
     * @param \Softworx\RocXolid\Http\Requests\CrudRequest $request
     */
    public function show(CrudRequest $request, $id)//: View
    {
        $repository = $this->getRepository($this->getRepositoryParam($request));

        $this->setModel($repository->findOrFail($id));

        $model_viewer_component = $this
            ->getModelViewerComponent($this->getModel())
            ->adjustShow($request, $this);

        if ($request->ajax()) {
            return $this->response
                ->modal($model_viewer_component->fetch('modal.show'))
                ->get();
        } else {
            return $this
                ->getDashboard()
                ->setModelViewerComponent($model_viewer_component)
                ->render('model', [
                    'model_viewer_template' => 'show'
                ]);
        }
    }
}
