@extends('layouts.app')

@section('title', 'Print Purchase Invoice')

@section('content')
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchase Invoice - {{ $purchase->invoice_no }}</title>
    <style>
        @media print {
            body { -webkit-print-color-adjust: exact; }
            .no-print { display: none !important; }
            .page-break { page-break-after: always; }
        }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            font-size: 12px;
            color: #333;
        }
        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 20px;
            border: 1px solid #eee;
            background: #fff;
        }
        .invoice-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 2px solid #4f46e5;
        }
        .company-info h1 {
            margin: 0;
            color: #4f46e5;
            font-size: 24px;
        }
        .invoice-info {
            text-align: right;
        }
        .invoice-info h2 {
            margin: 0 0 10px 0;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table th, table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        table th {
            background: #f3f4f6;
            font-weight: 600;
        }
        .totals {
            width: 300px;
            margin-left: auto;
        }
        .totals table {
            margin: 0;
        }
        .totals table td {
            padding: 8px 10px;
        }
        .totals .total-row {
            background: #4f46e5;
            color: white;
            font-weight: bold;
        }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
        }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .section-title {
            background: #f3f4f6;
            padding: 8px 12px;
            font-weight: 600;
            margin: 20px 0 10px 0;
            border-left: 3px solid #4f46e5;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
    </style>
</head>
<body onload="window.print()">
    <div class="invoice-box">
        <div class="invoice-header">
            <div class="company-info">
                <h1>POS System</h1>
                <p>Purchase Invoice</p>
            </div>
            <div class="invoice-info">
                <h2>{{ $purchase->invoice_no }}</h2>
                <p><strong>Date:</strong> {{ $purchase->created_at->format('M d, Y') }}</p>
                <p><strong>Status:</strong> 
                    <span class="badge badge-{{ $purchase->status === 'received' ? 'success' : ($purchase->status === 'pending' ? 'warning' : 'danger') }}">
                        {{ ucfirst($purchase->status) }}
                    </span>
                </p>
            </div>
        </div>

        <div style="display: flex; justify-content: space-between;">
            <div>
                <strong>Supplier:</strong>
                <p>{{ $purchase->supplier?->name ?? 'N/A' }}</p>
                <p>{{ $purchase->supplier?->phone ?? '' }}</p>
                <p>{{ $purchase->supplier?->address ?? '' }}</p>
            </div>
            <div style="text-align: right;">
                <strong>Created By:</strong>
                <p>{{ $purchase->creator?->name ?? 'N/A' }}</p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>SKU</th>
                    <th class="text-right">Quantity</th>
                    <th class="text-right">Unit Price</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchase->lines as $index => $line)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $line->product?->name ?? 'N/A' }}</td>
                    <td>{{ $line->product?->sku ?? '-' }}</td>
                    <td class="text-right">{{ number_format($line->quantity, 2) }}</td>
                    <td class="text-right">{{ number_format($line->purchase_price, 2) }}</td>
                    <td class="text-right">{{ number_format($line->line_total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <table>
                <tr>
                    <td>Subtotal:</td>
                    <td class="text-right">{{ number_format($purchase->lines->sum('line_total'), 2) }}</td>
                </tr>
                @if($purchase->discount_amount > 0)
                <tr>
                    <td>Discount ({{ $purchase->discount_type }}):</td>
                    <td class="text-right">-{{ number_format($purchase->discount_amount, 2) }}</td>
                </tr>
                @endif
                @if($purchase->tax_amount > 0)
                <tr>
                    <td>Tax:</td>
                    <td class="text-right">{{ number_format($purchase->tax_amount, 2) }}</td>
                </tr>
                @endif
                @if($purchase->shipping_charges > 0)
                <tr>
                    <td>Shipping:</td>
                    <td class="text-right">{{ number_format($purchase->shipping_charges, 2) }}</td>
                </tr>
                @endif
                @if($purchase->other_charges > 0)
                <tr>
                    <td>Other Charges:</td>
                    <td class="text-right">{{ number_format($purchase->other_charges, 2) }}</td>
                </tr>
                @endif
                <tr class="total-row">
                    <td>Grand Total:</td>
                    <td class="text-right">{{ number_format($purchase->total_amount, 2) }}</td>
                </tr>
                <tr>
                    <td>Paid:</td>
                    <td class="text-right" style="color: #059669;">{{ number_format($purchase->paid_amount, 2) }}</td>
                </tr>
                <tr>
                    <td>Due:</td>
                    <td class="text-right" style="color: #dc2626; font-weight: bold;">{{ number_format($purchase->due_amount, 2) }}</td>
                </tr>
            </table>
        </div>

        @if($purchase->returns->count() > 0)
        <div class="section-title">Return History</div>
        <table>
            <thead>
                <tr>
                    <th>Return Date</th>
                    <th>Items</th>
                    <th class="text-right">Amount</th>
                    <th>Note</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchase->returns as $return)
                <tr>
                    <td>{{ $return->return_date->format('M d, Y') }}</td>
                    <td>{{ $return->items->count() }} items</td>
                    <td class="text-right">{{ number_format($return->total_return_amount, 2) }}</td>
                    <td>{{ $return->note ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        @if($purchase->payments->count() > 0)
        <div class="section-title">Payment History</div>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Method</th>
                    <th class="text-right">Amount</th>
                    <th>Note</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchase->payments as $payment)
                <tr>
                    <td>{{ $payment->payment_date->format('M d, Y') }}</td>
                    <td>{{ ucfirst($payment->payment_method) }}</td>
                    <td class="text-right">{{ number_format($payment->amount, 2) }}</td>
                    <td>{{ $payment->note ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        @if($purchase->notes)
        <div class="section-title">Notes</div>
        <p>{{ $purchase->notes }}</p>
        @endif

        <div style="margin-top: 40px; text-align: center; color: #666; font-size: 11px;">
            <p>Thank you for your business!</p>
            <p>Printed on: {{ now()->format('Y-m-d H:i:s') }}</p>
        </div>
    </div>

    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #4f46e5; color: white; border: none; border-radius: 5px; cursor: pointer;">Print</button>
        <a href="{{ route('purchases.index') }}" style="padding: 10px 20px; background: #666; color: white; border: none; border-radius: 5px; text-decoration: none; margin-left: 10px;">Back</a>
    </div>
</body>
</html>
@endsection
