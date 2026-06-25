<?php

namespace App\Observers;

use App\Models\Lead;
use App\Models\User;

class LeadObserver
{
    public function created(Lead $lead): void
    {
        $lead->logActivity(
            event: 'created',
            title: 'تم إنشاء الـ Lead',
            description: 'تم إضافة العميل المحتمل إلى النظام.',
            newValues: $lead->only([
                'name',
                'company',
                'email',
                'mobile',
                'phone',
                'city',
                'source',
                'status',
                'assigned_to',
            ])
        );

        if ($lead->assigned_to) {
            $user = User::find($lead->assigned_to);

            $lead->logActivity(
                event: 'assigned',
                title: 'تم إسناد الـ Lead',
                description: 'تم إسناد الـ Lead إلى ' . ($user?->name ?? 'موظف غير معروف') . '.',
                oldValues: [
                    'assigned_to' => null,
                ],
                newValues: [
                    'assigned_to' => $lead->assigned_to,
                    'assigned_to_name' => $user?->name,
                ]
            );
        }
    }

    public function updated(Lead $lead): void
    {
        $changes = $lead->getChanges();

        unset($changes['updated_at']);

        if (empty($changes)) {
            return;
        }

        if (array_key_exists('assigned_to', $changes)) {
            $oldUser = User::find($lead->getOriginal('assigned_to'));
            $newUser = User::find($lead->assigned_to);

            $lead->logActivity(
                event: 'assigned',
                title: 'تم تغيير الموظف المسؤول',
                description: 'تم تغيير المسؤول من ' .
                    ($oldUser?->name ?? 'بدون موظف') .
                    ' إلى ' .
                    ($newUser?->name ?? 'بدون موظف') .
                    '.',
                oldValues: [
                    'assigned_to' => $lead->getOriginal('assigned_to'),
                    'assigned_to_name' => $oldUser?->name,
                ],
                newValues: [
                    'assigned_to' => $lead->assigned_to,
                    'assigned_to_name' => $newUser?->name,
                ]
            );

            unset($changes['assigned_to']);
        }

        if (array_key_exists('status', $changes)) {
            $lead->logActivity(
                event: 'status_changed',
                title: 'تم تغيير حالة الـ Lead',
                description: 'تم تغيير الحالة من ' .
                    $this->statusLabel($lead->getOriginal('status')) .
                    ' إلى ' .
                    $this->statusLabel($lead->status) .
                    '.',
                oldValues: [
                    'status' => $lead->getOriginal('status'),
                    'status_label' => $this->statusLabel($lead->getOriginal('status')),
                ],
                newValues: [
                    'status' => $lead->status,
                    'status_label' => $this->statusLabel($lead->status),
                ]
            );

            unset($changes['status']);
        }

        unset(
            $changes['converted_client_id'],
            $changes['converted_at'],
            $changes['deleted_at']
        );

        if (! empty($changes)) {
            $oldValues = [];
            $newValues = [];
            $fields = [];

            foreach ($changes as $field => $value) {
                $oldValues[$field] = $lead->getOriginal($field);
                $newValues[$field] = $lead->{$field};
                $fields[] = $this->fieldLabel($field);
            }

            $lead->logActivity(
                event: 'updated',
                title: 'تم تحديث بيانات الـ Lead',
                description: 'تم تحديث: ' . implode('، ', $fields) . '.',
                oldValues: $oldValues,
                newValues: $newValues
            );
        }
    }

    private function fieldLabel(string $field): string
    {
        return match ($field) {
            'name' => 'الاسم',
            'company' => 'الشركة',
            'email' => 'البريد الإلكتروني',
            'mobile' => 'الموبايل',
            'phone' => 'الهاتف',
            'city' => 'المدينة',
            'source' => 'المصدر',
            'notes' => 'الملاحظات',
            default => $field,
        };
    }

    private function statusLabel(?string $status): string
    {
        return match ($status) {
            'new' => 'جديد',
            'contacted' => 'تم التواصل',
            'qualified' => 'مؤهل',
            'unqualified' => 'غير مؤهل',
            'converted' => 'تم تحويله',
            'lost' => 'مفقود',
            default => 'غير معروف',
        };
    }
}