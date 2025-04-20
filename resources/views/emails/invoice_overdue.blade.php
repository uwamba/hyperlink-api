<!DOCTYPE html>
<html>
<head>
    <title>Invoice Overdue Notice</title>
</head>
<body>
    <p>Dear {{ $invoice->client->name }},</p>

    <p>This is a reminder that your invoice <strong>#{{ $invoice->invoice_no }}</strong> is overdue.</p>

    <ul>
        <li><strong>Amount:</strong> ${{ number_format($invoice->amount, 2) }}</li>
        <li><strong>Due Date:</strong> {{ \Carbon\Carbon::parse($invoice->due_date)->toFormattedDateString() }}</li>
        <li><strong>Status:</strong> {{ ucfirst($invoice->status) }}</li>
    </ul>

    <p>Please make your payment as soon as possible.</p>

    <p>Thank you,<br/>The Billing Team</p>
</body>
</html>
