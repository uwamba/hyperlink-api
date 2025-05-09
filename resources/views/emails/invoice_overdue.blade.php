<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Overdue Invoice Reminder</title>
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
        ul {
            list-style-type: none;
            padding: 0;
        }
        ul li {
            padding: 5px 0;
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
        <h2>Dear {{ $invoice->client->name }},</h2>

        <p>This is a friendly reminder that your invoice <strong>#{{ $invoice->invoice_no }}</strong> is overdue.</p>

        <ul>
            <li><strong>Amount:</strong> ${{ number_format($invoice->amount, 2) }}</li>
            <li><strong>Due Date:</strong> {{ \Carbon\Carbon::parse($invoice->due_date)->toFormattedDateString() }}</li>
        </ul>

        <p>Please ensure that payment is made immediately to avoid any late fees or service interruptions.</p>

        <p>If you have already made the payment, kindly disregard this notice. If you need assistance or have any questions, please feel free to contact us.</p>

        <div class="footer">
            <p>Best regards,<br/>The {{ config('app.name') }} Team</p>
        </div>
    </div>
</body>
</html>
