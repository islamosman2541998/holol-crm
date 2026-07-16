<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class ReportController extends Controller
{
    public function clients()
    {
        return view('admin.reports.clients');
    }

    public function leads()
    {
        return view('admin.reports.leads');
    }

    public function salesPayments()
    {
        return view('admin.reports.sales-payments');
    }

    public function quotations()
    {
        return view('admin.reports.quotations');
    }
}