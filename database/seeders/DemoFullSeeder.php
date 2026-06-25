<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\ClientFollowup;
use App\Models\Lead;
use App\Models\LeadFollowup;
use App\Models\Member;
use App\Models\Payment;
use App\Models\Project;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Service;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\TaskComment;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use App\Models\ProjectAttachment;
use App\Models\ProjectComment;

class DemoFullSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Starting full demo seeding...');

        $this->seedUsers();
        $this->seedServices();
        $this->seedTeams();
        $this->seedMembers();
        $this->seedClients();
        $this->seedLeads();
        $this->seedFollowups();
        $this->seedProjects();
        $this->seedProjectCommentsAndAttachments();
        $this->seedTasks();
        $this->seedSalesAndPayments();

        $this->command->info('Full demo seeding completed successfully.');
    }

    private function seedUsers(): void
    {
        $role = Role::query()->firstOrCreate([
            'name' => 'SEO Manager',
            'guard_name' => 'web',
        ]);

        $users = [
            ['name' => 'Admin Demo', 'email' => 'admin@demo.com'],
            ['name' => 'Maya SEO', 'email' => 'maya@demo.com'],
            ['name' => 'Islam Manager', 'email' => 'islam@demo.com'],
            ['name' => 'Ahmed Sales', 'email' => 'ahmed@demo.com'],
            ['name' => 'Sara Designer', 'email' => 'sara@demo.com'],
            ['name' => 'Omar Developer', 'email' => 'omar@demo.com'],
            ['name' => 'Nour Social', 'email' => 'nour@demo.com'],
            ['name' => 'Hassan Support', 'email' => 'hassan@demo.com'],
            ['name' => 'Yara Content', 'email' => 'yara@demo.com'],
            ['name' => 'Karim Ads', 'email' => 'karim@demo.com'],
        ];

        foreach ($users as $index => $user) {
            $createdUser = User::query()->updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => Hash::make('password'),
                    'phone' => '010000000' . $index,
                    'status' => true,
                    'email_verified_at' => now(),
                ]
            );

            if ($index === 0) {
                $createdUser->assignRole($role);
            }
        }
    }

    private function seedServices(): void
    {
        $services = [
            ['name' => 'SEO Service', 'code' => 'SEO', 'default_price' => 12000],
            ['name' => 'Website Design', 'code' => 'WEB', 'default_price' => 25000],
            ['name' => 'E-commerce Store', 'code' => 'STORE', 'default_price' => 45000],
            ['name' => 'Social Media Management', 'code' => 'SOCIAL', 'default_price' => 10000],
            ['name' => 'Google Ads Management', 'code' => 'GADS', 'default_price' => 8000],
            ['name' => 'Meta Ads Management', 'code' => 'MADS', 'default_price' => 8000],
            ['name' => 'Branding Package', 'code' => 'BRAND', 'default_price' => 18000],
            ['name' => 'Content Writing', 'code' => 'CONTENT', 'default_price' => 6000],
            ['name' => 'CRM System', 'code' => 'CRM', 'default_price' => 70000],
            ['name' => 'Clinic Management System', 'code' => 'CLINIC', 'default_price' => 55000],
        ];

        foreach ($services as $service) {
            Service::query()->updateOrCreate(
                ['code' => $service['code']],
                [
                    'name' => $service['name'],
                    'description' => 'خدمة تجريبية لاختبار السيستم',
                    'default_price' => $service['default_price'],
                    'status' => true,
                ]
            );
        }
    }

    private function seedTeams(): void
    {
        $teams = [
            ['name' => 'فريق البرمجة', 'code' => 'programming'],
            ['name' => 'فريق SEO', 'code' => 'seo'],
            ['name' => 'فريق السيلز', 'code' => 'sales'],
            ['name' => 'فريق التصميم', 'code' => 'design'],
            ['name' => 'فريق السوشيال ميديا', 'code' => 'social_media'],
            ['name' => 'فريق الدعم الفني', 'code' => 'support'],
            ['name' => 'فريق المحتوى', 'code' => 'content'],
            ['name' => 'فريق الإعلانات', 'code' => 'ads'],
            ['name' => 'فريق إدارة المشاريع', 'code' => 'project_management'],
            ['name' => 'فريق خدمة العملاء', 'code' => 'customer_success'],
        ];

        foreach ($teams as $team) {
            $createdTeam = Team::query()->updateOrCreate(
                ['code' => $team['code']],
                [
                    'name' => $team['name'],
                    'role_name' => 'team_' . $team['code'],
                    'description' => 'فريق تجريبي لاختبار الصلاحيات والتشغيل',
                    'status' => true,
                ]
            );

            $createdTeam->ensureRole();
        }
    }

    private function seedMembers(): void
    {
        $users = User::query()->where('email', '!=', 'admin@demo.com')->get()->values();
        $teams = Team::query()->get()->values();

        foreach ($users as $index => $user) {
            $team = $teams[$index % $teams->count()];

            $member = Member::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'team_id' => $team->id,
                    'name' => $user->name,
                    'job_title' => $index % 3 === 0 ? 'Team Manager' : 'Specialist',
                    'department' => $team->name,
                    'email' => $user->email,
                    'phone' => '011000000' . $index,
                    'mobile' => '012000000' . $index,
                    'hire_date' => now()->subDays(60 - $index)->toDateString(),
                    'is_manager' => $index % 3 === 0,
                    'status' => 'active',
                    'notes' => 'عضو تجريبي لاختبار السيستم',
                ]
            );

            $member->load(['user', 'team']);
            $member->syncUserTeamRole();
        }

        foreach (Team::query()->get() as $team) {
            $manager = Member::query()
                ->where('team_id', $team->id)
                ->where('is_manager', true)
                ->first();

            if (! $manager) {
                $manager = Member::query()
                    ->where('team_id', $team->id)
                    ->first();

                if ($manager) {
                    $manager->update(['is_manager' => true]);
                }
            }

            if ($manager) {
                $team->update(['manager_member_id' => $manager->id]);

                Member::query()
                    ->where('team_id', $team->id)
                    ->where('id', '!=', $manager->id)
                    ->update(['manager_id' => $manager->id]);
            }
        }
    }

    private function seedClients(): void
    {
        $members = Member::query()->whereNotNull('user_id')->get()->values();

        $clients = [
            ['name' => 'شركة النور الطبية', 'company' => 'Al Nour Medical'],
            ['name' => 'مركز الحياة كلينك', 'company' => 'Life Clinic'],
            ['name' => 'مطعم لقمة بلدي', 'company' => 'Loqma Balady'],
            ['name' => 'شركة المستقبل العقارية', 'company' => 'Future Real Estate'],
            ['name' => 'متجر رين سمارت', 'company' => 'Reine Smart'],
            ['name' => 'عيادات سمايل', 'company' => 'Lasting Smile'],
            ['name' => 'شركة ديستركت', 'company' => 'District 4'],
            ['name' => 'مؤسسة حلول', 'company' => 'Holol'],
            ['name' => 'شركة Spring Flower', 'company' => 'Spring Flower'],
            ['name' => 'شركة السائح', 'company' => 'Al Saeh'],
        ];

        foreach ($clients as $index => $client) {
            $member = $members->isNotEmpty() ? $members[$index % $members->count()] : null;

            Client::query()->updateOrCreate(
                ['email' => 'client' . ($index + 1) . '@demo.com'],
                [
                    'name' => $client['name'],
                    'company' => $client['company'],
                    'phone' => '020000000' . $index,
                    'mobile' => '010100000' . $index,
                    'city' => ['القاهرة', 'الجيزة', 'الإسكندرية', 'الرياض', 'جدة'][$index % 5],
                    'source' => ['facebook', 'website', 'referral', 'google', 'whatsapp'][$index % 5],
                    'status' => ['new', 'active', 'inactive', 'lost'][$index % 4],
                    'assigned_to' => $member?->user_id,
                    'notes' => 'عميل تجريبي لاختبار CRM',
                ]
            );
        }
    }

    private function seedLeads(): void
    {
        $members = Member::query()->whereNotNull('user_id')->get()->values();

        $leads = [
            ['name' => 'Lead Ahmed Clinic', 'company' => 'Ahmed Clinic'],
            ['name' => 'Lead Store Plus', 'company' => 'Store Plus'],
            ['name' => 'Lead Beauty Center', 'company' => 'Beauty Center'],
            ['name' => 'Lead Real Estate Pro', 'company' => 'Real Estate Pro'],
            ['name' => 'Lead Smart Edu', 'company' => 'Smart Edu'],
            ['name' => 'Lead Dental Care', 'company' => 'Dental Care'],
            ['name' => 'Lead Food House', 'company' => 'Food House'],
            ['name' => 'Lead Media Hub', 'company' => 'Media Hub'],
            ['name' => 'Lead Tech Lab', 'company' => 'Tech Lab'],
            ['name' => 'Lead Fitness Zone', 'company' => 'Fitness Zone'],
        ];

        $statuses = ['new', 'contacted', 'qualified', 'unqualified', 'lost'];

        foreach ($leads as $index => $lead) {
            $member = $members->isNotEmpty() ? $members[$index % $members->count()] : null;

            Lead::query()->updateOrCreate(
                ['email' => 'lead' . ($index + 1) . '@demo.com'],
                [
                    'name' => $lead['name'],
                    'company' => $lead['company'],
                    'phone' => '030000000' . $index,
                    'mobile' => '011100000' . $index,
                    'city' => ['القاهرة', 'الجيزة', 'الإسكندرية', 'المنصورة', 'طنطا'][$index % 5],
                    'source' => ['facebook', 'website', 'referral', 'google', 'whatsapp'][$index % 5],
                    'status' => $statuses[$index % count($statuses)],
                    'assigned_to' => $member?->user_id,
                    'notes' => 'Lead تجريبي لاختبار مراحل البيع',
                ]
            );
        }
    }

    private function seedFollowups(): void
    {
        $users = User::query()->get()->values();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Skipping followups seeding.');
            return;
        }

        foreach (Client::query()->get() as $clientIndex => $client) {
            for ($i = 1; $i <= 2; $i++) {
                ClientFollowup::query()->updateOrCreate(
                    [
                        'client_id' => $client->id,
                        'note' => 'متابعة عميل رقم ' . $i . ' للعميل: ' . $client->name,
                    ],
                    [
                        'user_id' => $users[($clientIndex + $i) % $users->count()]?->id,
                        'next_followup_at' => now()->addDays($i + 1)->setTime(12, 0)->toDateTimeString(),
                        'status' => ['pending', 'done', 'cancelled'][$i % 3],
                    ]
                );
            }
        }

        foreach (Lead::query()->get() as $leadIndex => $lead) {
            for ($i = 1; $i <= 2; $i++) {
                LeadFollowup::query()->updateOrCreate(
                    [
                        'lead_id' => $lead->id,
                        'note' => 'متابعة Lead رقم ' . $i . ' للـ Lead: ' . $lead->name,
                    ],
                    [
                        'user_id' => $users[($leadIndex + $i) % $users->count()]?->id,
                        'type' => ['call', 'whatsapp', 'meeting', 'note', 'email'][$i % 5],
                        'next_followup_at' => now()->addDays($i + 2)->setTime(14, 0)->toDateTimeString(),
                        'status' => ['pending', 'done', 'cancelled'][$i % 3],
                    ]
                );
            }
        }
    }

    private function seedProjects(): void
    {
        $clients = Client::query()->get()->values();
        $services = Service::query()->get()->values();
        $teams = Team::query()->get()->values();
        $members = Member::query()->where('status', 'active')->get()->values();
        $users = User::query()->get()->values();

        for ($i = 1; $i <= 10; $i++) {
            $client = $clients[($i - 1) % $clients->count()];
            $service = $services[($i - 1) % $services->count()];
            $team = $teams[($i - 1) % $teams->count()];

            $manager = $members->where('team_id', $team->id)->first()
                ?? $members[($i - 1) % $members->count()];

            Project::query()->updateOrCreate(
                [
                    'name' => 'مشروع تجريبي رقم ' . $i,
                    'client_id' => $client->id,
                ],
                [
                    'service_id' => $service->id,
                    'team_id' => $team->id,
                    'manager_member_id' => $manager?->id,
                    'created_by' => $users[($i - 1) % $users->count()]?->id,
                    'code' => 'PRJ-' . str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                    'description' => 'وصف تجريبي للمشروع رقم ' . $i,
                    'priority' => ['low', 'medium', 'high', 'urgent'][$i % 4],
                    'status' => ['new', 'planning', 'in_progress', 'on_hold', 'completed'][$i % 5],
                    'start_date' => now()->subDays($i * 2)->toDateString(),
                    'due_date' => now()->addDays($i * 3)->toDateString(),
                    'completed_date' => $i % 5 === 0 ? now()->subDay()->toDateString() : null,
                    'budget' => 10000 + ($i * 3500),
                    'notes' => 'ملاحظات داخلية للمشروع التجريبي',
                ]
            );
        }
    }
private function seedProjectCommentsAndAttachments(): void
{
    $projects = Project::query()->get();
    $members = Member::query()->where('status', 'active')->get()->values();
    $users = User::query()->get()->values();

    if ($projects->isEmpty() || $users->isEmpty()) {
        $this->command->warn('Projects or users are missing. Skipping project comments and attachments.');
        return;
    }

    foreach ($projects as $projectIndex => $project) {
        $user = $users[$projectIndex % $users->count()];
        $member = $members->isNotEmpty()
            ? $members[$projectIndex % $members->count()]
            : null;

        for ($i = 1; $i <= 2; $i++) {
            ProjectComment::query()->updateOrCreate(
                [
                    'project_id' => $project->id,
                    'comment' => 'تعليق تجريبي رقم ' . $i . ' على المشروع: ' . $project->name,
                ],
                [
                    'user_id' => $user->id,
                    'member_id' => $member?->id,
                ]
            );
        }

        ProjectAttachment::query()->updateOrCreate(
            [
                'project_id' => $project->id,
                'file_name' => 'demo-project-file-' . $project->id . '.pdf',
            ],
            [
                'user_id' => $user->id,
                'member_id' => $member?->id,
                'file_path' => 'demo/demo-project-file-' . $project->id . '.pdf',
                'file_type' => 'application/pdf',
                'file_size' => 250000 + ($projectIndex * 1000),
                'notes' => 'مرفق تجريبي للمشروع بدون ملف فعلي',
            ]
        );
    }
}
    private function seedTasks(): void
    {
        $projects = Project::query()->with('client')->get()->values();
        $clients = Client::query()->get()->values();
        $leads = Lead::query()->get()->values();
        $members = Member::query()->where('status', 'active')->get()->values();
        $users = User::query()->get()->values();

        for ($i = 1; $i <= 20; $i++) {
            $member = $members[($i - 1) % $members->count()];
            $user = $users[($i - 1) % $users->count()];

            $project = $i <= 10 ? $projects[($i - 1) % $projects->count()] : null;
            $client = $project?->client ?? ($i % 2 === 0 ? $clients[($i - 1) % $clients->count()] : null);
            $lead = ! $project && ! $client ? $leads[($i - 1) % $leads->count()] : null;

            $task = Task::query()->updateOrCreate(
                [
                    'title' => 'مهمة تجريبية رقم ' . $i,
                ],
                [
                    'assigned_member_id' => $member->id,
                    'created_by' => $user->id,
                    'client_id' => $client?->id,
                    'lead_id' => $lead?->id,
                    'project_id' => $project?->id,
                    'description' => 'وصف تجريبي للمهمة رقم ' . $i,
                    'priority' => ['low', 'medium', 'high', 'urgent'][$i % 4],
                    'status' => ['new', 'in_progress', 'review', 'completed', 'cancelled'][$i % 5],
                    'start_at' => now()->subDays($i)->toDateTimeString(),
                    'due_at' => now()->addDays($i - 5)->setTime(15, 0)->toDateTimeString(),
                    'completed_at' => $i % 5 === 3 ? now()->toDateTimeString() : null,
                    'notes' => 'ملاحظات داخلية على المهمة',
                ]
            );

            for ($c = 1; $c <= 2; $c++) {
                TaskComment::query()->updateOrCreate(
                    [
                        'task_id' => $task->id,
                        'comment' => 'تعليق تجريبي رقم ' . $c . ' على ' . $task->title,
                    ],
                    [
                        'user_id' => $user->id,
                        'member_id' => $member->id,
                    ]
                );
            }

            TaskAttachment::query()->updateOrCreate(
                [
                    'task_id' => $task->id,
                    'file_name' => 'demo-task-file-' . $i . '.pdf',
                ],
                [
                    'user_id' => $user->id,
                    'member_id' => $member->id,
                    'file_path' => 'demo/demo-task-file-' . $i . '.pdf',
                    'file_type' => 'application/pdf',
                    'file_size' => 120000 + ($i * 1000),
                    'notes' => 'مرفق تجريبي بدون ملف فعلي',
                ]
            );
        }
    }

    private function seedSalesAndPayments(): void
    {
        $clients = Client::query()->get()->values();
        $services = Service::query()->get()->values();
        $users = User::query()->get()->values();

        if ($clients->isEmpty() || $services->isEmpty() || $users->isEmpty()) {
            $this->command->warn('Clients, services, or users are missing. Skipping sales and payments seeding.');
            return;
        }

        for ($i = 1; $i <= 10; $i++) {
            $client = $clients[($i - 1) % $clients->count()];
            $service = $services[($i - 1) % $services->count()];
            $user = $users[($i - 1) % $users->count()];

            $subtotal = (float) ($service->default_price ?? (10000 + ($i * 2500)));
            $vat = round($subtotal * 0.14, 2);
            $total = $subtotal + $vat;

            $paymentMethod = ['cash', 'bank_transfer', 'instapay', 'vodafone_cash', 'other'][$i % 5];

            $sale = Sale::query()->updateOrCreate(
                [
                    'client_id' => $client->id,
                    'user_id' => $user->id,
                    'sold_at' => now()->subDays($i)->toDateString(),
                    'notes' => 'فاتورة تجريبية رقم ' . $i,
                ],
                [
                    'subtotal' => $subtotal,
                    'vat' => $vat,
                    'total' => $total,
                    'payment_method' => $paymentMethod,
                    'status' => $i % 3 === 0 ? 'paid' : 'partial',
                    'notes' => 'فاتورة تجريبية لاختبار المبيعات والمدفوعات',
                ]
            );

            SaleItem::query()->updateOrCreate(
                [
                    'sale_id' => $sale->id,
                    'service_id' => $service->id,
                ],
                [
                    'quantity' => 1,
                    'unit_price' => $subtotal,
                    'total' => $subtotal,
                    'notes' => 'خدمة تجريبية: ' . $service->name,
                ]
            );

            $paidAmount = $sale->status === 'paid'
                ? $total
                : round($total / 2, 2);

            Payment::query()->updateOrCreate(
                [
                    'sale_id' => $sale->id,
                    'user_id' => $user->id,
                    'paid_at' => now()->subDays($i - 1)->toDateString(),
                ],
                [
                    'amount' => $paidAmount,
                    'payment_method' => $paymentMethod,
                    'notes' => 'دفعة تجريبية لاختبار المدفوعات',
                ]
            );
        }
    }
}
