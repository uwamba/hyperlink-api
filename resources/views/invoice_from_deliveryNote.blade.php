<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $client->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        .invoice-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .invoice-header h1 {
            font-size: 30px;
            margin-bottom: 10px;
        }
        .invoice-details {
            margin-bottom: 20px;
        }
        .invoice-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .invoice-details table, th, td {
            border: 1px solid #ccc;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        .total {
            font-size: 20px;
            margin-top: 20px;
            text-align: right;
        }
    </style>
</head>
<body>

<div class="invoice-header">
    <h1>Invoice</h1>
    <p>Client: {{ $client->name }}</p>
    <p>Items: </p>
    <p>Issue Date: {{ $issue_date }}</p>
    <p>Due Date: {{ $due_date }}</p>
</div>

<div class="invoice-details">
    <table>
        <tr>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Amount</th>
        </tr>
        <tr>
           
            <td>${{ number_format($amount, 2) }}</td>
        </tr>
    </table>
</div>

<div class="total">
    <strong>Total Amount: ${{ number_format($amount, 2) }}</strong>
</div>

</body>
</html>
