<?php

namespace App\Observers;

use App\Models\Client;
use App\Models\User;

class ClientObserver
{
    public function created(Client $client): void
    {
        $client->logActivity(
            event: 'created',
            title: 'تم إنشاء العميل',
            description: 'تم إضافة العميل إلى النظام.',
            newValues: $client->only([
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

        if ($client->assigned_to) {
            $user = User::find($client->assigned_to);

            $client->logActivity(
                event: 'assigned',
                title: 'تم إسناد العميل',
                description: 'تم إسناد العميل إلى ' . ($user?->name ?? 'موظف غير معروف') . '.',
                oldValues: [
                    'assigned_to' => null,
                ],
                newValues: [
                    'assigned_to' => $client->assigned_to,
                    'assigned_to_name' => $user?->name,
                ]
            );
        }
    }

    public function updated(Client $client): void
    {
        $changes = $client->getChanges();

        unset($changes['updated_at']);

        if (empty($changes)) {
            return;
        }

        if (array_key_exists('assigned_to', $changes)) {
            $oldUser = User::find($client->getOriginal('assigned_to'));
            $newUser = User::find($client->assigned_to);

            $client->logActivity(
                event: 'assigned',
                title: 'تم تغيير الموظف المسؤول',
                description: 'تم تغيير المسؤول من ' .
                    ($oldUser?->name ?? 'بدون موظف') .
                    ' إلى ' .
                    ($newUser?->name ?? 'بدون موظف') .
                    '.',
                oldValues: [
                    'assigned_to' => $client->getOriginal('assigned_to'),
                    'assigned_to_name' => $oldUser?->name,
                ],
                newValues: [
                    'assigned_to' => $client->assigned_to,
                    'assigned_to_name' => $newUser?->name,
                ]
            );

            unset($changes['assigned_to']);
        }

        if (array_key_exists('status', $changes)) {
            $client->logActivity(
                event: 'status_changed',
                title: 'تم تغيير حالة العميل',
                description: 'تم تغيير الحالة من ' .
                    $this->statusLabel($client->getOriginal('status')) .
                    ' إلى ' .
                    $this->statusLabel($client->status) .
                    '.',
                oldValues: [
                    'status' => $client->getOriginal('status'),
                    'status_label' => $this->statusLabel($client->getOriginal('status')),
                ],
                newValues: [
                    'status' => $client->status,
                    'status_label' => $this->statusLabel($client->status),
                ]
            );

            unset($changes['status']);
        }

        unset($changes['deleted_at']);

        if (! empty($changes)) {
            $oldValues = [];
            $newValues = [];
            $fields = [];

            foreach ($changes as $field => $value) {
                $oldValues[$field] = $client->getOriginal($field);
                $newValues[$field] = $client->{$field};
                $fields[] = $this->fieldLabel($field);
            }

            $client->logActivity(
                event: 'updated',
                title: 'تم تحديث بيانات العميل',
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
            'active' => 'نشط',
            'inactive' => 'غير نشط',
            'lost' => 'مفقود',
            default => 'غير معروف',
        };
    }
}