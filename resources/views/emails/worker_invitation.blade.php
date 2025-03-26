<!DOCTYPE html>
<html>
<head>
    <title>Exciting Opportunity Awaits You!</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0;">
    <div style="max-width: 600px; margin: 30px auto; background-color: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <h1 style="color: #333333; text-align: center;">Hello there!</h1>
        <p style="color: #555555; font-size: 16px; text-align: center;">You’ve been handpicked by <strong>{{ $contractorName }}</strong> to join their amazing team!</p>

        @if($dailyPay)
        <p style="color: #555555; font-size: 16px; text-align: center;">We are offering you a competitive daily pay of <strong>RM{{ number_format($dailyPay, 2) }}</strong>. Join us and let’s build something great together!</p>
        @else
        <p style="color: #555555; font-size: 16px; text-align: center;">Come and be part of our growing family — we value your skills and dedication.</p>
        @endif

        <div style="text-align: center; margin: 20px 0;">
            <a href="{{ route('acceptWorkerInvitation', ['token' => $invitation->id]) }}" style="background-color:#28a745;color:white;padding:12px 25px;text-decoration:none;font-weight:bold;border-radius:5px;display:inline-block;">Accept Invitation</a>
        </div>

        <p style="color: #555555; font-size: 14px; text-align: center;">We can’t wait to work with you!</p>
        <p style="color: #555555; font-size: 14px; text-align: center;">Best Regards,<br><strong>{{ $contractorName }}'s Team</strong></p>
    </div>
</body>
</html>
