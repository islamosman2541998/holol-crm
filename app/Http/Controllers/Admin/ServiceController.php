<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    public function index()
    {
        return view('admin.services.index');
    }

    public function create()
    {
        return view('admin.services.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateService($request);

        Service::query()->create($data);

        return redirect()
            ->route('admin.services.index')
            ->with('success', 'تم إضافة الخدمة بنجاح');
    }

    public function edit(Service $service)
    {
        return view('admin.services.edit', compact('service'));
    }

    public function update(Request $request, Service $service)
    {
        $data = $this->validateService($request, $service);

        $service->update($data);

        return redirect()
            ->route('admin.services.index')
            ->with('success', 'تم تحديث الخدمة بنجاح');
    }

    public function destroy(Service $service)
    {
        $service->delete();

        return redirect()
            ->route('admin.services.index')
            ->with('success', 'تم حذف الخدمة بنجاح');
    }

    private function validateService(Request $request, ?Service $service = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('services', 'code')->ignore($service?->id),
            ],
            'description' => ['nullable', 'string'],
            'default_price' => ['required', 'numeric', 'min:0'],
            'status' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'اسم الخدمة مطلوب',
            'code.required' => 'كود الخدمة مطلوب',
            'code.unique' => 'كود الخدمة مستخدم بالفعل',
            'default_price.required' => 'السعر الافتراضي مطلوب',
            'default_price.numeric' => 'السعر يجب أن يكون رقم',
        ]);
    }
}