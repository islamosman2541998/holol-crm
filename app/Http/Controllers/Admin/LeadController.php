<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeadController extends Controller
{
    public function index()
    {
        return view('admin.leads.index');
    }
    public function show(Lead $lead)
    {
        $lead->load([
            'assignedUser',
            'convertedClient',
        ]);

        return view('admin.leads.show', compact('lead'));
    }
    public function create()
    {
        $users = User::query()
            ->where('status', true)
            ->orderBy('name')
            ->get();

        return view('admin.leads.create', compact('users'));
    }

    public function store(Request $request)
    {
        $data = $this->validateLead($request);

        Lead::query()->create($data);

        return redirect()
            ->route('admin.leads.index')
            ->with('success', 'تم إضافة العميل المحتمل بنجاح');
    }

    public function edit(Lead $lead)
    {
        $users = User::query()
            ->where('status', true)
            ->orWhere('id', $lead->assigned_to)
            ->orderBy('name')
            ->get();

        return view('admin.leads.edit', compact('lead', 'users'));
    }

    public function update(Request $request, Lead $lead)
    {
        $data = $this->validateLead($request);

        $lead->update($data);

        return redirect()
            ->route('admin.leads.index')
            ->with('success', 'تم تحديث العميل المحتمل بنجاح');
    }

    public function destroy(Lead $lead)
    {
        $lead->delete();

        return redirect()
            ->route('admin.leads.index')
            ->with('success', 'تم حذف العميل المحتمل بنجاح');
    }

    public function convert(Lead $lead)
    {
        abort_if($lead->status === 'converted', 422, 'هذا العميل المحتمل تم تحويله بالفعل');

        DB::transaction(function () use ($lead) {
            $client = Client::query()->create([
                'assigned_to' => $lead->assigned_to,
                'name' => $lead->name,
                'company' => $lead->company,
                'email' => $lead->email,
                'mobile' => $lead->mobile,
                'phone' => $lead->phone,
                'city' => $lead->city,
                'source' => $lead->source,
                'status' => 'new',
                'notes' => $lead->notes,
            ]);

            $lead->update([
                'status' => 'converted',
                'converted_client_id' => $client->id,
                'converted_at' => now(),
            ]);
        });

        return redirect()
            ->route('admin.leads.index')
            ->with('success', 'تم تحويل العميل المحتمل إلى عميل بنجاح');
    }

    private function validateLead(Request $request): array
    {
        return $request->validate([
            'assigned_to' => ['nullable', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:50'],
            'city' => ['nullable', 'string', 'max:100'],
            'source' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:new,contacted,qualified,unqualified,converted,lost'],
            'notes' => ['nullable', 'string'],
        ], [
            'name.required' => 'اسم العميل المحتمل مطلوب',
            'email.email' => 'صيغة البريد الإلكتروني غير صحيحة',
            'status.required' => 'حالة العميل المحتمل مطلوبة',
        ]);
    }
}
