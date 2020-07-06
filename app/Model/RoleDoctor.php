<?php

namespace App\Model;


class Role extends Model
{
    protected $fillable = [
        'name', 'desc', 'admin'
    ];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permission',
            'role_id', 'permission_id');
    }

    public static function managers($roleId)
    {
        return Manager::query()->where('model', Role::class)
            ->where('model_id', $roleId)->get();
    }

    public static function managersCount($roleId)
    {
        return Manager::query()->where('model', Role::class)
            ->where('model_id', $roleId)->count();
    }
}
