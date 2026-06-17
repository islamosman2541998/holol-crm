<?php

namespace Database\Seeders;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Seeder;

class LeadsSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::query()
            ->where('status', true)
            ->pluck('id')
            ->toArray();

        if (empty($users)) {
            $this->command->warn('No active users found. Please seed users first.');
            return;
        }

        $leads = [
            [
                'name' => 'أحمد سمير',
                'company' => 'مركز فيجن الطبي',
                'email' => 'ahmed@vision-medical.test',
                'mobile' => '01011112222',
                'phone' => null,
                'city' => 'القاهرة',
                'source' => 'Facebook',
                'status' => 'new',
                'notes' => 'مهتم بنظام إدارة عيادات ويريد معرفة الباقات.',
            ],
            [
                'name' => 'دينا محمود',
                'company' => 'Beauty Zone',
                'email' => 'dina@beautyzone.test',
                'mobile' => '01022223333',
                'phone' => null,
                'city' => 'الجيزة',
                'source' => 'Instagram',
                'status' => 'contacted',
                'notes' => 'تم التواصل معها وتريد عرض سعر لإدارة السوشيال ميديا.',
            ],
            [
                'name' => 'محمود حسن',
                'company' => 'شركة العمران للتطوير',
                'email' => 'mahmoud@omran-dev.test',
                'mobile' => '01033334444',
                'phone' => '023334455',
                'city' => 'القاهرة الجديدة',
                'source' => 'Website',
                'status' => 'qualified',
                'notes' => 'Lead مؤهل لخدمات Website و SEO.',
            ],
            [
                'name' => 'سارة عادل',
                'company' => 'Rose Dental Clinic',
                'email' => 'sara@rosedental.test',
                'mobile' => '01044445555',
                'phone' => null,
                'city' => 'الشيخ زايد',
                'source' => 'Referral',
                'status' => 'qualified',
                'notes' => 'مرشحة قوية لخدمة Meta Ads ونظام إدارة العيادة.',
            ],
            [
                'name' => 'كريم فوزي',
                'company' => 'مطعم بيت الشاورما',
                'email' => null,
                'mobile' => '01055556666',
                'phone' => null,
                'city' => 'الإسكندرية',
                'source' => 'Call',
                'status' => 'unqualified',
                'notes' => 'الميزانية غير مناسبة حاليًا.',
            ],
            [
                'name' => 'نور خالد',
                'company' => 'نور ستور',
                'email' => 'nour@nourstore.test',
                'mobile' => '01066667777',
                'phone' => null,
                'city' => 'طنطا',
                'source' => 'Facebook',
                'status' => 'new',
                'notes' => 'مهتمة بمتجر إلكتروني وحملات إعلانات.',
            ],
            [
                'name' => 'محمد أشرف',
                'company' => 'المختبر الحديث',
                'email' => 'mohamed@modernlab.test',
                'mobile' => '01077778888',
                'phone' => '024445566',
                'city' => 'المنصورة',
                'source' => 'LinkedIn',
                'status' => 'contacted',
                'notes' => 'طلب اجتماع لمناقشة SEO و Google Ads.',
            ],
            [
                'name' => 'هند إبراهيم',
                'company' => 'Spring Flowers',
                'email' => 'hend@springflowers.test',
                'mobile' => '01088889999',
                'phone' => null,
                'city' => 'القاهرة',
                'source' => 'Instagram',
                'status' => 'lost',
                'notes' => 'اختارت التعامل مع شركة أخرى.',
            ],
            [
                'name' => 'إسلام عادل',
                'company' => 'Tech Gate',
                'email' => 'islam@techgate.test',
                'mobile' => '01099990000',
                'phone' => null,
                'city' => 'القاهرة',
                'source' => 'Website',
                'status' => 'new',
                'notes' => 'مهتم بخدمة Website Development.',
            ],
            [
                'name' => 'منى سامي',
                'company' => 'Clinic Care',
                'email' => 'mona@cliniccare.test',
                'mobile' => '01111112222',
                'phone' => null,
                'city' => 'الجيزة',
                'source' => 'WhatsApp',
                'status' => 'qualified',
                'notes' => 'تريد Demo لنظام إدارة العيادات.',
            ],
        ];

        foreach ($leads as $index => $lead) {
            Lead::query()->updateOrCreate(
                [
                    'mobile' => $lead['mobile'],
                ],
                [
                    ...$lead,
                    'assigned_to' => $users[$index % count($users)],
                ]
            );
        }
    }
}