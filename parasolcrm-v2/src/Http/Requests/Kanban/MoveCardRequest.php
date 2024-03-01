<?php

namespace ParasolCRMV2\Http\Requests\Kanban;

use App\Models\Lead\Lead;
use Illuminate\Foundation\Http\FormRequest;

class MoveCardRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id' => 'required|exists:'.Lead::getTableName(),
            'crm_step_id' => 'required|int',
        ];
    }

    public function all($keys = null): array
    {
        $data = parent::all();
        $data['id'] = $this->route('id');

        return $data;
    }
}
