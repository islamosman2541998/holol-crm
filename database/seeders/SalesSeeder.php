<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Sale;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SalesSeeder extends Seeder
{
    public function run(): void
    {
        $clients = Client::query()->get();
        $users = User::query()->where('status', true)->get();
        $services = Service::query()->where('status', true)->get();

        if ($clients->isEmpty()) {
            $this->command->warn('No clients found. Please run ClientsSeeder first.');
            return;
        }

        if ($users->isEmpty()) {
            $this->command->warn('No active users found. Please run RolesAndPermissionsSeeder first.');
            return;
        }

        if ($services->isEmpty()) {
            $this->command->warn('No services found. Please run ServicesSeeder first.');
            return;
        }

        $salesData = [
            [
                'client_index' => 0,
                'status' => 'paid',
                'payment_method' => 'cash',
                'vat' => 500,
                'sold_at' => now()->subDays(2)->toDateString(),
                'notes' => 'عملية بيع لخدمات موقع إلكتروني وحملة إعلانات.',
                'items' => [
                    ['code' => 'website', 'quantity' => 1],
                    ['code' => 'meta_ads', 'quantity' => 1],
                ],
            ],
            [
                'client_index' => 1,
                'status' => 'partial',
                'payment_method' => 'bank_transfer',
                'vat' => 300,
                'sold_at' => now()->subDays(5)->toDateString(),
                'notes' => 'عميل دفع جزء من قيمة خدمات السوشيال ميديا.',
                'items' => [
                    ['code' => 'smm', 'quantity' => 1],
                    ['code' => 'graphic_design', 'quantity' => 1],
                ],
            ],
            [
                'client_index' => 2,
                'status' => 'pending',
                'payment_method' => 'instapay',
                'vat' => 0,
                'sold_at' => now()->subDays(1)->toDateString(),
                'notes' => 'بانتظار تأكيد الدفع.',
                'items' => [
                    ['code' => 'seo', 'quantity' => 1],
                    ['code' => 'google_ads', 'quantity' => 1],
                ],
            ],
            [
                'client_index' => 3,
                'status' => 'paid',
                'payment_method' => 'vodafone_cash',
                'vat' => 100,
                'sold_at' => now()->subDays(10)->toDateString(),
                'notes' => 'خدمات SMS و WhatsApp لحملة قصيرة.',
                'items' => [
                    ['code' => 'sms', 'quantity' => 1000],
                    ['code' => 'whatsapp', 'quantity' => 500],
                ],
            ],
            [
                'client_index' => 4,
                'status' => 'cancelled',
                'payment_method' => 'other',
                'vat' => 0,
                'sold_at' => now()->subDays(7)->toDateString(),
                'notes' => 'تم إلغاء عملية البيع بناءً على طلب العميل.',
                'items' => [
                    ['code' => 'printing', 'quantity' => 1],
                ],
            ],
            [
                'client_index' => 5,
                'status' => 'paid',
                'payment_method' => 'cash',
                'vat' => 250,
                'sold_at' => now()->subDays(3)->toDateString(),
                'notes' => 'خدمات تصميم وإدارة محتوى.',
                'items' => [
                    ['code' => 'graphic_design', 'quantity' => 2],
                    ['code' => 'smm', 'quantity' => 1],
                ],
            ],
            [
                'client_index' => 6,
                'status' => 'pending',
                'payment_method' => 'bank_transfer',
                'vat' => 450,
                'sold_at' => now()->toDateString(),
                'notes' => 'عرض خدمات SEO وموقع إلكتروني.',
                'items' => [
                    ['code' => 'website', 'quantity' => 1],
                    ['code' => 'seo', 'quantity' => 1],
                ],
            ],
            [
                'client_index' => 7,
                'status' => 'partial',
                'payment_method' => 'instapay',
                'vat' => 150,
                'sold_at' => now()->subDays(4)->toDateString(),
                'notes' => 'حملة إعلانات Google و Email Marketing.',
                'items' => [
                    ['code' => 'google_ads', 'quantity' => 1],
                    ['code' => 'email_marketing', 'quantity' => 1],
                ],
            ],
        ];

        DB::transaction(function () use ($salesData, $clients, $users, $services) {
            foreach ($salesData as $index => $saleData) {
                $client = $clients[$saleData['client_index'] % $clients->count()];
                $user = $users[$index % $users->count()];

                $subtotal = 0;
                $itemsToCreate = [];

                foreach ($saleData['items'] as $itemData) {
                    $service = $services->firstWhere('code', $itemData['code']);

                    if (! $service) {
                        continue;
                    }

                    $quantity = $itemData['quantity'];
                    $unitPrice = $service->default_price;
                    $itemTotal = $quantity * $unitPrice;

                    $subtotal += $itemTotal;

                    $itemsToCreate[] = [
                        'service_id' => $service->id,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total' => $itemTotal,
                        'notes' => null,
                    ];
                }

                if (empty($itemsToCreate)) {
                    continue;
                }

                $vat = $saleData['vat'];
                $total = $subtotal + $vat;

                $sale = Sale::query()->create([
                    'client_id' => $client->id,
                    'user_id' => $user->id,
                    'subtotal' => $subtotal,
                    'vat' => $vat,
                    'total' => $total,
                    'payment_method' => $saleData['payment_method'],
                    'status' => $saleData['status'],
                    'sold_at' => $saleData['sold_at'],
                    'notes' => $saleData['notes'],
                ]);

                foreach ($itemsToCreate as $item) {
                    $sale->items()->create($item);
                }
            }
        });
    }
}