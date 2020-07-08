<?php

namespace App\Model;

class Visit extends Model
{
    protected $table = 'visits';

    protected $fillable = [
        'status', 'cycle', 'day', 'first_visit', 'manager_id', 'member_id', 'remarks'
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
