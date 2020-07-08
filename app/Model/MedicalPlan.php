<?php

namespace App\Model;


class MedicalPlan extends Model
{
    protected $table = 'medical_plans';

    protected $fillable = [
        'member_id', 'doctor_id', 'kinds', 'times'
    ];

    protected $casts = [
        'kinds' => 'json'
    ];

    public function member()
    {
        return $this->hasOne(Member::class, 'id', 'member_id');
    }

    public function doctor()
    {
        return $this->hasOne(RoleDoctor::class, 'id', 'doctor_id');
    }
}
