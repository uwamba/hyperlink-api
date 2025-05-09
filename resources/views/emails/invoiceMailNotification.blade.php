<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Notification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #333;
        }
        .invoice-details {
            margin: 20px 0;
        }
        .invoice-details td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        .invoice-details th {
            padding: 8px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
        }
        .footer {
            margin-top: 30px;
            font-size: 14px;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Hello, {{ $invoice->client->name }}!</h2>
        <p>This is to inform you that your invoice has been generated.</p>
        
        <p>Below are the details of your invoice:</p>

        <table class="invoice-details" style="width: 100%; border-collapse: collapse;">
            <tr>
                <th>Invoice Number</th>
                <td>{{ $invoice->invoice_no }}</td>
            </tr>
            <tr>
                <th>Amount</th>
                <td>${{ number_format($invoice->amount, 2) }}</td>
            </tr>
            <tr>
                <th>Due Date</th>
                <td>{{ \Carbon\Carbon::parse($invoice->due_date)->format('F j, Y') }}</td>
            </tr>
            <tr>
                <th>Status</th>
                <td>{{ ucfirst($invoice->status) }}</td>
            </tr>
        </table>

        <p>You can download your invoice as a PDF by clicking the link below:</p>
        <p><a href="{{ url('path/to/your/invoice/pdf') }}">Download Invoice PDF</a></p>

        <p>If you have any questions or concerns, feel free to contact us.</p>

        <div class="footer">
            <p>Thank you,<br>The {{ config('app.name') }} Team</p>
        </div>
    </div>
</body>
</html>
