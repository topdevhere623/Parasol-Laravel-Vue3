<?php

declare(ticks=1);

namespace ParasolCRMV2\Services\CRM;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use ParasolCRMV2\Builders\Validator;
use ParasolCRMV2\Services\CRM\Traits\Response;

class PrslService
{
    use Response;

    private array $policyModelExistCache = [];

    public function validateFieldsOrFail($fields, $input, $id = null)
    {
        $validator = new Validator($fields);
        if (!$validator->validateFields($input, $id)) {
            $this->responseData(['errors' => $validator->getErrors()], 'Validation error', 422)->throwResponse();
        }
    }

    public function policyExists(string $model)
    {
        if (empty($this->policyModelExistCache[class_basename($model)])) {
            $this->policyModelExistCache[class_basename($model)] = Gate::getPolicyFor($model) != null;
        }
        return $this->policyModelExistCache[class_basename($model)];
    }

    public function checkGatePolicy($ability, string $model, Model $record = null): bool
    {
        if ($this->policyExists($model)) {
            return Gate::any($ability, [$model, $record ?? '']);
        }
        $ability = is_array($ability) ? $ability : [$ability];
        foreach ($ability as $item) {
            return \Auth::user()->hasPermission($item.'-'.$model);
        }
        return false;
    }

    public function getRequestFilters(): array
    {
        $filters = request()->input('filters') ?? '';
        return json_decode($filters, true) ?? [];
    }

    public function getCurrentResourceName(): ?string
    {
        $routeResource = request()->route('resource')
            ?? Str::of(request()->route()->uri())
                ->ltrim('/')
                ->between('api/crm/v2/', '/');

        return Str::of($routeResource)
            ->camel()
            ->ucfirst()
            ->singular()
            ->toString();
    }

    public function getResourceClass(string $resource = null): ?string
    {
        $resource ??= $this->getCurrentResourceName();
        $class = "\App\\ParasolCRMV2\\Resources\\{$resource}Resource";
        return class_exists($class) ? $class : null;
    }

    public function getResource(string $resource = null)
    {
        $resourceClass = $this->getResourceClass($resource);
        return $resourceClass ? new $resourceClass() : null;
    }

    public function getResourceEndpoint(string $resource = null): ?string
    {
        $resource = ucfirst($resource ?? $this->getCurrentResourceName());
        return (string)\Str::of($resource)->singular()->kebab()->lower();
    }

    public function getRelationFieldEndpoint(string $fieldName = null, string $resource = null): ?string
    {
        $resource = ucfirst($resource ?? $this->getCurrentResourceName());
        return $this->getResourceEndpoint($resource).'/relation-options'.($fieldName ? "/{$fieldName}" : '');
    }

    public function getResourceLabel(string $resource = null): ?string
    {
        $resource ??= $this->getCurrentResourceName();
        $class = "\App\\ParasolCRMV2\\Resources\\{$resource}Resource";
        if (class_exists($class)) {
            return $class::label();
        }

        return null;
    }

    public function getResourceSingularLabel(string $resource = null): ?string
    {
        $resource ??= $this->getCurrentResourceName();
        $class = "\App\\ParasolCRMV2\\Resources\\{$resource}Resource";
        if (class_exists($class)) {
            return $class::singularLabel();
        }

        return null;
    }
}
