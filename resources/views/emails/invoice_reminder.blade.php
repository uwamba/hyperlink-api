<!DOCTYPE html>
<html>
<head>
    <title>Invoice Reminder</title>
</head>
<body>
    <p>Dear {{ $invoice->client->name }},</p>

    <p>This is a friendly reminder that invoice <strong>#{{ $invoice->invoice_no }}</strong> is due in a few days.</p>

    <ul>
        <li><strong>Amount:</strong> ${{ number_format($invoice->amount, 2) }}</li>
        <li><strong>Due Date:</strong> {{ \Carbon\Carbon::parse($invoice->due_date)->toFormattedDateString() }}</li>
    </ul>

    <p>Please ensure that payment is made on time to avoid any late fees.</p>

    <p>Best regards,<br/>Your Billing Team</p>
</body>
</html>
