<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Purchase Order #{{ $purchase->invoice_number }}</title>
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

        .company-address p, .supplier-address p {
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

        /* Supplier Info */
        .supplier-section {
            padding-bottom: 15px;
            border-bottom: 1px solid #e5e7eb; /* gray-200 */
            margin-bottom: 20px;
        }

        .supplier-block, .shipping-details-block {
            float: left;
            width: 48%;
            box-sizing: border-box;
        }
        .shipping-details-block {
            float: right;
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
            margin-bottom: 20px;
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
        .totals-wrapper {
            margin-top: 10px;
        }
        .totals-section {
            float: right;
            width: 220px;
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

        /* Utility Classes */
        .font-semibold { font-weight: 600; }
        .text-primary { color: #1d4ed8; }
        .text-muted { color: #6b7280; }
        .text-danger { color: #dc2626; }

        /* Print optimization */
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
                @if($purchase->company?->profile?->logo)
                    <img src="{{ public_path(str_replace(url('/'), '', asset('storage/' . $purchase->company->profile->logo))) }}"
                         class="company-logo"
                         alt="{{ $purchase->company->name }} Logo"
                         onerror="this.style.display='none';">
                @endif
                <h2 class="company-name">{{ $purchase->company->name ?? 'Your Company' }}</h2>
                @if($purchase->company?->profile)
                <div class="company-address">
                    <p>{{ $purchase->company->profile->street_address ?? '' }}</p>
                    <p>{{ ($purchase->company->profile->city ?? '') . ($purchase->company->profile->city && $purchase->company->profile->state ? ', ' : '') . ($purchase->company->profile->state ?? '') }}</p>
                    <p>{{ ($purchase->company->profile->postal_code ?? '') . ($purchase->company->profile->postal_code && $purchase->company->profile->country ? ', ' : '') . ($purchase->company->profile->country ?? '') }}</p>
                </div>
                @endif
            </div>

            <div class="invoice-info-block">
                <h1 class="invoice-title">PURCHASE ORDER</h1>
                <div class="invoice-meta">
                    <p><span class="label">PO #:</span> <span class="value">{{ $purchase->invoice_number }}</span></p>
                    <p><span class="label">Order Date:</span> <span class="value">{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('M d, Y') }}</span></p>
                    <p><span class="label">Expected Delivery:</span> <span class="value">{{ \Carbon\Carbon::parse($purchase->expected_delivery_date)->format('M d, Y') }}</span></p>
                </div>
            </div>
        </div>

        <div class="supplier-section clearfix">
            <div class="supplier-block">
                <h3 class="info-box-title">Supplier</h3>
                <div class="info-box-content">
                    @if($purchase->supplier)
                        <p><strong>{{ $purchase->supplier->name }}</strong></p>
                        <p>{{ $purchase->supplier->phone ?? 'N/A' }}</p>
                        <p>{{ $purchase->supplier->email ?? 'N/A' }}</p>
                        @if($purchase->supplier->address_line_1) <p>{{ $purchase->supplier->address_line_1 }}</p> @endif
                        @if($purchase->supplier->city || $purchase->supplier->state || $purchase->supplier->postal_code)
                        <p>{{ $purchase->supplier->city ?? '' }}{{ $purchase->supplier->city && $purchase->supplier->state ? ', ' : '' }}{{ $purchase->supplier->state ?? '' }} {{ $purchase->supplier->postal_code ?? '' }}</p>
                        @endif
                        @if($purchase->supplier->country) <p>{{ $purchase->supplier->country }}</p> @endif
                    @else
                        <p>No Supplier Selected</p>
                    @endif
                </div>
            </div>

            <div class="shipping-details-block">
                <h3 class="info-box-title">Shipping Details</h3>
                <div class="info-box-content">
                    <p>
                        Ship to: {{ $purchase->shipping_address ?? 'Main Warehouse' }}<br>
                        Carrier: {{ $purchase->carrier ?? 'N/A' }}<br>
                        Tracking #: {{ $purchase->tracking_number ?? 'N/A' }}
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
                        <th class="text-right" style="width: 20%;">Unit Cost</th>
                        <th class="text-right" style="width: 20%;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @php $itemCounter = 1; @endphp
                    @foreach ($items as $item)
                    <tr>
                        <td>{{ $itemCounter++ }}</td>
                        <td>
                            <div class="product-name">{{ $item->product->name ?? 'N/A' }}</div>
                            @if($item->product?->description)
                            <div class="product-desc">{{ Str::limit($item->product->description, 50) }}</div>
                            @endif
                        </td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-right">${{ number_format($item->unit_cost, 2) }}</td>
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
                        <td class="value">${{ number_format($items->sum('total'), 2) }}</td>
                    </tr>
                    @if(isset($purchase->discount) && $purchase->discount > 0)
                    <tr>
                        <td class="label">Discount:</td>
                        <td class="value text-danger">-${{ number_format($purchase->discount, 2) }}</td>
                    </tr>
                    @endif
                    @if(isset($purchase->tax) && $purchase->tax > 0)
                    <tr>
                        <td class="label">Tax:</td>
                        <td class="value">${{ number_format($purchase->tax, 2) }}</td>
                    </tr>
                    @endif
                    @if(isset($purchase->delivery_fee) && $purchase->delivery_fee > 0)
                    <tr>
                        <td class="label">Delivery Fee:</td>
                        <td class="value">${{ number_format($purchase->delivery_fee, 2) }}</td>
                    </tr>
                    @endif
                    <tr class="grand-total">
                        <td class="label">Total:</td>
                        <td class="value">${{ number_format($purchase->total_cost, 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="clearfix"></div>

        @if($purchase->payment_terms)
        <div class="payment-terms" style="margin-top: 20px; padding: 10px; background: #f9fafb; border-radius: 4px;">
            <p style="margin: 0; font-size: 10px;">
                <strong>Payment Terms:</strong> Net {{ $purchase->payment_terms }} Days
                @if($purchase->due_date)
                <br><strong>Due Date:</strong> {{ $purchase->due_date->format('M d, Y') }}
                @endif
            </p>
        </div>
        @endif

        <div class="footer-section">
            <p>This is an official purchase order from {{ $purchase->company->name ?? 'Your Company' }}</p>
            <p>Please notify us immediately of any discrepancies</p>
            <p class="mt-2">
                {{ $purchase->company->name ?? 'Your Company' }}
                @if($purchase->company?->profile?->phone_number) | {{ $purchase->company->profile->phone_number }} @endif
            </p>
        </div>
    </div>
</body>
</html> 