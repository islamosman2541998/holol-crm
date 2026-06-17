<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServicesSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'name' => 'SMS Marketing',
                'code' => 'sms',
                'description' => 'خدمة إرسال رسائل SMS للعملاء والحملات الترويجية.',
                'default_price' => 0.35,
                'status' => true,
            ],
            [
                'name' => 'WhatsApp Campaigns',
                'code' => 'whatsapp',
                'description' => 'إرسال حملات واتساب للعملاء مع إمكانية المرفقات.',
                'default_price' => 1.00,
                'status' => true,
            ],
            [
                'name' => 'Website Development',
                'code' => 'website',
                'description' => 'تصميم وتطوير مواقع إلكترونية للشركات والأنشطة التجارية.',
                'default_price' => 15000,
                'status' => true,
            ],
            [
                'name' => 'SEO',
                'code' => 'seo',
                'description' => 'تحسين ظهور الموقع في محركات البحث.',
                'default_price' => 5000,
                'status' => true,
            ],
            [
                'name' => 'Google Ads',
                'code' => 'google_ads',
                'description' => 'إدارة حملات إعلانات Google.',
                'default_price' => 4000,
                'status' => true,
            ],
            [
                'name' => 'Meta Ads',
                'code' => 'meta_ads',
                'description' => 'إدارة حملات Facebook و Instagram.',
                'default_price' => 4000,
                'status' => true,
            ],
            [
                'name' => 'Social Media Management',
                'code' => 'smm',
                'description' => 'إدارة صفحات السوشيال ميديا وخطة المحتوى.',
                'default_price' => 6000,
                'status' => true,
            ],
            [
                'name' => 'Graphic Design',
                'code' => 'graphic_design',
                'description' => 'تصميمات سوشيال ميديا وهوية بصرية ومواد دعائية.',
                'default_price' => 3000,
                'status' => true,
            ],
            [
                'name' => 'Printing',
                'code' => 'printing',
                'description' => 'خدمات الطباعة والمطبوعات الدعائية.',
                'default_price' => 1000,
                'status' => true,
            ],
            [
                'name' => 'Email Marketing',
                'code' => 'email_marketing',
                'description' => 'إعداد وإرسال حملات بريد إلكتروني تسويقية.',
                'default_price' => 2500,
                'status' => true,
            ],
        ];

        foreach ($services as $service) {
            Service::query()->updateOrCreate(
                ['code' => $service['code']],
                $service
            );
        }
    }
}