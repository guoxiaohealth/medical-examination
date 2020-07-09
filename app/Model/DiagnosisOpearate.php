<?php

namespace App\Model;

class DiagnosisOpearate extends Model
{
    protected $table = 'diagnosis_opearates';

    protected $fillable = [
        'role_doctor_id', 'diagnosis_id', 'operate'
    ];

    public function roleDoctor()
    {
        return $this->hasOne(RoleDoctor::class, 'id', 'role_doctor_id');
    }

    public function diagnosis()
    {
        return $this->hasOne(Diagnosis::class, 'id', 'diagnosis_id');
    }
}
