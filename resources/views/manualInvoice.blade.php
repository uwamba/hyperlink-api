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
            gap: 20px;
            flex-wrap: wrap;
        }
        .column {
            flex: 1;
            min-width: 300px;
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
        .text-bold {
            font-weight: bold;
        }
        .text-center {
            text-align: center;
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

        {{-- From and Bill To side by side --}}
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

        {{-- Invoice Items --}}
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Item Description</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>{{ $plan->name ?? 'Subscription Plan' }}</td>
                    <td>1</td>
                    <td>{{ number_format($invoice->amount, 2) }}</td>
                    <td>{{ number_format($invoice->amount, 2) }}</td>
                </tr>
            </tbody>
        </table>

        {{-- Total Amount --}}
        <div class="text-right mb-4">
            <p><strong>Total Amount:</strong> {{ number_format($amount, 2) }}</p>
        </div>

        {{-- Bank Account Info --}}
        <div class="mb-4">
            <strong>Bank Details:</strong><br>
            Bank Account Name: {{ $BANK_ACCOUNT_NAME }}<br>
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
