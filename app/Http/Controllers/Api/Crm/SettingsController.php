<?php

namespace App\Http\Controllers\Api\Crm;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use ParasolCRM\Builders\Form;
use ParasolCRM\FieldCollection;
use ParasolCRM\Http\Controllers\ResourceController;
use ParasolCRM\Services\CRM\Facades\Prsl;

class SettingsController extends ResourceController
{
    public function update(): JsonResponse
    {
        abort_unless(Prsl::checkGatePolicy('update', $this->resource->getModel()), 403, 'Not Allowed');

        $requestParams = request()->all();
        $fields = FieldCollection::make($this->resource->fields())->getFormFields();

        Prsl::validateFieldsOrFail($fields, $requestParams);

        $form = Form::make($this->resourceQuery, $fields, $this->prepareModel(), $this->resource->layout());
        $form->setParams($requestParams);
        $form->fillRecord();

        \DB::beginTransaction();
        foreach ($form->getRecord()->getAttributes() as $key => $value) {
            try {
                Setting::updateByKey($key, $value);
            } catch (ModelNotFoundException $exception) {
                return Prsl::responseError($exception->getMessage(), 422);
            }
        }
        \DB::commit();

        // $form->setRecord($this->prepareModel());
        return Prsl::responseData($form->build(), 'Settings has been updated');
    }

    public function form(): JsonResponse
    {
        abort_unless(Prsl::checkGatePolicy('view', $this->resource->getModel()), 403, 'Not Allowed');

        $record = $this->prepareModel();
        $fields = FieldCollection::make($this->resource->fields())->getFormFields();
        $form = Form::make($this->resourceQuery, $fields, $record, $this->resource->layout());

        return Prsl::responseData($form->build());
    }

    protected function prepareModel(): Model
    {
        $record = $this->resourceQuery->createRecordInstance();

        $this->resourceQuery->createRecordInstance()
            ->where('editable', true)
            ->get()
            ->each(
                fn ($item) => $record->setAttribute($item->key, $item->value)
            );

        return $record;
    }
}
