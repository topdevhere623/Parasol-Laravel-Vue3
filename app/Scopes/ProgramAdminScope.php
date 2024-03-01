<?php

namespace App\Scopes;

use App\Enum\Booking\StepEnum;
use App\Models\BackofficeUser;
use App\Models\Booking;
use App\Models\Club\Checkin;
use App\Models\Member\Member;
use App\Models\Payments\Payment;
use App\Models\Plan;
use App\Models\Program;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ProgramAdminScope implements Scope
{
    protected int $programId = 0;

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        if (
            \Auth::hasUser()
            && \Auth::user() instanceof BackofficeUser
            && \Auth::user()->hasTeam('program_admins')
            && $this->programId = auth()->user()->program_id
        ) {
            match (true) {
                $model instanceof Program => $builder->where($model->getTable().'.id', $this->programId),
                $model instanceof Booking => $this->bookingScope($builder),
                $model instanceof Payment => $this->paymentScope($builder),
                $model instanceof Plan => $this->planScope($builder),
                $model instanceof Checkin => $this->checkinScope($builder),
                $model instanceof Member => $builder->where($model->getTable().'.program_id', $this->programId),
                default => $builder->where('program_id', $this->programId)
            };
        }
    }

    private function bookingScope(Builder $builder): void
    {
        $builder->whereHas('plan', function (Builder $builder) {
            $builder->whereHas('package', function (Builder $builder) {
                $builder->where('program_id', $this->programId);
            });
        });

        if ($this->programId != Program::ENTERTAINER_HSBC) {
            $builder->where('bookings.step', StepEnum::Completed);
        }
    }

    private function paymentScope(Builder $builder): void
    {
        $builder->whereHas('member')
            ->whereIn(
                'payments.status',
                [Payment::STATUSES['paid'], Payment::STATUSES['refunded'], Payment::STATUSES['partial_refunded']]
            );

        if ($this->programId == Program::ENTERTAINER_SOLEIL_ID) {
            $builder->whereDate('payment_date', '>=', '2023-05-01');
        }
    }

    private function planScope(Builder $builder): void
    {
        $builder->whereHas(
            'package',
            fn (Builder $packageBuilder) => $packageBuilder->where('program_id', $this->programId)
        );
    }

    private function checkinScope(Builder $builder): void
    {
        $builder->whereHas('member');
    }
}
