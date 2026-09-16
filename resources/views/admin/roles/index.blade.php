@extends('layouts.admin')

@section('title', 'الأدوار والصلاحيات')
@section('page_title', 'الأدوار والصلاحيات')

@section('content')



    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">قائمة الأدوار</h5>

            @can('roles.create')
                <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i>
                    إضافة دور
                </a>
            @endcan
        </div>

        <div class="card-body">
            <div class="table-responsive app-table-responsive">
                <table class="table table-hover align-middle app-data-table">
                    <thead>
                        <tr>
                            <th>اسم الدور</th>
                            <th>عدد الصلاحيات</th>
                            <th class="text-end">الإجراءات</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($roles as $role)
                            <tr>
                                <td class="fw-semibold">{{ $role->name }}</td>
                                <td>{{ $role->permissions_count }}</td>
                                <td class="text-end">
                                    @if ($role->name !== 'SEO Manager')
                                        @can('roles.edit')
                                            <a href="{{ route('admin.roles.edit', $role) }}"
                                                class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endcan

                                        @can('roles.delete')
                                            <form action="{{ route('admin.roles.destroy', $role) }}" method="POST"
                                                class="d-inline js-delete-form">
                                                @csrf
                                                @method('DELETE')

                                                <button class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    @else
                                        <span class="badge bg-dark">مدير النظام</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">
                                    لا يوجد أدوار
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $roles->links() }}
        </div>
    </div>

@endsection
