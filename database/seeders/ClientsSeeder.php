<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClientsSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::query()->where('status', true)->pluck('id')->toArray();

        if (empty($users)) {
            $this->command->warn('No active users found. Please seed users first.');
            return;
        }

        $clients = [
            [
                'name' => 'أحمد محمد',
                'company' => 'شركة النور للتجارة',
                'email' => 'ahmed@elnour.test',
                'phone' => '0223456789',
                'mobile' => '01012345678',
                'city' => 'القاهرة',
                'source' => 'Facebook',
                'status' => 'new',
                'notes' => 'مهتم بخدمات إدارة السوشيال ميديا ويريد عرض سعر.',
            ],
            [
                'name' => 'محمد علي',
                'company' => 'عيادة سما',
                'email' => 'mohamed@sama-clinic.test',
                'phone' => '0234567891',
                'mobile' => '01023456789',
                'city' => 'الجيزة',
                'source' => 'Referral',
                'status' => 'active',
                'notes' => 'عميل نشط، يحتاج متابعة شهرية لحملات Meta.',
            ],
            [
                'name' => 'سارة خالد',
                'company' => 'بيوتي سنتر روز',
                'email' => 'sara@rose-beauty.test',
                'phone' => null,
                'mobile' => '01034567890',
                'city' => 'الإسكندرية',
                'source' => 'Instagram',
                'status' => 'new',
                'notes' => 'طلبت تفاصيل عن باقات التصميم والمحتوى.',
            ],
            [
                'name' => 'كريم حسن',
                'company' => 'مطعم لقمة بلدي',
                'email' => 'karim@lokma.test',
                'phone' => '0245678912',
                'mobile' => '01045678901',
                'city' => 'القاهرة',
                'source' => 'Call',
                'status' => 'active',
                'notes' => 'يريد حملة إعلانات لزيادة الطلبات أونلاين.',
            ],
            [
                'name' => 'منى إبراهيم',
                'company' => 'District 4',
                'email' => 'mona@district4.test',
                'phone' => null,
                'mobile' => '01056789012',
                'city' => 'القاهرة الجديدة',
                'source' => 'Website',
                'status' => 'inactive',
                'notes' => 'تم التواصل أكثر من مرة ولم يتم الرد مؤخرًا.',
            ],
            [
                'name' => 'حسام السيد',
                'company' => 'شركة الصائغ',
                'email' => 'hossam@elsaigh.test',
                'phone' => '0256789123',
                'mobile' => '01067890123',
                'city' => 'المنصورة',
                'source' => 'Facebook',
                'status' => 'lost',
                'notes' => 'اختار شركة أخرى بعد المقارنة.',
            ],
            [
                'name' => 'إيمان عادل',
                'company' => 'Spring Flower',
                'email' => 'eman@springflower.test',
                'phone' => null,
                'mobile' => '01078901234',
                'city' => 'طنطا',
                'source' => 'Instagram',
                'status' => 'new',
                'notes' => 'مهتمة بخدمات الهوية البصرية والموقع الإلكتروني.',
            ],
            [
                'name' => 'مصطفى جمال',
                'company' => 'المختبر Lab',
                'email' => 'mostafa@lab.test',
                'phone' => '0267891234',
                'mobile' => '01089012345',
                'city' => 'القاهرة',
                'source' => 'Referral',
                'status' => 'active',
                'notes' => 'عميل محتمل لخدمات SEO و Google Ads.',
            ],
            [
                'name' => 'نوران سمير',
                'company' => 'Rose Clinic',
                'email' => 'nouran@roseclinic.test',
                'phone' => null,
                'mobile' => '01090123456',
                'city' => 'الشيخ زايد',
                'source' => 'WhatsApp',
                'status' => 'active',
                'notes' => 'تحتاج خطة محتوى وإعلانات شهرية.',
            ],
            [
                'name' => 'ياسر فتحي',
                'company' => 'EG Plan Development',
                'email' => 'yasser@egplan.test',
                'phone' => '0278912345',
                'mobile' => '01101234567',
                'city' => 'القاهرة',
                'source' => 'LinkedIn',
                'status' => 'new',
                'notes' => 'طلب اجتماع لمناقشة تطوير موقع الشركة.',
            ],
        ];

        foreach ($clients as $index => $client) {
            Client::query()->updateOrCreate(
                [
                    'mobile' => $client['mobile'],
                ],
                [
                    ...$client,
                    'assigned_to' => $users[$index % count($users)],
                ]
            );
        }
    }
}