<?php

namespace Softworx\RocXolid\Http\Controllers\Traits\Actions;

// rocXolid utils
use Softworx\RocXolid\Http\Requests\CrudRequest;
// rocXolid form components
use Softworx\RocXolid\Components\Forms\CrudForm as CrudFormComponent;

/**
 * Trait to reload a form (upon field data change to adjust other fields' data).
 *
 * @author softworx <hello@softworx.digital>
 * @package Softworx\RocXolid
 * @version 1.0.0
 */
trait ReloadsForm
{
    /**
     * Reload Create/Update form to dynamically load related field values.
     *
     * @param \Softworx\RocXolid\Http\Requests\CrudRequest $request
     * @param mixed $id
     * @todo: verify if $int can be type hinted as int
     */
    public function formReload(CrudRequest $request, $id = null)//: Response
    {
        $repository = $this->getRepository($this->getRepositoryParam($request));

        $model = $id ? $repository->findOrFail($id) : $repository->getModel();

        $this->setModel($model);

        // @todo: refactor to clearly identify the form we want to get, not artificially like this
        // put form->options['route-action'], or full identification data into the request
        // this can serve as a fallback
        if ($model->exists) {
            $form = $repository
                ->getForm($this->getFormParam($request, 'update'))
                    ->setFieldsRequestInput($request->input())
                    ->adjustUpdateBeforeSubmit($request);
        } else {
            $form = $repository
                ->getForm($this->getFormParam($request, 'create'))
                    ->setFieldsRequestInput($request->input())
                    ->adjustCreateBeforeSubmit($request);
        }

        $form_component = CrudFormComponent::build($this, $this)
                ->setForm($form)
                ->setRepository($repository);

        return $this->response
                ->replace($form_component->getDomId('fieldset'), $form_component->fetch('include.fieldset'))
                ->get();
    }
}
