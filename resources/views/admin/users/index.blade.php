@extends('layouts.admin')

@section('title', 'المستخدمين')
@section('page_title', 'المستخدمين')

@section('content')

    <div class="list-card">
        <header class="list-card-head">
            <div>
                <h2 class="list-card-title">قائمة المستخدمين</h2>
                <p class="list-card-sub">{{ number_format($users->total()) }} مستخدم</p>
            </div>

            @can('users.create')
                <div class="list-card-actions">
                    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i>
                        إضافة مستخدم
                    </a>
                </div>
            @endcan
        </header>

        <div class="table-responsive data-table-wrap is-bordered-top">
            <table class="table align-middle mb-0 data-table has-actions">
                <thead>
                    <tr>
                        <th>الاسم</th>
                        <th>الإيميل</th>
                        <th>الموبايل</th>
                        <th>الدور</th>
                        <th>الحالة</th>
                        <th>آخر دخول</th>
                        <th class="col-actions">الإجراءات</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>
                                <div class="cell-person">
                                    <span class="cell-person-avatar">{{ mb_substr($user->name, 0, 1) }}</span>
                                    <span class="cell-person-name">{{ $user->name }}</span>
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td class="text-nowrap">{{ $user->phone ?? '-' }}</td>
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
                            <td class="cell-date-wrap">
                                @if ($user->last_login_at)
                                    <span class="cell-date">{{ $user->last_login_at->format('Y-m-d') }}</span>
                                    <span class="cell-time">{{ $user->last_login_at->format('H:i') }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="row-actions">
                                    @can('users.edit')
                                        <a href="{{ route('admin.users.edit', $user) }}" class="row-action is-edit"
                                            title="تعديل المستخدم" aria-label="تعديل المستخدم">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endcan

                                    @can('users.delete')
                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                                            class="js-delete-form">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="row-action is-danger" title="حذف المستخدم"
                                                aria-label="حذف المستخدم">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="list-empty">
                                    <i class="bi bi-inbox"></i>
                                    <div>لا يوجد مستخدمين حتى الآن</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="list-card-foot">
                {{ $users->links() }}
            </div>
        @endif
    </div>

@endsection
