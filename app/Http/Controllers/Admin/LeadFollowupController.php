<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class LeadFollowupController extends Controller
{
    public function index()
    {
        return view('admin.lead-followups.index');
    }
}