<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;
use App\Notifications\TaskDueNotification;
use Illuminate\Database\Seeder;

/**
 * Demo data for the dashboard tasks popup.
 *
 * php artisan db:seed --class=TaskPopupDemoSeeder
 * Set TASK_POPUP_EMAIL in .env to target another user (default: seo@hololcrm.test).
 */
class TaskPopupDemoSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('TASK_POPUP_EMAIL', 'seo@hololcrm.test');

        $user = User::query()->where('email', $email)->first();

        if (! $user || ! $user->member) {
            $this->command->warn("User {$email} not found or has no linked member.");

            return;
        }

        $assigner = User::query()
            ->where('id', '!=', $user->id)
            ->where('status', true)
            ->first() ?? $user;

        $newlyAssigned = [
            [
                'title' => 'مراجعة تقرير SEO الشهري للعميل',
                'description' => 'مراجعة أرقام الزيارات والكلمات المفتاحية قبل إرسال التقرير للعميل.',
                'priority' => 'high',
                'due_at' => now()->addDays(2)->setTime(14, 0),
            ],
            [
                'title' => 'تحديث الـ Meta Tags لصفحات الخدمات',
                'description' => 'تحديث العناوين والأوصاف لصفحات الخدمات حسب خطة الكلمات الجديدة.',
                'priority' => 'medium',
                'due_at' => now()->addDays(4)->setTime(12, 0),
            ],
            [
                'title' => 'تحليل منافسين لعميل جديد',
                'description' => 'تحليل أهم 5 منافسين من حيث الكلمات والروابط الخلفية.',
                'priority' => 'urgent',
                'due_at' => now()->addDay()->setTime(16, 0),
            ],
        ];

        $overdue = [
            [
                'title' => 'إرسال خطة المحتوى للعميل',
                'description' => 'الخطة جاهزة ومحتاجة مراجعة نهائية وإرسال.',
                'priority' => 'high',
                'status' => 'in_progress',
                'due_at' => now()->subDays(2)->setTime(11, 0),
            ],
            [
                'title' => 'إصلاح روابط 404 في الموقع',
                'description' => 'معالجة الروابط المكسورة اللي ظهرت في Search Console.',
                'priority' => 'urgent',
                'status' => 'new',
                'due_at' => now()->subDay()->setTime(17, 0),
            ],
        ];

        $dueToday = [
            [
                'title' => 'اجتماع متابعة مع فريق المحتوى',
                'description' => 'مراجعة المقالات المطلوبة لهذا الأسبوع.',
                'priority' => 'medium',
                'status' => 'new',
                'due_at' => today()->setTime(23, 0),
            ],
        ];

        foreach ($newlyAssigned as $data) {
            $task = $this->createTask($user, $assigner, $data + ['status' => 'new']);

            $user->notify(new TaskAssignedNotification($task, $assigner->name));
        }

        foreach (array_merge($overdue, $dueToday) as $data) {
            $this->createTask($user, $assigner, $data);
        }

        // The login popup for today/overdue tasks fires once per day; push today's reminder
        // back so the next login creates a fresh one and shows the popup again.
        $user->notifications()
            ->where('type', TaskDueNotification::class)
            ->whereDate('created_at', today())
            ->update(['created_at' => now()->subDay()]);

        $this->command->info("Task popup demo data seeded for {$email}. Log out and log in again to see the popup.");
    }

    private function createTask(User $user, User $assigner, array $data): Task
    {
        $task = Task::query()->updateOrCreate(
            ['title' => $data['title'], 'created_by' => $assigner->id],
            $data + ['start_at' => now()->subDays(3)]
        );

        $task->assignedMembers()->syncWithoutDetaching([$user->member->id]);

        return $task;
    }
}
