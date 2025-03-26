<!DOCTYPE html>
<html>
<head>
    <title>New Receipt Uploaded</title>
</head>
<body>
    <p>Hello,</p>
    <p>A new receipt has been uploaded for invoice/quotation <strong>{{ $data['invoiceNo'] }}</strong> on project <strong>{{ $data['projectName'] }}</strong>.</p>
    <p>Please click the button below to view the receipt:</p>
    <a href="{{ $data['checkUrl'] }}" style="background-color: #4CAF50; color: white; padding: 10px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; margin: 10px 0; cursor: pointer;">Check Receipt</a>
    <p>Thank you,</p>
    <p>Alloymont</p>
</body>
</html>