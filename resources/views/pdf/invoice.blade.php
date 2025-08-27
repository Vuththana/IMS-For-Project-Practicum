<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice #{{ $sale->invoice_number }}</title>
    <style>
        @page {
            size: A4;
            margin: 20mm 15mm; /* Standard A4 margins */
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif; /* DejaVu Sans for better DomPDF compatibility */
            margin: 0;
            padding: 0;
            color: #374151; /* Equivalent to text-gray-700 */
            font-size: 11px; /* Slightly smaller base font for PDF */
            line-height: 1.4;
            background: #fff;
        }

        .container {
            width: 100%;
            margin: 0 auto;
            box-sizing: border-box;
        }

        /* Clearfix for floated elements */
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        /* Header Section */
        .header-section {
            padding-bottom: 15px;
            border-bottom: 1px solid #e5e7eb; /* gray-200 */
            margin-bottom: 20px;
        }

        .company-info-block {
            float: left;
            width: 60%;
        }

        .invoice-info-block {
            float: right;
            width: 38%; /* Leave some gap */
            text-align: right;
        }

        .company-logo {
            max-height: 60px; /* Control max height */
            max-width: 180px; /* Control max width */
            margin-bottom: 10px;
            object-fit: contain;
        }

        .company-name {
            font-size: 18px;
            font-weight: 600; /* semibold */
            color: #1d4ed8; /* primary-600 (example) */
            margin: 0 0 5px 0;
        }

        .company-address p, .customer-address p {
            margin: 2px 0;
            color: #6b7280; /* gray-500 */
        }

        .invoice-title {
            font-size: 24px;
            font-weight: 600; /* semibold */
            color: #1d4ed8; /* primary-600 */
            margin: 0 0 5px 0;
            text-align: right;
        }
        .invoice-meta p {
            margin: 3px 0;
            font-size: 10px;
            text-align: right;
        }
        .invoice-meta span.label {
            color: #4b5563; /* gray-600 */
        }
        .invoice-meta span.value {
            font-weight: 600; /* semibold */
        }


        /* Customer Info */
        .customer-section {
            padding-bottom: 15px;
            border-bottom: 1px solid #e5e7eb; /* gray-200 */
            margin-bottom: 20px;
        }

        .billed-to-block, .delivery-details-block {
            float: left;
            width: 48%;
            box-sizing: border-box;
        }
        .delivery-details-block {
            float: right;
            /* text-align: right; */ /* Let content align left within this block */
        }

        .info-box-title {
            font-size: 10px;
            font-weight: 600; /* semibold */
            color: #6b7280; /* gray-500 */
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .info-box-content {
            background: #f9fafb; /* gray-50 */
            padding: 8px;
            border-radius: 4px;
            font-size: 10px;
        }
        .info-box-content p {
            margin: 2px 0;
        }
        .info-box-content strong {
            font-weight: 600;
            color: #374151; /* gray-700 */
        }


        /* Items Table */
        .items-section {
            /* padding: 15px 0; */ /* Padding handled by container */
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th,
        .items-table td {
            padding: 7px 8px;
            border: 1px solid #e5e7eb; /* gray-200 */
            text-align: left;
            vertical-align: top;
        }
        .items-table th {
            background: #f3f4f6; /* gray-100 */
            font-weight: 600; /* semibold */
            text-transform: uppercase;
            font-size: 9px;
        }
        .items-table td {
            font-size: 10px;
        }
        .items-table .text-right { text-align: right; }
        .items-table .text-center { text-align: center; }

        .product-name {
            font-weight: 600; /* semibold */
            color: #1f2937; /* gray-800 */
        }
        .product-desc {
            font-size: 9px;
            color: #6b7280; /* gray-500 */
        }

        /* Totals Section */
        .totals-wrapper { /* To help with clearing floats before it */
            /* No specific style needed other than to contain the floated totals-section */
        }
        .totals-section {
            float: right;
            width: 220px; /* Adjust as needed */
            margin-top: 10px;
            background: #f9fafb; /* gray-50 */
            padding: 10px;
            border-radius: 4px;
        }
        .total-row {
            /* Using table for totals for better alignment in DomPDF */
        }
        .totals-section table {
            width: 100%;
        }
        .totals-section td {
            padding: 4px 0;
            font-size: 10px;
        }
        .totals-section td.label {
            text-align: left;
            color: #4b5563; /* gray-600 */
        }
        .totals-section td.value {
            text-align: right;
            font-weight: 600; /* semibold */
        }
        .totals-section tr.grand-total td {
            font-size: 12px;
            font-weight: bold;
            color: #1d4ed8; /* primary-600 */
            border-top: 1px solid #e5e7eb; /* gray-200 */
            padding-top: 6px;
        }


        /* Footer */
        .footer-section {
            padding: 15px 0;
            text-align: center;
            color: #6b7280; /* gray-500 */
            border-top: 1px solid #e5e7eb; /* gray-200 */
            margin-top: 30px;
            font-size: 10px;
        }
        .footer-section p {
            margin: 2px 0;
        }

        /* Utility Classes (keep simple) */
        .font-semibold { font-weight: 600; }
        .text-primary { color: #1d4ed8; } /* Example primary color */
        .text-muted { color: #6b7280; }
        .text-danger { color: #dc2626; } /* Example danger color */

        /* Print optimization - DomPDF usually handles this well with @page */
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-section clearfix">
            <div class="company-info-block">
                @if($sale->company?->profile?->logo)
                    {{-- IMPORTANT: For DomPDF, use public_path() or base64 for local images --}}
                    <img src="{{ public_path(str_replace(url('/'), '', asset('storage/' . $sale->company->profile->logo))) }}"
                         class="company-logo"
                         alt="{{ $sale->company->name }} Logo"
                         onerror="this.style.display='none';">
                @endif
                <h2 class="company-name">{{ $sale->company->name ?? 'Your Company' }}</h2>
                @if($sale->company?->profile)
                <div class="company-address">
                    <p>{{ $sale->company->profile->street_address ?? '' }}</p>
                    <p>{{ ($sale->company->profile->city ?? '') . ($sale->company->profile->city && $sale->company->profile->state ? ', ' : '') . ($sale->company->profile->state ?? '') }}</p>
                    <p>{{ ($sale->company->profile->postal_code ?? '') . ($sale->company->profile->postal_code && $sale->company->profile->country ? ', ' : '') . ($sale->company->profile->country ?? '') }}</p>
                </div>
                @endif
            </div>

            <div class="invoice-info-block">
                <h1 class="invoice-title">INVOICE</h1>
                <div class="invoice-meta">
                    <p><span class="label">Issue Date:</span> <span class="value">{{ \Carbon\Carbon::parse($sale->sale_date)->format('M d, Y') }}</span></p>
                    <p><span class="label">Due Date:</span> <span class="value">{{ \Carbon\Carbon::parse($sale->sale_date)->addDays(30)->format('M d, Y') }}</span></p>
                    <p><span class="label">Invoice #:</span> <span class="value">{{ $sale->invoice_number }}</span></p>
                </div>
            </div>
        </div>

        <div class="customer-section clearfix">
            <div class="billed-to-block">
                <h3 class="info-box-title">Billed To</h3>
                <div class="info-box-content">
                    @if($sale->customer)
                        <p><strong>{{ $sale->customer->name }}</strong></p>
                        <p>{{ $sale->customer->phone ?? 'N/A' }}</p>
                        <p>{{ $sale->customer->email ?? 'N/A' }}</p>
                        {{-- Add full address if available on customer model --}}
                        @if($sale->customer->address_line_1) <p>{{ $sale->customer->address_line_1 }}</p> @endif
                        @if($sale->customer->city || $sale->customer->state || $sale->customer->postal_code)
                        <p>{{ $sale->customer->city ?? '' }}{{ $sale->customer->city && $sale->customer->state ? ', ' : '' }}{{ $sale->customer->state ?? '' }} {{ $sale->customer->postal_code ?? '' }}</p>
                        @endif
                        @if($sale->customer->country) <p>{{ $sale->customer->country }}</p> @endif
                    @else
                        <p>Guest Customer</p>
                    @endif
                </div>
            </div>

            <div class="delivery-details-block">
                <h3 class="info-box-title">Delivery Details</h3>
                <div class="info-box-content">
                    <p>
                        Method: {{ $sale->delivery_method ?? 'N/A' }}<br> {{-- Assuming you have delivery_method on Sale model --}}
                        Delivered By: {{ $sale->deliverer->name ?? 'N/A' }} <br>
                        Status: {{ $sale->delivery_status instanceof \App\Enums\Delivery\DeliveryStatus 
    ? $sale->delivery_status->getLabel() 
    : ucfirst((string) ($sale->delivery_status ?? 'N/A')) }}

                    </p>
                </div>
            </div>
        </div>

        <div class="items-section">
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 40%;">Product</th>
                        <th class="text-center" style="width: 15%;">Quantity</th>
                        <th class="text-right" style="width: 20%;">Unit Price</th>
                        <th class="text-right" style="width: 20%;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @php $itemCounter = 1; @endphp
                    @foreach ($sale->saleItems as $item) {{-- Assuming $sale->saleItems is the relationship --}}
                    <tr>
                        <td>{{ $itemCounter++ }}</td>
                        <td>
                            <div class="product-name">{{ $item->product->name ?? 'N/A' }}</div>
                            @if($item->product?->description)
                            <div class="product-desc">{{ Str::limit($item->product->description, 50) }}</div>
                            @endif
                        </td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-right">${{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-right font-semibold">${{ number_format($item->total, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="totals-wrapper clearfix">
            <div class="totals-section">
                <table>
                    <tr>
                        <td class="label">Subtotal:</td>
                        <td class="value">${{ number_format($sale->saleItems->sum('total'), 2) }}</td>
                    </tr>
                    @if(isset($sale->discount) && $sale->discount > 0)
                    <tr>
                        <td class="label">Discount:</td>
                        <td class="value text-danger">-${{ number_format($sale->discount, 2) }}</td>
                    </tr>
                    @endif
                    @if(isset($sale->tax) && $sale->tax > 0)
                    <tr>
                        <td class="label">Tax:</td>
                        <td class="value">${{ number_format($sale->tax, 2) }}</td>
                    </tr>
                    @endif
                     @if(isset($sale->delivery_fee) && $sale->delivery_fee > 0)
                    <tr>
                        <td class="label">Delivery Fee:</td>
                        <td class="value">${{ number_format($sale->delivery_fee, 2) }}</td>
                    </tr>
                    @endif
                    <tr class="grand-total">
                        <td class="label">Total:</td>
                        <td class="value">${{ number_format($sale->total_amount, 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="clearfix"></div>


        <div class="footer-section">
            <p>Thank you for your business!</p>
            <p class="mt-2">
                {{ $sale->company->name ?? 'Your Company' }}
                @if($sale->company?->profile?->phone_number) | {{ $sale->company->profile->phone_number }} @endif
            </p>
        </div>
    </div>
</body>
</html>