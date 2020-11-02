<?php

namespace App\Model;


use Carbon\Carbon;

class VisitDetails extends Model
{
    protected $table = 'visit_details';

    protected $fillable = [
        'visit_id', 'manager_id', 'member_id', 'state', 'remarks', 'plan_date', 'real_date'
    ];

    protected $appends = ['status'];

    public function member()
    {
        return $this->hasOne(Member::class, 'id', 'member_id');
    }

    public function manager()
    {
        return $this->hasOne(Manager::class, 'id', 'manager_id');
    }

    public function visit()
    {
        return $this->hasOne(Visit::class, 'id', 'visit_id');
    }

    public function getStatusAttribute()
    {
        if (empty($this->attributes['real_date'])) {
            return 2;
        }
        if (Carbon::now()->isAfter(Carbon::parse($this->attributes['plan_date']))) {
            return 3;
        }
        return 1;
    }
}
