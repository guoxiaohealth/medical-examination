<?php

namespace App\Model;


use Carbon\Carbon;

class Member extends Model
{
    protected $table = 'members';

    protected $fillable = [
        'name', 'sex', 'birthday', 'mobile', 'remarks', 'member_kind_id', 'channel_id'
    ];

    protected $appends = [
        'age'
    ];

    public function getAgeAttribute()
    {
        $birthday = Carbon::parse($this->attributes['birthday']);
        return Carbon::now()->diffInYears($birthday);
    }

    public function memberKind()
    {
        return $this->hasOne(MemberKind::class, 'id', 'member_kind_id');
    }

    public function channel()
    {
        return $this->hasOne(Channel::class, 'id', 'channel_id');
    }

    public function medicalPlans()
    {
        return $this->hasMany(MedicalPlan::class, 'member_id', 'id');
    }

    public function diagnosis()
    {
        return $this->hasMany(Diagnosis::class, 'member_id', 'id');
    }

    public function visit()
    {
        return $this->hasOne(Visit::class, 'member_id', 'id');
    }
}
