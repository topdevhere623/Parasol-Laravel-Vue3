<?php

namespace App\Models\QueryFilters;

class OfferFilter extends QueryFilter
{
    protected $searchable = [
        'type',
        'emirate',
        'name',
    ];

    public function filterType($value)
    {
        return $this->builder->where('offer_type_id', $value);
    }

    public function filterTypeId($value)
    {
        return $this->builder->leftJoinRelation('offerType')->where('offer_types.uuid', $value);
    }

    public function filterClubId($value)
    {
        return $this->builder->leftJoinRelation('clubs')->where('clubs.uuid', $value);
    }

    public function filterEmirate($value)
    {
        return $this->builder->where('emirate', $value);
    }

    public function filterName($value)
    {
        return $this->builder->where('offers.name', 'like', '%'.$value.'%');
    }
}
