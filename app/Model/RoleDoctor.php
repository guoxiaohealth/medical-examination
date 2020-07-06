<?php

namespace App\Model;


class RoleDoctor extends Model
{
    protected $table = 'roles_doctors';

    protected $fillable = [
        'kind', 'role_is_admin', 'role_name', 'doctor_name', 'doctor_desc', 'doctor_image', 'doctor_department_id', 'doctor_can_meet'
    ];

    public function doctorDepartment()
    {
        return $this->hasOne(Department::class, 'doctor_department_id', 'id');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_doctor_permission',
            'role_doctor_id', 'permission_id');
    }
}
