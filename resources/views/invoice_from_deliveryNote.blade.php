<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $invoice->invoice_no }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            color: #333;
        }
        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
            line-height: 1.6;
        }
        .flex {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .section-box {
            width: 48%;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        .table th {
            background-color: #f3f3f3;
            text-align: left;
        }
        .mb-4 {
            margin-bottom: 1.5rem;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .text-bold {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="invoice-box">
        {{-- Header --}}
        <div class="flex mb-4">
            <div>
                <img src="{{ public_path('images/logo.png') }}" alt="Company Logo" height="80">
            </div>
            <div class="text-right">
                <h4>INVOICE</h4>
                <p>Invoice #: <span class="text-bold">{{ $invoice->invoice_no }}</span></p>
                <p>Issue Date: {{ $issue_date }}</p>
                <p>Due Date: {{ $due_date }}</p>
            </div>
        </div>

        {{-- From and Bill To --}}
        <div class="flex mb-4">
            <div class="column">
                <strong>From:</strong><br>
                {{ $COMPANY_NAME }}<br>
                TIN: {{ $COMPANY_TIN }}
            </div>
             <div class="column"></div>
            <div class="text-right">
              
                <strong>Bill To:</strong><br>
                {{ $client->name }}<br>
                {{ $client->email ?? '' }}<br>
                {{ $client->address ?? '' }}
              </div>
            </div>
            
        </div>
       

        {{-- Items Table --}}
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Description</th>
                    <th>Qty</th>
                    <th>Unit</th>
                    <th>Unit Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @php $grandTotal = 0; @endphp
                @foreach ($items as $index => $item)
                    @php
                        $total = $item->quantity * $item->unit_price;
                        $grandTotal += $total;
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->description ?? 'Item' }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ $item->unit }}</td>
                        <td>{{ number_format($item->unit_price, 2) }}</td>
                        <td>{{ number_format($total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Total Amount --}}
        <div class="text-right mb-4">
            <p><strong>Total Amount:</strong> {{ number_format($grandTotal, 2) }}</p>
        </div>

        {{-- Bank Info --}}
        <div class="mb-4">
            <strong>Bank Details:</strong><br>
            Account Name: {{ $BANK_ACCOUNT_NAME }}<br>
            Bank of Kigali (RWF): {{ $BANK_ACCOUNT_RWF }}<br>
            Bank of Kigali (USD): {{ $BANK_ACCOUNT_USD }}
        </div>

        {{-- Footer --}}
        <div class="text-center" style="margin-top: 40px; font-size: 12px;">
            Thank you for doing business with us.
        </div>
    </div>
</body>
</html>
