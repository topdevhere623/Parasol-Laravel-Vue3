<?php

declare(ticks=1);

namespace ParasolCRM\Builders;

use ParasolCRM\FieldCollection;

class Validator
{
    protected array $errors = [];
    private array $fields;

    public function __construct(FieldCollection $fieldCollection)
    {
        $this->fields = $fieldCollection->getFormFields()->all();
    }

    public function validateFields(array $input, $id = null, $fields = []): bool
    {
        $rules = [];
        $attributes = [];
        $labels = [];
        $fields = count($fields) ? $fields : $this->fields;

        foreach ($fields as $field) {
            $labels[$field->name] = $field->label;
            $field_rules = $field->getRules($id ? 'update' : 'create');
            if (!$field_rules) {
                continue;
            }

            foreach ($field_rules as &$value) {
                if ($id && is_string($value) && strpos($value, 'unique') !== false) {
                    $value = $value.','.$id;
                }
            }

            unset($value);

            // Check for all field rules have no numeric indexes
            // Need for HasOne / HasMany field
            // for rules like location.*.phone = required
            if (array_keys($field->rules) !== range(0, count($field->rules) - 1)) {
                foreach ($field->rules as $fieldName => $rule) {
                    $rules[$fieldName] = $rule;
                    $attributes = array_merge($attributes, $field->rulesAttributes);
                    // $attributes[$fieldName] = $field->label;
                }
            } else {
                $rules[$field->name] = $field_rules;
            }
        }

        $validator = \Illuminate\Support\Facades\Validator::make($input, $rules, [], $attributes);
        $validator->setAttributeNames($labels);

        if ($validator->fails()) {
            $this->errors = $validator->errors()->toArray();
            return false;
        }

        return true;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
