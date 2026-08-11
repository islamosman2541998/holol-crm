@php
    $logoPath = setting('branding.system_logo')
        ? public_path('storage/' . setting('branding.system_logo'))
        : null;

    $companyName = setting('general.company_name', 'Holol');
    $systemName = setting('general.system_name', 'Holol CRM');
@endphp

<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">

    <style>
        @page {
            margin: 14mm 12mm 14mm 12mm;
            background-color: #F5F7FB;
        }

        body {
            font-family: dejavusanscondensed, dejavusans;
            direction: rtl;
            text-align: right;
            font-size: 12px;
            color: #101828;
            background: #F5F7FB;
            line-height: 1.9;
            margin: 0;
            padding: 0;
        }

        .page-bg-left {
            position: fixed;
            top: 0;
            left: 0;
            width: 18mm;
            height: 297mm;
            background: #0A1034;
            opacity: 0.08;
        }

        .page-bg-top {
            position: fixed;
            top: -35mm;
            right: -20mm;
            width: 95mm;
            height: 95mm;
            background: #E8ECF7;
            border-radius: 50%;
        }

        .page-bg-bottom {
            position: fixed;
            bottom: -45mm;
            left: -25mm;
            width: 110mm;
            height: 110mm;
            background: #EEF1F8;
            border-radius: 50%;
        }

        .watermark {
            position: fixed;
            top: 125mm;
            left: 20mm;
            font-size: 54px;
            font-weight: bold;
            color: #0A1034;
            opacity: 0.035;
            transform: rotate(-28deg);
            z-index: 0;
        }

        .document {
            position: relative;
            z-index: 2;
            width: 100%;
            background: #ffffff;
            padding: 0;
        }

        .top-bar {
            width: 100%;
            background: #0A1034;
            color: #ffffff;
            padding: 20px 24px;
            margin-bottom: 26px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            border: 0;
        }

        .header-table td {
            border: 0;
            padding: 0;
            vertical-align: middle;
        }

        .logo-cell {
            width: 35%;
            text-align: right;
        }

        .title-cell {
            width: 65%;
            text-align: left;
        }

        .logo {
            max-width: 125px;
            max-height: 62px;
        }

        .doc-title {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #ffffff;
            letter-spacing: 0.4px;
        }

        .doc-subtitle {
            font-size: 12px;
            color: #D9DEF0;
        }

        .content {
            padding: 0 18px 18px 18px;
        }

        .section-title {
            font-size: 15px;
            font-weight: bold;
            color: #0A1034;
            margin: 0 0 12px 0;
            padding: 0 0 8px 0;
            border-bottom: 1px solid #D9DEEA;
        }

        .info-wrapper {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 34px;
        }

        .info-wrapper td {
            width: 50%;
            vertical-align: top;
            border: 0;
            padding: 0;
        }

        .info-wrapper .right-col {
            padding-left: 9px;
        }

        .info-wrapper .left-col {
            padding-right: 9px;
        }

        .info-card {
            border: 1px solid #D9DEEA;
            background: #FBFCFF;
            padding: 18px 18px 16px 18px;
            min-height: 178px;
        }

        .info-card-title {
            font-size: 15px;
            font-weight: bold;
            color: #0A1034;
            margin-bottom: 14px;
            padding-bottom: 8px;
            border-bottom: 1px solid #E2E6F0;
        }

        .details-table {
            width: 100%;
            border-collapse: collapse;
        }

        .details-table td {
            border: 0;
            padding: 6px 0 7px 0;
            font-size: 12px;
            line-height: 2;
            vertical-align: top;
        }

        .details-table .label-cell {
            width: 38%;
            font-weight: bold;
            color: #344054;
            white-space: nowrap;
        }

        .details-table .value-cell {
            width: 62%;
            color: #101828;
            text-align: right;
            word-break: break-word;
        }

        .badge {
            display: inline-block;
            background: #EEF1FA;
            color: #0A1034;
            border: 1px solid #C9D1E6;
            padding: 4px 10px;
            font-size: 11px;
            font-weight: bold;
        }

        .items-section {
            margin-top: 12px;
            margin-bottom: 28px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            background: #ffffff;
        }

        .items-table th {
            background: #0A1034;
            color: #ffffff;
            border: 1px solid #0A1034;
            padding: 12px 9px;
            font-size: 11px;
            font-weight: bold;
            text-align: right;
        }

        .items-table td {
            border: 1px solid #D7DCE8;
            padding: 13px 9px;
            vertical-align: top;
            font-size: 11px;
            line-height: 1.9;
        }

        .items-table tr:nth-child(even) td {
            background: #F7F9FD;
        }

        .service-name {
            font-weight: bold;
            font-size: 12px;
            color: #101828;
        }

        .service-code {
            font-size: 10px;
            color: #667085;
            margin-top: 3px;
        }

        .number-cell {
            text-align: center;
            direction: ltr;
        }

        .money-cell {
            text-align: left;
            direction: ltr;
            white-space: nowrap;
        }

        .summary-area {
            width: 100%;
            margin-top: 22px;
        }

        .summary-table {
            width: 43%;
            border-collapse: collapse;
            margin-right: auto;
            margin-left: 0;
            background: #ffffff;
        }

        .summary-table td {
            border: 1px solid #D7DCE8;
            padding: 12px 13px;
            font-size: 12px;
        }

        .summary-label {
            background: #F2F4F9;
            font-weight: bold;
            color: #101828;
        }

        .summary-value {
            text-align: left;
            direction: ltr;
            font-weight: bold;
        }

        .total-row td {
            background: #0A1034;
            color: #ffffff;
            font-size: 14px;
            font-weight: bold;
        }

        .notes-box {
            margin-top: 16px;
            border: 1px solid #D9DEEA;
            background: #FBFCFF;
            padding: 16px;
            line-height: 2.1;
            color: #344054;
        }

        .terms-box {
            margin-top: 24px;
            border: 1px solid #D9DEEA;
            background: #F8FAFE;
            padding: 14px 16px;
            line-height: 2;
            font-size: 11px;
            color: #475467;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 26px;
        }

        .signature-table td {
            width: 50%;
            border: 0;
            padding: 0;
            vertical-align: top;
        }

        .signature-box {
            border-top: 1px solid #C9D1E6;
            padding-top: 8px;
            width: 70%;
            color: #667085;
            font-size: 11px;
        }

        .footer {
            margin-top: 30px;
            padding-top: 13px;
            border-top: 1px solid #D9DEEA;
            text-align: center;
            color: #667085;
            font-size: 10px;
            line-height: 1.9;
        }

        .spacer {
            height: 18px;
        }
    </style>
</head>

<body>
    <div class="page-bg-left"></div>
    <div class="page-bg-top"></div>
    <div class="page-bg-bottom"></div>
    <div class="watermark">QUOTATION</div>

    <div class="document">
        <div class="top-bar">
            <table class="header-table">
                <tr>
                    <td class="logo-cell">
                        @if ($logoPath && file_exists($logoPath))
                            <img src="{{ $logoPath }}" class="logo" alt="Logo">
                        @else
                            <strong>{{ $companyName }}</strong>
                        @endif
                    </td>

                    <td class="title-cell">
                        <div class="doc-title">عرض سعر</div>
                        {{-- <div class="doc-subtitle">
                            {{ $companyName }} | {{ $systemName }}
                        </div> --}}
                    </td>
                </tr>
            </table>
        </div>

        @php
            $contact = $quotation->client ?? $quotation->lead;
        @endphp

        <div class="content">
            <table class="info-wrapper">
                <tr>
                    <td class="right-col">
                        <div class="info-card">
                            <div class="info-card-title">{{ $quotation->client ? 'بيانات العميل' : 'بيانات Lead' }}</div>

                            <table class="details-table">
                                <tr>
                                    <td class="label-cell">الاسم</td>
                                    <td class="value-cell">{{ $contact?->name ?? '-' }}</td>
                                </tr>

                                <tr>
                                    <td class="label-cell">الشركة</td>
                                    <td class="value-cell">{{ $contact?->company ?? '-' }}</td>
                                </tr>

                                <tr>
                                    <td class="label-cell">الموبايل</td>
                                    <td class="value-cell">{{ $contact?->mobile ?? '-' }}</td>
                                </tr>

                                <tr>
                                    <td class="label-cell">الهاتف</td>
                                    <td class="value-cell">{{ $contact?->phone ?? '-' }}</td>
                                </tr>

                                <tr>
                                    <td class="label-cell">البريد الإلكتروني</td>
                                    <td class="value-cell">{{ $contact?->email ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </td>

                    <td class="left-col">
                        <div class="info-card">
                            <div class="info-card-title">بيانات عرض السعر</div>

                            <table class="details-table">
                                <tr>
                                    <td class="label-cell">رقم العرض</td>
                                    <td class="value-cell">{{ $quotation->quotation_number }}</td>
                                </tr>

                                <tr>
                                    <td class="label-cell">الحالة</td>
                                    <td class="value-cell">
                                        <span class="badge">{{ $quotation->status_label }}</span>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="label-cell">تاريخ العرض</td>
                                    <td class="value-cell">{{ $quotation->quotation_date?->format('Y-m-d') ?? '-' }}</td>
                                </tr>

                                <tr>
                                    <td class="label-cell">صالح حتى</td>
                                    <td class="value-cell">{{ $quotation->valid_until?->format('Y-m-d') ?? '-' }}</td>
                                </tr>

                                <tr>
                                    <td class="label-cell">أنشئ بواسطة</td>
                                    <td class="value-cell">{{ $quotation->user?->name ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </td>
                </tr>
            </table>

            <div class="items-section">
                <div class="section-title">تفاصيل الخدمات</div>

                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width: 34%;">الخدمة</th>
                            <th style="width: 10%;">الكمية</th>
                            <th style="width: 18%;">سعر الوحدة</th>
                            <th style="width: 18%;">الإجمالي</th>
                            <th style="width: 20%;">ملاحظات</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($quotation->items as $item)
                            <tr>
                                <td>
                                    <div class="service-name">{{ $item->service?->name ?? '-' }}</div>

                                    @if ($item->service?->code)
                                        <div class="service-code">{{ $item->service->code }}</div>
                                    @endif
                                </td>

                                <td class="number-cell">
                                    {{ $item->quantity }}
                                </td>

                                <td class="money-cell">
                                    {{ number_format($item->unit_price, 2) }}
                                </td>

                                <td class="money-cell">
                                    {{ number_format($item->total, 2) }}
                                </td>

                                <td>
                                    {{ $item->notes ?: '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align:center; padding:18px;">
                                    لا توجد خدمات داخل عرض السعر
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="summary-area">
                <table class="summary-table">
                    <tr>
                        <td class="summary-label">Subtotal</td>
                        <td class="summary-value">{{ number_format($quotation->subtotal, 2) }}</td>
                    </tr>

                    <tr>
                        <td class="summary-label">VAT</td>
                        <td class="summary-value">{{ number_format($quotation->vat, 2) }}</td>
                    </tr>

                    <tr class="total-row">
                        <td>Total</td>
                        <td class="summary-value">{{ number_format($quotation->total, 2) }}</td>
                    </tr>
                </table>
            </div>

            @if ($quotation->notes)
                <div class="spacer"></div>

                <div class="section-title">ملاحظات</div>

                <div class="notes-box">
                    {{ $quotation->notes }}
                </div>
            @endif

            {{-- <div class="terms-box">
                <strong>ملاحظات عامة:</strong>
                <br>
                هذا العرض صالح حتى التاريخ الموضح أعلاه، وقد تختلف الأسعار في حالة تغيير نطاق العمل أو إضافة خدمات جديدة.
                يتم تأكيد التنفيذ بعد اعتماد العرض وتسجيل الدفعة المتفق عليها.
            </div> --}}

            {{-- <table class="signature-table">
                <tr>
                    <td>
                        <div class="signature-box">
                            توقيع العميل
                        </div>
                    </td>

                    <td>
                        <div class="signature-box">
                            توقيع الشركة
                        </div>
                    </td>
                </tr>
            </table> --}}

            <div class="footer">
                تم إنشاء هذا المستند بواسطة {{ $systemName }}
                <br>
                {{ $companyName }}
            </div>
        </div>
    </div>
</body>
</html>