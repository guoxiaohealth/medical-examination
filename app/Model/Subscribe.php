<?php

namespace App\Model;


class Subscribe extends Model
{
    protected $table = 'subscribes';

    protected $fillable = [
        'date', 'status', 'remarks', 'member_id', 'doctor_id'
    ];

    public function member()
    {
        return $this->hasOne(Member::class, 'id', 'member_id');
    }

    public function doctor()
    {
        return $this->hasOne(RoleDoctor::class, 'id', 'doctor_id');
    }

    public function diagnose()
    {
        return $this->hasOne(Diagnosis::class, 'subscribe_id', 'id');
    }
}
