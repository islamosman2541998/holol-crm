@extends('layouts.admin')

@section('title', 'الأدوار والصلاحيات')
@section('page_title', 'الأدوار والصلاحيات')

@section('content')

    <div class="list-card">
        <header class="list-card-head">
            <div>
                <h2 class="list-card-title">قائمة الأدوار</h2>
                <p class="list-card-sub">{{ number_format($roles->total()) }} دور</p>
            </div>

            @can('roles.create')
                <div class="list-card-actions">
                    <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i>
                        إضافة دور
                    </a>
                </div>
            @endcan
        </header>

        <div class="table-responsive data-table-wrap is-bordered-top">
            <table class="table align-middle mb-0 data-table has-actions">
                <thead>
                    <tr>
                        <th>اسم الدور</th>
                        <th>عدد الصلاحيات</th>
                        <th class="col-actions">الإجراءات</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($roles as $role)
                        <tr>
                            <td><span class="cell-person-name">{{ $role->name }}</span></td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $role->permissions_count }}</span>
                            </td>
                            <td>
                                <div class="row-actions">
                                    @if ($role->name !== 'SEO Manager')
                                        @can('roles.edit')
                                            <a href="{{ route('admin.roles.edit', $role) }}" class="row-action is-edit"
                                                title="تعديل الدور" aria-label="تعديل الدور">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endcan

                                        @can('roles.delete')
                                            <form action="{{ route('admin.roles.destroy', $role) }}" method="POST"
                                                class="js-delete-form">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit" class="row-action is-danger" title="حذف الدور"
                                                    aria-label="حذف الدور">
                                                    <i class="bi bi-trash3"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    @else
                                        <span class="badge bg-dark">مدير النظام</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">
                                <div class="list-empty">
                                    <i class="bi bi-inbox"></i>
                                    <div>لا يوجد أدوار حتى الآن</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($roles->hasPages())
            <div class="list-card-foot">
                {{ $roles->links() }}
            </div>
        @endif
    </div>

@endsection
