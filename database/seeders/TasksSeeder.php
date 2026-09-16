<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Lead;
use App\Models\Member;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class TasksSeeder extends Seeder
{
    public function run(): void
    {
        $members = Member::query()->where('status', 'active')->get();
        $clients = Client::query()->get();
        $leads = Lead::query()->where('status', '!=', 'converted')->get();
        $users = User::query()->where('status', true)->get();

        if ($members->isEmpty()) {
            $this->command->warn('No members found. Please run MembersSeeder first.');

            return;
        }

        if ($users->isEmpty()) {
            $this->command->warn('No active users found.');

            return;
        }

        $tasks = [
            [
                'title' => 'متابعة عرض السعر مع العميل',
                'description' => 'التواصل مع العميل لمراجعة عرض السعر والرد على أي استفسارات.',
                'priority' => 'high',
                'status' => 'new',
                'start_at' => now()->toDateTimeString(),
                'due_at' => now()->addDay()->setTime(15, 0)->toDateTimeString(),
                'notes' => 'يفضل التواصل واتساب أولًا.',
            ],
            [
                'title' => 'تجهيز خطة SEO مبدئية',
                'description' => 'إعداد خطة SEO أولية للعميل تشمل الكلمات المقترحة والتحليل المبدئي.',
                'priority' => 'medium',
                'status' => 'in_progress',
                'start_at' => now()->subDay()->toDateTimeString(),
                'due_at' => now()->addDays(3)->setTime(13, 0)->toDateTimeString(),
                'notes' => null,
            ],
            [
                'title' => 'مراجعة بيانات Lead جديد',
                'description' => 'مراجعة بيانات العميل المحتمل وتحديد هل مناسب للتحويل أم لا.',
                'priority' => 'medium',
                'status' => 'review',
                'start_at' => now()->subDays(2)->toDateTimeString(),
                'due_at' => now()->addDay()->setTime(11, 0)->toDateTimeString(),
                'notes' => 'مراجعة المصدر والاحتياج.',
            ],
            [
                'title' => 'مكالمة عاجلة مع عميل متأخر',
                'description' => 'الاتصال بالعميل المتأخر للمتابعة وتحديد الخطوة القادمة.',
                'priority' => 'urgent',
                'status' => 'new',
                'start_at' => now()->subDays(3)->toDateTimeString(),
                'due_at' => now()->subDay()->setTime(12, 0)->toDateTimeString(),
                'notes' => 'المهمة متأخرة وتحتاج متابعة سريعة.',
            ],
        ];

        foreach ($tasks as $index => $taskData) {
            $member = $members[$index % $members->count()];
            $client = $clients->isNotEmpty() ? $clients[$index % $clients->count()] : null;
            $lead = $leads->isNotEmpty() ? $leads[$index % $leads->count()] : null;
            $user = $users[$index % $users->count()];

            $task = Task::query()->updateOrCreate(
                ['title' => $taskData['title']],
                [
                    ...$taskData,
                    'created_by' => $user->id,
                    'client_id' => $index % 2 === 0 ? $client?->id : null,
                    'lead_id' => $index % 2 !== 0 ? $lead?->id : null,
                    'completed_at' => $taskData['status'] === 'completed' ? now() : null,
                ]
            );

            $task->assignedMembers()->sync([$member->id]);
        }
    }
}
