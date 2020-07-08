<?php

namespace App\Model;

class MedicalPlanOperate extends Model
{
    protected $table = 'medical_plan_operates';

    protected $fillable = [
        'role_doctor_id', 'medical_plan_id', 'operate'
    ];
}
