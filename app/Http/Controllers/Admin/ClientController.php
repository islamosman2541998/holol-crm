<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index()
    {
        return view('admin.clients.index');
    }
public function show(Client $client)
{
    $client->load('assignedUser');

    return view('admin.clients.show', compact('client'));
}
    public function create()
    {
        $users = User::query()
            ->where('status', true)
            ->orderBy('name')
            ->get();

        return view('admin.clients.create', compact('users'));
    }

    public function store(Request $request)
    {
        $data = $this->validateClient($request);

        Client::query()->create($data);

        return redirect()
            ->route('admin.clients.index')
            ->with('success', 'تم إضافة العميل بنجاح');
    }

   public function edit(Client $client)
{
    $users = User::query()
        ->where('status', true)
        ->orWhere('id', $client->assigned_to)
        ->orderBy('name')
        ->get();

    return view('admin.clients.edit', compact('client', 'users'));
}

    public function update(Request $request, Client $client)
    {
        $data = $this->validateClient($request, $client);

        $client->update($data);

        return redirect()
            ->route('admin.clients.index')
            ->with('success', 'تم تحديث بيانات العميل بنجاح');
    }

    public function destroy(Client $client)
    {
        $client->delete();

        return redirect()
            ->route('admin.clients.index')
            ->with('success', 'تم حذف العميل بنجاح');
    }

    private function validateClient(Request $request, ?Client $client = null): array
    {
        return $request->validate([
            'assigned_to' => ['nullable', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'mobile' => ['nullable', 'string', 'max:50'],
            'city' => ['nullable', 'string', 'max:100'],
            'source' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:new,active,inactive,lost'],
            'notes' => ['nullable', 'string'],
        ], [
            'name.required' => 'اسم العميل مطلوب',
            'email.email' => 'صيغة البريد الإلكتروني غير صحيحة',
            'status.required' => 'حالة العميل مطلوبة',
            'status.in' => 'حالة العميل غير صحيحة',
        ]);
    }
}
