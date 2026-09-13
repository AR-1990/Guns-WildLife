<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt {{ $sale->receipt_no }}</title>
    @php
        $companyName = $settings?->company_name ?: 'Guns & Wildlife';
        $companyPhone = $settings?->phone ?: '+92 300 0000000';
        $companyEmail = $settings?->email ?: 'info@gunswildlife.pk';
        $companyAddress = $settings?->address ?: 'Main Boulevard, Lahore, Pakistan';
        $companyLogo = null;
        if (filled($settings?->logo_path)) {
            $relativeLogoPath = 'storage/' . $settings->logo_path;
            $companyLogo = !empty($autoPrint)
                ? asset($relativeLogoPath)
                : public_path($relativeLogoPath);
        }
        $isPdf = !empty($isPdf);
    @endphp
    <style>
        @page {
            margin: 8mm 10mm;
        }

        body {
            margin: 0;
            background: #f4f6f8;
            font-family: Arial, Helvetica, sans-serif;
            color: #111827;
        }

        .receipt-page {
            max-width: 920px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .receipt-actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .receipt-btn {
            border: 1px solid #fe9874;
            background: #fe9874;
            color: #fff;
            padding: 0.8rem 1.2rem;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
        }

        .receipt-btn--light {
            background: #fff;
            color: #fe9874;
        }

        .receipt-card {
            background: #fff;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 8px 30px rgba(15, 23, 42, 0.08);
            border: 1px solid #f2d5ca;
            border-top: 5px solid #fe9874;
        }

        .receipt-card__head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid #f3f4f6;
            margin-bottom: 1.5rem;
        }

        .receipt-card__brand {
            display: flex;
            gap: 1rem;
            align-items: flex-start;
        }

        .receipt-card__logo {
            width: 78px;
            height: 78px;
            object-fit: contain;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 0.35rem;
            background: #fff;
        }

        .receipt-card__title {
            margin: 0 0 0.35rem;
            font-size: 1.8rem;
            font-weight: 700;
            color: #d66d46;
        }

        .receipt-card__meta {
            margin: 0;
            color: #6b7280;
            font-size: 0.95rem;
            line-height: 1.7;
        }

        .receipt-card__tag {
            text-align: right;
        }

        .receipt-card__tag h2 {
            margin: 0 0 0.35rem;
            font-size: 1.7rem;
            color: #d66d46;
        }

        .receipt-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .receipt-box {
            background: #fff8f4;
            border: 1px solid #f2d5ca;
            border-radius: 10px;
            padding: 1rem 1.1rem;
        }

        .receipt-box__label {
            display: block;
            font-size: 0.8rem;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 0.35rem;
        }

        .receipt-box__value {
            margin: 0;
            font-size: 1rem;
            font-weight: 600;
        }

        .receipt-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .receipt-table th,
        .receipt-table td {
            border: 1px solid #d1d5db;
            padding: 0.75rem;
            text-align: left;
            font-size: 0.92rem;
        }

        .receipt-table th {
            background: #fff1ea;
            color: #d66d46;
        }

        .receipt-summary {
            margin-top: 1.5rem;
            margin-left: auto;
            width: 100%;
            max-width: 380px;
            border: 1px solid #f2d5ca;
            border-radius: 12px;
            overflow: hidden;
        }

        .receipt-summary__row {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.85rem 1rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .receipt-summary__row:last-child {
            border-bottom: 0;
        }

        .receipt-summary__row--total {
            background: #fe9874;
            color: #fff;
            font-weight: 700;
            font-size: 1.05rem;
        }

        .receipt-footer {
            margin-top: 2rem;
            display: flex;
            justify-content: space-between;
            gap: 1.5rem;
            align-items: flex-end;
        }

        .receipt-footer__note {
            max-width: 60%;
            color: #6b7280;
            line-height: 1.6;
            font-size: 0.92rem;
        }

        .receipt-footer__sign {
            min-width: 220px;
            text-align: center;
        }

        .receipt-footer__line {
            border-top: 1px solid #d66d46;
            margin-top: 3rem;
            padding-top: 0.5rem;
            color: #4b5563;
            font-size: 0.9rem;
        }

        body.receipt-pdf {
            background: #fff;
            font-size: 13px;
        }

        body.receipt-pdf .receipt-page {
            max-width: 100%;
            margin: 0;
            padding: 0;
        }

        body.receipt-pdf .receipt-actions {
            display: none !important;
        }

        body.receipt-pdf .receipt-card {
            box-shadow: none;
            border-radius: 0;
            padding: 1rem 1.1rem;
        }

        body.receipt-pdf .receipt-card__head {
            gap: 1rem;
            padding-bottom: 0.9rem;
            margin-bottom: 0.9rem;
        }

        body.receipt-pdf .receipt-card__logo {
            width: 56px;
            height: 56px;
        }

        body.receipt-pdf .receipt-card__title {
            font-size: 1.4rem;
            margin-bottom: 0.2rem;
        }

        body.receipt-pdf .receipt-card__meta,
        body.receipt-pdf .receipt-box__label,
        body.receipt-pdf .receipt-box__value,
        body.receipt-pdf .receipt-table th,
        body.receipt-pdf .receipt-table td,
        body.receipt-pdf .receipt-summary__row,
        body.receipt-pdf .receipt-footer__note,
        body.receipt-pdf .receipt-footer__line {
            font-size: 0.78rem;
            line-height: 1.35;
        }

        body.receipt-pdf .receipt-card__tag h2 {
            font-size: 1.2rem;
            margin-bottom: 0.2rem;
        }

        body.receipt-pdf .receipt-grid {
            gap: 0.55rem;
            margin-bottom: 0.85rem;
        }

        body.receipt-pdf .receipt-box {
            padding: 0.55rem 0.7rem;
        }

        body.receipt-pdf .receipt-table {
            margin-top: 0.55rem;
        }

        body.receipt-pdf .receipt-table th,
        body.receipt-pdf .receipt-table td {
            padding: 0.38rem 0.45rem;
        }

        body.receipt-pdf .receipt-summary {
            margin-top: 0.8rem;
            max-width: 280px;
        }

        body.receipt-pdf .receipt-summary__row {
            padding: 0.45rem 0.7rem;
        }

        body.receipt-pdf .receipt-footer {
            margin-top: 0.9rem;
            gap: 1rem;
            align-items: flex-end;
        }

        body.receipt-pdf .receipt-footer__note {
            max-width: 58%;
        }

        body.receipt-pdf .receipt-footer__line {
            margin-top: 1.4rem;
            padding-top: 0.3rem;
        }

        @media print {
            body {
                background: #fff;
            }

            .receipt-page {
                max-width: 100%;
                margin: 0;
                padding: 0;
            }

            .receipt-actions {
                display: none !important;
            }

            .receipt-card {
                box-shadow: none;
                border: 0;
                border-radius: 0;
                padding: 0;
            }
        }
    </style>
</head>
<body class="{{ $isPdf ? 'receipt-pdf' : 'receipt-web' }}" @if (!empty($autoPrint)) onload="window.print()" @endif>
    <div class="receipt-page">
        <div class="receipt-actions">
            <button type="button" class="receipt-btn" onclick="window.print()">Print / Save PDF</button>
            <a href="{{ route('admin.sales.pdf', $sale) }}" class="receipt-btn">Download PDF</a>
            <a href="{{ route('admin.sales.show', $sale) }}" class="receipt-btn receipt-btn--light">Back</a>
        </div>

        <div class="receipt-card">
            <div class="receipt-card__head">
                <div class="receipt-card__brand">
                    @if ($companyLogo)
                        <img src="{{ $companyLogo }}" alt="Logo" class="receipt-card__logo">
                    @endif
                    <div>
                        <h1 class="receipt-card__title">{{ $companyName }}</h1>
                        <p class="receipt-card__meta">
                            {{ $companyAddress }}<br>
                            Phone: {{ $companyPhone }}<br>
                            Email: {{ $companyEmail }}
                        </p>
                    </div>
                </div>
                <div class="receipt-card__tag">
                    <h2>Sales Receipt</h2>
                    <p class="receipt-card__meta">
                        Receipt: {{ $sale->receipt_no }}<br>
                        Date: {{ $sale->sale_date?->format('d-M-Y') }}<br>
                        Status: {{ ucfirst($sale->status) }}
                    </p>
                </div>
            </div>

            <div class="receipt-grid">
                <div class="receipt-box">
                    <span class="receipt-box__label">Customer</span>
                    <p class="receipt-box__value">{{ $sale->customer_name }}</p>
                </div>
                <div class="receipt-box">
                    <span class="receipt-box__label">Phone Number</span>
                    <p class="receipt-box__value">{{ $sale->customer_phone ?: 'N/A' }}</p>
                </div>
                <div class="receipt-box">
                    <span class="receipt-box__label">Document Type</span>
                    <p class="receipt-box__value">{{ \Illuminate\Support\Str::title(str_replace('_', ' ', $sale->document_type)) }}</p>
                </div>
                <div class="receipt-box">
                    <span class="receipt-box__label">Reference / PO No.</span>
                    <p class="receipt-box__value">{{ $sale->reference ?: 'N/A' }}</p>
                </div>
                <div class="receipt-box">
                    <span class="receipt-box__label">Prepared By</span>
                    <p class="receipt-box__value">{{ $sale->creator?->name ?: 'System' }}</p>
                </div>
            </div>

            <table class="receipt-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Serial / Weapon</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sale->items as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->product_name }}</td>
                            <td>{{ $item->serial_number ?: ($item->productUnit?->weapon_number ?: 'N/A') }}</td>
                            <td>{{ $item->quantity }} {{ $item->unit }}</td>
                            <td>{{ number_format((float) $item->price, 2) }}</td>
                            <td>{{ number_format((float) $item->total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="receipt-summary">
                <div class="receipt-summary__row">
                    <span>Subtotal</span>
                    <strong>{{ number_format((float) $sale->subtotal, 2) }}</strong>
                </div>
                <div class="receipt-summary__row">
                    <span>Discount</span>
                    <strong>{{ number_format((float) $discountAmount, 2) }}</strong>
                </div>
                <div class="receipt-summary__row">
                    <span>Net Amount</span>
                    <strong>{{ number_format((float) $sale->grand_total, 2) }}</strong>
                </div>
                <div class="receipt-summary__row receipt-summary__row--total">
                    <span>Grand Total</span>
                    <strong>{{ number_format((float) $sale->grand_total, 2) }}</strong>
                </div>
            </div>

            <div class="receipt-footer">
                <div class="receipt-footer__note">
                    Thank you for your business. Use the print dialog destination as `Save as PDF` if you want a PDF copy of this receipt.
                </div>
                <div class="receipt-footer__sign">
                    <div class="receipt-footer__line">Authorized Signature</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
