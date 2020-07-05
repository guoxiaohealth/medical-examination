<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Model\Permission;

class PermissionController extends Controller
{
    public function list()
    {
        return $this->data(
            Permission::with('children')->where('parent_id', 0)->get()
        );
    }
}
