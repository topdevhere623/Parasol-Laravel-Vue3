<?php

namespace ParasolCRM;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use ParasolCRM\Activities\Facades\Activity;
use ParasolCRM\Builders\Chart;
use ParasolCRM\Builders\Filter;
use ParasolCRM\Builders\Form;
use ParasolCRM\Builders\Status;
use ParasolCRM\Builders\Table;
use ParasolCRM\Fields\RelationField;
use ParasolCRM\Services\CRM\Facades\Prsl;

trait CRUD
{
    use EmptyScheme;

    public function filter(): JsonResponse
    {
        abort_unless(Prsl::checkGatePolicy('index', $this->resource->getModel()), 403, 'Not Allowed');

        $filters = Filter::make($this->resource->filters(), $this->resource->layoutFilters());

        return Prsl::responseData($filters->build());
    }

    public function table(): JsonResponse
    {
        abort_unless(Prsl::checkGatePolicy('index', $this->resource->getModel()), 403, 'Not Allowed');

        if (method_exists($this->resource, 'tableQuery')) {
            // Clear selects before pass query to resource query handler
            $this->resource->tableQuery($this->resourceQuery->getQueryBuilder()->select([]));
        }

        $fields = FieldCollection::make($this->resource->fields())->getTableFields();
        $table = Table::make($this->resourceQuery, $fields, $this->resource->getDefaultSort());
        $table->setParams(request()->all());
        $table->setFilter(Filter::make($this->resource->filters())->setValues(Prsl::getRequestFilters()));

        return Prsl::responseData($table->build());
    }

    public function document()// : ?StreamedResponse
    {
        abort_unless(Prsl::checkGatePolicy('index', $this->resource->getModel()), 403, 'Not Allowed');
        abort_unless(Prsl::checkGatePolicy('export', $this->resource->getModel()), 403, 'Not Allowed');

        if (method_exists($this->resource, 'tableQuery')) {
            $this->resource->tableQuery($this->resourceQuery->getQueryBuilder());
        }

        $fields = FieldCollection::make($this->resource->fields())->getTableFields();
        $table = Table::make($this->resourceQuery, $fields);
        $table->setParams(request()->all());
        $table->setFilter(Filter::make($this->resource->filters())->setValues(Prsl::getRequestFilters()));

        return $table->download();
    }

    public function charts(): JsonResponse
    {
        abort_unless(Prsl::checkGatePolicy('index', $this->resource->getModel()), 403, 'Not Allowed');

        $fields = FieldCollection::make($this->resource->fields())->getTableFields();
        $chart = Chart::make($this->resourceQuery, $fields, $this->resource->charts());
        $chart->setParams(request()->all());
        $chart->setFilter(Filter::make($this->resource->filters())->setValues(Prsl::getRequestFilters()));

        return Prsl::responseData($chart->build());
    }

    public function status(): JsonResponse
    {
        abort_unless(Prsl::checkGatePolicy('index', $this->resource->getModel()), 403, 'Not Allowed');

        if (method_exists($this->resource, 'statusQuery')) {
            $this->resource->statusQuery($this->resourceQuery->getQueryBuilder());
        }

        $fields = FieldCollection::make($this->resource->fields())->getTableFields();
        $statuses = Status::make($this->resourceQuery, $fields, $this->resource->statuses());
        $statuses->setParams(request()->all());
        $statuses->setFilter(Filter::make($this->resource->filters())->setValues(Prsl::getRequestFilters()));

        return Prsl::responseData($statuses->build());
    }

    public function form(): JsonResponse
    {
        abort_unless(Prsl::checkGatePolicy('create', $this->resource->getModel()), 403, 'Not Allowed');

        $record = $this->resourceQuery->createRecordInstance();
        $fields = FieldCollection::make($this->resource->fields())->getFormFields();

        $form = Form::make($this->resourceQuery, $fields, $record, $this->resource->layout());

        return Prsl::responseData($form->build(true));
    }

    public function show(): JsonResponse
    {
        $record = $this->resourceQuery->findRecord($this->id);

        abort_unless(!!$record, 404, 'Not found');
        abort_unless(Prsl::checkGatePolicy('view', $this->resource->getModel(), $record), 403, 'Not Allowed');

        $fields = FieldCollection::make($this->resource->fields())->getFormFields();
        $form = Form::make($this->resourceQuery, $fields, $record, $this->resource->layout());

        return Prsl::responseData($form->build());
    }

    public function store(): JsonResponse
    {
        abort_unless(Prsl::checkGatePolicy('create', $this->resource->getModel()), 403, 'Not Allowed');

        $requestParams = request()->all();
        $fields = FieldCollection::make($this->resource->fields())->getFormFields();
        $record = $this->resourceQuery->createRecordInstance();

        Prsl::validateFieldsOrFail($fields, $requestParams);
        $form = Form::make($this->resourceQuery, $fields, $record, $this->resource->layout());
        $form->setParams($requestParams);

        return $form->save() ? Prsl::responseData($form->build()) : Prsl::responseError('Not created', 500);
    }

    public function update(): JsonResponse
    {
        $record = $this->resourceQuery->findRecord($this->id);

        abort_unless(!!$record, 404, 'Not found');
        abort_unless(Prsl::checkGatePolicy('update', $this->resource->getModel(), $record), 403, 'Not Allowed');

        $requestParams = request()->all();
        $fields = FieldCollection::make($this->resource->fields())->getFormFields();

        Prsl::validateFieldsOrFail($fields, $requestParams, $this->id);

        $form = Form::make($this->resourceQuery, $fields, $record, $this->resource->layout());

        $form->setParams($requestParams);
        return $form->save() ? Prsl::responseData($form->build()) : Prsl::responseError('Not updated', 500);
    }

    public function destroy(): JsonResponse
    {
        $record = $this->resourceQuery->findRecord($this->id);

        abort_unless(!!$record, 404, 'Not found');
        abort_unless(Prsl::checkGatePolicy('delete', $this->resource->getModel(), $record), 403, 'Not Allowed');

        return $record->delete() ? Prsl::responseSuccess() : Prsl::responseError('Not deleted', 500);
    }

    public function log(Request $request): JsonResponse
    {
        abort_unless(Prsl::checkGatePolicy('log', $this->resource->getModel()), 403, 'Not Allowed');

        $from = Carbon::parse($request->input('from'))->format('Y-m-d H:i:s');
        $to = Carbon::parse($request->input('to'))->format('Y-m-d H:i:s');

        return Prsl::responseData(Activity::build($this->resource->getModel(), $this->id, $from, $to));
    }

    public function relationOptions()
    {
        $record = null;
        if ($resourceId = request()->get('resource_id')) {
            $record = $this->resourceQuery->findRecord($resourceId);
        }
        $record ??= $this->resourceQuery->createRecordInstance();

        abort_unless(
            Prsl::checkGatePolicy(['update', 'create'], $this->resource->getModel(), $record),
            403,
            'Not Allowed'
        );

        $fieldName = request()->route('field');
        $filter = request()->get('filter');
        $fields = FieldCollection::make($this->resource->fields())->getFormFields();
        $field = $fields->findFieldByName($fieldName);

        abort_if($resourceId && !$record, 404, 'Not found');

        if ($field instanceof RelationField) {
            // Prsl::responseData($field->getOptions($record, $filter));
            return $field->getOptions($record, $filter);
        }

        abort(404);
    }
}
