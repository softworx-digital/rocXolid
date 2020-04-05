<?php

namespace Softworx\RocXolid\Forms;

use DB;
use Config;
use Illuminate\Support\Collection;
// relations
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// rocXolid contracts
use Softworx\RocXolid\Contracts\Controllable;
use Softworx\RocXolid\Contracts\Modellable;
// rocXolid form contracts
use Softworx\RocXolid\Forms\Contracts\Form;
use Softworx\RocXolid\Forms\Contracts\Formable as FormableContract;
// rocXolid traits
use Softworx\RocXolid\Traits\Controllable as ControllableTrait;
use Softworx\RocXolid\Traits\Modellable as ModellableTrait;
// rocXolid field types
use Softworx\RocXolid\Forms\Fields\Type\ButtonGroup;
use Softworx\RocXolid\Forms\Fields\Type\ButtonSubmitActions;
use Softworx\RocXolid\Forms\Fields\Type\ButtonSubmit;
// rocXolid http requests
use Softworx\RocXolid\Http\Requests\CrudRequest;

/**
 * @todo: subject to refactoring
 * @todo: add automated support for relation fields (relation, model_attribute, model_type, model_id)
 */
abstract class AbstractCrudForm extends AbstractForm implements Controllable, Modellable
{
    use ControllableTrait;
    use ModellableTrait;

    // @todo: cannot be used as intended because of trait overrideability limitations, find some other approach
    /*
    use Traits\Crud\DefaultOptions;
    use Traits\Crud\DefaultButtonToolbars;
    use Traits\Crud\DefaultButtonGroups;
    use Traits\Crud\DefaultButtons;
    use Traits\Crud\DefaultButtonGroups;
    use Traits\Crud\DefaultFieldGroups;
    */

    protected $options = [
        'method' => 'POST',
        //'route-action' => '<action>',
        'class' => 'form-horizontal form-label-left',
    ];

    protected $fieldgroups = false;

    protected $buttontoolbars = false;

    protected $buttongroups = [
        ButtonGroup::DEFAULT_NAME => [
            'type' => ButtonGroup::class,
            'options' => [
                'wrapper' => false,
                'attributes' => [
                    'class' => 'btn-group pull-right'
                ],
            ],
        ],
    ];

    // @todo: filter buttons according to permissions
    protected $buttons = [
        // submit - default group
        'submit' => [
            'type' => ButtonSubmitActions::class,
            'options' => [
                'group' => ButtonGroup::DEFAULT_NAME,
                'label' => [
                    'title' => 'submit-back',
                ],
                'actions' => [
                    'submit-edit' => 'submit-edit',
                    'submit-new' => 'submit-new',
                ],
                'attributes' => [
                    'class' => 'btn btn-success'
                ],
            ],
        ],
    ];

    public function buildFields($validate = true): Form
    {
        parent::buildFields($validate);

        if ($this->getModel()->exists && !$this->wasSubmitted()) {
            $this->setFieldsModelValues();
        }

        return $this;
    }

    public function setFieldsModelValues(): Form
    {
        $this->getModelAttributes()->each(function ($value, $attribute) {
            if ($this->hasFormField($attribute)) {
                $this
                    ->getFormField($attribute)
                        //->setValue($value, $index)
                        ->setValue($value)
                        ->updateParent();
            }
        });

        $user = auth('rocXolid')->user();

        // check permissions for relation fields
        $this->getModelRelationships()->each(function ($relation, $attribute) use ($user) {
            if (!$user
                || (($relation instanceof HasOneOrMany) && ($user->can('update', [ $this->getModel(), $attribute ])))
                || (($relation instanceof BelongsTo) && ($user->can('update', [ $this->getModel(), $attribute ])))
                || (($relation instanceof BelongsToMany) && ($user->can('assign', [ $this->getModel(), $attribute ])))) {

                if ($this->hasFormField($attribute)) {
                    $this
                        ->getFormField($attribute)
                            //->setValue($value, $index)
                            ->setValue($relation->pluck(sprintf(
                                '%s.%s',
                                $relation->getRelated()->getTable(),
                                $relation->getRelated()->getKeyName()
                            )))
                            ->updateParent();

                    if (method_exists($relation, 'getPivotColumns') && filled($relation->getPivotColumns())) {
                        $this
                            ->getFormField($attribute)
                            ->setPivotData($relation->get()->pluck('pivot'))
                            ->updateParent();

                        collect($relation->getPivotColumns())->each(function ($pivot_attribute) use ($relation) {
                            if ($this->hasFormField($pivot_attribute)) {
                                $field = $this->getFormField($pivot_attribute);

                                if (!$field->isPivotFor($relation)) {
                                    throw new \UnderflowException(sprintf(
                                        'Field [%s] has no or invalid "pivot-for" option assigned, relation name [%s] expected',
                                        $field->getName(),
                                        $relation->getRelationName()
                                    ));
                                }

                                $field
                                    ->setValue($this->getModel()->decimalize($relation->get()->pluck(sprintf(
                                        '%s.%s',
                                        $relation->getPivotAccessor(),
                                        $pivot_attribute
                                    ))))
                                    ->updateParent();
                            }
                        });
                    }
                }
            }
        });

        return $this;
    }

    public function getPivotFormFields(BelongsToMany $relation): Collection
    {
        return $this->getFormFields()->filter(function ($field) use ($relation) {
            return $field->isPivotFor($relation);
        });
    }

    public function makeRouteAction($route_action): string
    {
        if ($this->getModel()->exists) {
            return $this->getController()->getRoute($route_action, $this->getModel());
        } else {
            return $this->getController()->getRoute($route_action);
        }
    }

    public function adjustCreate(CrudRequest $request)
    {
        return $this;
    }

    public function adjustCreateBeforeSubmit(CrudRequest $request)
    {
        return $this;
    }

    public function adjustUpdate(CrudRequest $request)
    {
        return $this;
    }

    public function adjustUpdateBeforeSubmit(CrudRequest $request)
    {
        return $this;
    }

    // @todo - ked bude field builder upgradeovany cez factory (uz je, ale este nefactoruje objekty), tak rovno tvorba fieldov a nie len definicii
    // aj to asi nejako krajsie rozdelit - processovanie fieldov (definicii), lebo by sa este mali adjustovat
    protected function getFieldsDefinition(): array
    {
        if (!$this->fields) {
            $attributes = $this->getModelAttributes();

            if (!$attributes->count()) {
                throw new \InvalidArgumentException(sprintf('Model [%s] has no defined fillable attributes', get_class($this->getModel())));
            }

            foreach ($this->getModelAttributes() as $attribute => $value) {
                $column = $this->getConnection()->getDoctrineColumn($this->getModel()->getTable(), $attribute);

                $this->fields[$attribute] = $this->getFormFieldFactory()->makeFieldDefinition($column);
            }

            foreach ($this->getModelRelationships() as $attribute => $relation) {
                $this->fields[$attribute] = $this->getFormFieldFactory()->makeRelationFieldDefinition($this->getConnection(), $attribute, $relation);
            }
        }

        // @todo: "hotfixed", you can do better
        $fields = $this->fields;

        $fields = $this->adjustFieldsDefinition($fields);
        $fields = $this->adjustFieldsValidationDefinition($fields);
        $fields = $this->filterFieldsDefinitionByPermissions($fields);

        return $fields;
    }

    protected function filterFieldsDefinitionByPermissions(array $fields): array
    {
        if (!$user = auth('rocXolid')->user()) {
            return $fields;
        }

        return collect($fields)->filter(function ($definition, $field_name) use ($user) {
            // for now, leave scalar fields untouched
            if (!method_exists($this->getModel(), $field_name)) {
                return true;
            } elseif ($this->getModel()->$field_name() instanceof HasOneOrMany) {
                return $user->can('update', [ $this->getModel(), $field_name ]);
            } elseif ($this->getModel()->$field_name() instanceof BelongsTo) {
                return $user->can('assign', [ $this->getModel(), $field_name ]);
            } elseif ($this->getModel()->$field_name() instanceof BelongsToMany) {
                return $user->can('assign', [ $this->getModel(), $field_name ]);
            } else {
                throw new \RuntimeException(sprintf(
                    'Unsupported relation type [%s] - field [%s]',
                    get_class($this->getModel()->$field_name()),
                    $field_name
                ));
            }
        })->toArray();
    }

    // @todo: "hotfixed", you can do better
    protected function adjustFieldsValidationDefinition($fields)
    {
        foreach ($fields as $field_name => &$field_definition) {
            $rules_config_key = sprintf('rocXolid.validation.%s.%s.rules', get_class($this->getModel()), $field_name);

            if (Config::has($rules_config_key)) {
                $field_definition['options']['validation']['rules'] = Config::get($rules_config_key);
            }
        }

        return $fields;
    }

    protected function getButtonsDefinition(): array
    {
        // @todo: "hotfixed", you can do better
        $buttons = $this->buttons;

        $buttons = $this->adjustButtonsDefinition($buttons);
        $buttons = $this->filterButtonsDefinitionByPermissions($buttons);

        return $buttons;
    }

    protected function filterButtonsDefinitionByPermissions(array $buttons): array
    {
        if (!$user = auth('rocXolid')->user()) {
            return $buttons;
        }

        return collect($buttons)->filter(function ($definition, $button_name) use ($user) {
// @todo
            return true;
        })->toArray();
    }

    protected function getModelAttributes(): Collection
    {
        $attributes = new Collection();

        /*
        foreach ($this->getModel()->toArray() as $attribute => $value)
        {
            if (!in_array($attribute, $this->getModel()->getHidden()))
            {
                $attributes->put($attribute, $this->getModel()->exists ? $value : null);
            }
        }
        */

        foreach ($this->getModel()->getFillable() as $attribute) {
            if (!in_array($attribute, $this->getModel()->getHidden())) {
                $attributes->put($attribute, $this->getModel()->exists ? $this->getModel()->getAttributeFieldValue($attribute) : null);
            }
        }

        foreach ($this->getModel()->getExtraAttributes() as $attribute) {
            $attributes->put($attribute, $this->getModel()->exists ? $this->getModel()->getAttributeFieldValue($attribute) : null);
        }

        return $attributes;
    }

    protected function getModelRelationships(): Collection
    {
        $relationships = new Collection();

        foreach ($this->getModel()->getRelationshipMethods() as $method) {
            $relation = $this->getModel()->$method();
            $attribute = $this->getFormFieldFactory()->getRelationshipFieldName($method, $relation);

            $relationships->put($attribute, $relation);
        }

        return $relationships;
    }

    // @todo - toto zrejme niekam upratat
    // skusit do novej classy a nastavit nech sa ta injectuje miesto laravelovskej Illuminate\Database\Connection
    protected function getConnection()
    {
        $connection = DB::connection();
        $connection
            ->getDoctrineConnection()
            ->getDatabasePlatform()
            ->registerDoctrineTypeMapping('enum', 'string');

        return $connection;
    }
}
