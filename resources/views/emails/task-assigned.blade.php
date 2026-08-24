<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تم تسنيد مهمة لك</title>
</head>
<body style="margin:0; padding:0; background-color:#f5f6fa; font-family: Tahoma, Arial, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f5f6fa; padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius:14px; overflow:hidden;">
                    <tr>
                        <td style="background-color:#0d6efd; padding:20px 28px;">
                            <span style="color:#ffffff; font-size:18px; font-weight:bold;">{{ setting('general.system_name', 'Holol CRM') }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px;">
                            <p style="font-size:16px; color:#111827; margin:0 0 8px;">
                                مرحبًا {{ $user->name }}،
                            </p>
                            <p style="font-size:14px; color:#6b7280; margin:0 0 24px;">
                                @if ($assignedByName)
                                    {{ $assignedByName }} سند لك مهمة جديدة.
                                @else
                                    تم تسنيد مهمة جديدة لك.
                                @endif
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding-bottom:10px;">
                                        <span style="display:inline-block; background-color:#dcfce7; color:#16a34a; font-size:13px; font-weight:bold; padding:4px 12px; border-radius:999px;">
                                            مهمة جديدة
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 14px; border:1px solid #e5e7eb; border-radius:10px; display:block;">
                                        <a href="{{ route('admin.tasks.show', $task) }}" style="color:#111827; font-size:14px; font-weight:600; text-decoration:none;">
                                            {{ $task->title }}
                                        </a>

                                        @if ($task->due_at)
                                            <div style="color:#6b7280; font-size:12px; margin-top:4px;">
                                                الموعد: {{ $task->due_at->format('Y-m-d H:i') }}
                                            </div>
                                        @endif

                                        @if ($task->description)
                                            <div style="color:#6b7280; font-size:12px; margin-top:8px;">
                                                {{ str($task->description)->limit(150) }}
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            </table>

                            <div style="margin-top:24px;">
                                <a href="{{ route('admin.tasks.show', $task) }}" style="display:inline-block; background-color:#0d6efd; color:#ffffff; font-size:14px; font-weight:600; text-decoration:none; padding:10px 20px; border-radius:8px;">
                                    عرض المهمة
                                </a>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 28px; background-color:#f9fafb; text-align:center;">
                            <span style="color:#9ca3af; font-size:12px;">
                                رسالة تلقائية من {{ setting('general.system_name', 'Holol CRM') }}
                            </span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
