<?php

namespace App\Model;


class VisitDetails extends Model
{
    protected $table = 'visit_details';

    protected $fillable = [
        'visit_id', 'manager_id', 'member_id', 'state', 'remarks', 'plan_date', 'real_date'
    ];

    public function member()
    {
        return $this->hasOne(Member::class, 'id', 'member_id');
    }

    public function managers()
    {
        return $this->hasOne(Manager::class, 'manager_id', 'id');
    }
}
