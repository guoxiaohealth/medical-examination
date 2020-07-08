<?php

namespace App\Model;


class Diagnosis extends Model
{
    protected $table = 'diagnoses';


    protected $fillable = [
        'member_id', 'subscribe_id', 'doctor_id', 'times', 'no', 'conclusion', 'suggest', 'remarks'
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
