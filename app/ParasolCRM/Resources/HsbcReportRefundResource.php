<?php

namespace App\ParasolCRM\Resources;

use App\Models\HSBCUsedCard;
use Illuminate\Database\Eloquent\Builder;

class HsbcReportRefundResource extends HsbcReportRegistrationResource
{
    public function tableQuery(Builder $query)
    {
        parent::tableQuery($query);
        $query->where('hsbc_used_cards.status', HSBCUsedCard::STATUSES['refunded']);
    }

    public static function label()
    {
        $isAdmin = auth()->user()->isAdmin();
        return $isAdmin ? 'Reports | HSBC Refunds' : 'Refunds';
    }
}
