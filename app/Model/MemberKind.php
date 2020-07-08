<?php

namespace App\Model;


class MemberKind extends Model
{
    protected $table = 'member_kinds';

    protected $fillable = [
        'kind', 'desc'
    ];
}
