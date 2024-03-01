<?php

namespace App\ParasolCRM\Resources;

use App\Models\BackofficeUser;
use App\Models\WebFormRequest;
use ParasolCRM\Fields\BelongsTo;
use ParasolCRM\Fields\DateTime;
use ParasolCRM\Fields\Email;
use ParasolCRM\Fields\HorizontalRadioButton;
use ParasolCRM\Fields\Phone;
use ParasolCRM\Fields\Text;
use ParasolCRM\Fields\Textarea;
use ParasolCRM\Filters\EqualFilter;
use ParasolCRM\Filters\Fields\MultipleSelectFilterField;
use ParasolCRM\Filters\Fields\SelectFilterField;
use ParasolCRM\Filters\Fields\TextFilterField;
use ParasolCRM\Filters\LikeFilter;
use ParasolCRM\ResourceScheme;

class WebFormRequestResource extends ResourceScheme
{
    public static $model = WebFormRequest::class;

    protected const STATUS_BADGES = [
        'incoming' => 'blue',
        'assigned' => 'blue',
        'pending' => 'orange',
        'responded' => 'light',
        'joined' => 'green',
        'lost' => 'red',
    ];

    public function fields(): array
    {
        return [
            HorizontalRadioButton::make('status')
                ->options(WebFormRequest::getConstOptions('statuses'))
                ->badges(self::STATUS_BADGES)
                ->rules('required')
                ->sortable(),
            Text::make('type')
                ->onlyOnTable(),
            Text::make('type')
                ->unfillableRecord(),
            Email::make('email')
                ->rules('required'),
            Phone::make('phone'),
            Textarea::make('data', 'Request')
                ->unfillableRecord()
                ->onlyOnForm()
                ->setFromRecordHandler(function ($record, $field) {
                    $result = '';
                    foreach (collect($record->data) as $key => $value) {
                        $result .= \Str::title($key).': '.$value.PHP_EOL;
                    }
                    return $result;
                }),
            Textarea::make('note', 'Note'),
            DateTime::make('updated_at', 'Last updated at')
                ->onlyOnTable(),
            DateTime::make('created_at', 'Requested at')
                ->onlyOnTable(),

            BelongsTo::make('salesPerson', BackofficeUser::class)
                ->titleField('last_name'),

            Text::make('booking')
                ->sortable()
                ->onlyOnTable()
                ->displayHandler(fn ($record) => $record->booking?->reference_id)
                ->url(fn ($record) => "/bookings/{$record->booking?->id}/view"),

            Text::make('member_name')
                ->sortable()
                ->onlyOnTable()
                ->displayHandler(fn ($record) => $record->booking?->member?->full_name),
        ];
    }

    public function filters(): array
    {
        return [
            EqualFilter::make(
                SelectFilterField::make()
                    ->options(WebFormRequest::getConstOptions('statuses')),
                'status',
                'web_form_requests.status'
            )
                ->quick(),
            LikeFilter::make(TextFilterField::make(), 'email', 'web_form_requests.email')
                ->quick(),
            LikeFilter::make(TextFilterField::make(), 'type')
                ->quick(),

            EqualFilter::make(
                MultipleSelectFilterField::make()
                    ->options(BackofficeUser::orderBy('last_name')->pluck('last_name', 'id')->toArray()),
                'salesPerson',
                'backoffice_user_id'
            )->quick(),
        ];
    }
}
