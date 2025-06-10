<p>Hello {{ $ticket->client->name ?? 'User' }},</p>
<p>We have reviewed your support ticket (Issue: <strong>{{ $ticket->issue }}</strong>).</p>
<p>Here is our feedback:</p>
<p><em>{{ $feedback }}</em></p>
<p>Thank you,<br/>Support Team</p>
