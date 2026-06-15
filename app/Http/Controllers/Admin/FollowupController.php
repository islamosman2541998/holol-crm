<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class FollowupController extends Controller
{
    public function index()
    {
        return view('admin.followups.index');
    }
}