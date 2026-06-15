@extends('layouts.admin')

@section('title', 'المستخدمين')
@section('page_title', 'المستخدمين')

@section('content')



    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">قائمة المستخدمين</h5>

            @can('users.create')
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i>
                    إضافة مستخدم
                </a>
            @endcan
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>الاسم</th>
                            <th>الإيميل</th>
                            <th>الموبايل</th>
                            <th>الدور</th>
                            <th>الحالة</th>
                            <th>آخر دخول</th>
                            <th class="text-end">الإجراءات</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td class="fw-semibold">{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->phone ?? '-' }}</td>
                                <td>
                                    @foreach ($user->roles as $role)
                                        <span class="badge bg-primary">{{ $role->name }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    @if ($user->status)
                                        <span class="badge bg-success text-white">نشط</span>
                                    @else
                                        <span class="badge bg-secondary">غير نشط</span>
                                    @endif
                                </td>
                                <td>{{ $user->last_login_at?->format('Y-m-d H:i') ?? '-' }}</td>
                                <td class="text-end">
                                    @can('users.edit')
                                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endcan

                                    @can('users.delete')
                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                                            class="d-inline js-delete-form">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    لا يوجد مستخدمين
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $users->links() }}
        </div>
    </div>

@endsection
