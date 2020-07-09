<?php

namespace App\Model;

class MedicalPlanOperate extends Model
{
    protected $table = 'medical_plan_operates';

    protected $fillable = [
        'role_doctor_id', 'medical_plan_id', 'operate'
    ];

    public function roleDoctor()
    {
        return $this->hasOne(RoleDoctor::class, 'id', 'role_doctor_id');
    }

    public function medicalPlan()
    {
        return $this->hasOne(MedicalPlan::class, 'id', 'medical_plan_id');
    }
}
