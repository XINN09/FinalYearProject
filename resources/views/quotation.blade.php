<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Quotation</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                font-size: 14px;
            }
            .container {
                max-width: 800px;
                padding: 20px;
            }
            .blue-padding {
                width: 100%;
                height: 20px;
                background-color: #326baf;
                margin-bottom: 10px;
            }
            .content {
                width: 100%;
                text-align: center;
                margin: 0 30px;
            }
            .content img {
                width: 120px;
            }
            .header-text {
                display: block;
                text-align: center;
            }
            #quotation-title {
                font-weight: bold;
                margin: 10px 30px 20px 30px;
                text-align: center;
                padding-bottom: 5px;
                border-bottom: 1px solid #6e6e6e;
                font-size: 22px;
            }
            .header-text h2 {
                margin: 0;
                font-size: 18px;
                padding-bottom: 8px;
            }
            .header-text small {
                font-size: 12px;
                margin-top: 2px;
                color: black;
            }
            .header-text p {
                margin: 3px 0;
            }
            .info-section {
                width: 100%;
                margin: 0 30px 20px 30px;
                display: table;
            }
            .info-section div {
                display: table-cell;
                width: 50%;
                vertical-align: top;
            }
            .info-section strong, .project-section strong {
                font-size: 16px;
            }
            .info-section p, .project-section p {
                font-size: 13px;
                margin: 10px 0px;
                width: 60%;
            }
            .project-section {
                border-top: 1px solid #6e6e6e;
                margin: 0 30px;
            }
            .quotation {
                width: 94%;
                border-collapse: collapse;
                margin: 20px 30px 20px 30px;
            }
            .quotation th, .quotation td {
                border: 1px solid #c0bdbd;
                padding: 8px;
                text-align: left;
            }
            .quotation th {
                background-color: #326baf;
                color: white;
            }
            .quotation tbody tr:nth-child(odd) {
                background-color: #ebebeb;
            }
            .quotation tbody tr:nth-child(even) {
                background-color: white;
            }
            .total-section {
                text-align: right;
                font-size: 16px;
                font-weight: bold;
                margin-top: 20px;
            }
            .remarks {
                margin: 10px 30px;
                font-size: 12px;
                width: 55%;
            }
            .remarks h4 {
                margin: 5px;
            }
            .remarks p {
                margin: 5px;
            }
            .signature {
                width: 92%;
                text-align: justify;
                margin: 50px 30px;
            }
            .signature::after {
                content: "";
                display: inline-block;
                width: 100%;
            }
            .signature div {
                display: inline-block;
                width: 30%;
                text-align: center;
                border-top: 1px solid black;
                padding-top: 5px;
                font-weight: bold;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="blue-padding"></div>
            <div class="main-container">
                <!-- Header -->
                <table class="content">
                    <tr>
                        <td style="width: 20%; text-align: left;">
                            <img src="{{ $businessInfo['companyLogo'] }}" alt="Business Logo" style="width: 120px;">
                        </td>
                        <td style="width: 80%; text-align: center; vertical-align: middle;" class="header-text">
                            <h2>{{ $businessInfo['businessName'] }} <small style="font-size: 11px;">({{ $businessInfo['businessNo'] }})</small></h2>
                            <p>{{ $businessInfo['businessAddress'] }}</p>
                            <p>Tel: {{ $businessInfo['contractorPhone'] }}</p>
                            <p>Email: {{ $businessInfo['contractorEmail'] }}</p>
                        </td>
                    </tr>
                </table>

                <h1 id="quotation-title">QUOTATION</h1>

                <div class="info-section">
                    <div class="client-info">
                        <p><strong>{{ $clientInfo['clientName'] }}</strong></p>
                        <p>{{ $clientInfo['clientAddress'] }}</p>
                        <p>Tel: {{ $clientInfo['clientPhone'] }}</p>
                        <p>Email: {{ $clientInfo['clientEmail'] }}</p>
                    </div>
                    <div>
                        <p><strong>QUOTE No: {{ $quotationInfo['quotationNumber'] }}</strong></p>
                        <p>Date of Issues: {{ $quotationInfo['quotationDate'] }}</p>
                        <p>Terms: {{ $quotationInfo['paymentTerm'] }}</p>
                        <p>Page: 1 of 1</p>
                    </div>
                </div>

                <div class="project-section">
                    <div style="width: 60%;">
                        <p><strong>{{ $projectInfo['projectName'] ?? 'No Project Name' }}</strong></p>
                        <p style="margin: 18px 0;">{{ $projectInfo['projectAddress'] ?? 'No Address Provided' }}</p>
                        <p>Attn: {{ $projectInfo['contactName'] }}</p>
                        <p>Tel: {{ $projectInfo['contactPhone'] }}</p>
                    </div>
                </div>

                <table class="quotation">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Task Name</th>
                            <th>QTY</th>
                            <th>UOM</th>
                            <th>Rate</th>
                            <th style="width: 15%;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tasks as $index => $task)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $task['taskName'] }}</td>
                            <td>{{ $task['qty'] }}</td>
                            <td>{{ $task['uom'] ?? 'NO' }}</td>
                            <td>{{ number_format($task['unitPrice'], 2) }}</td>
                            <td style="border-bottom: 1px solid #c0bdbd;">{{ number_format($task['budget'], 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            @php    
                            $depositPercentage = $paymentDetails['depositRate'] ?? 0;
                            @endphp
                            @if ($depositPercentage != 0)
                            <td colspan="2" style="text-align: left; font-style: italic; border: none;">
                                * A deposit of {{ $depositPercentage }}% is required upon acceptance of this quotation
                            </td>
                            @else
                            <td colspan="2" style="border: none;"></td>
                            @endif
                            <td colspan="3" style="text-align: right; font-weight: bold; border: none; color: #6f6f6f;">
                                Subtotal:
                            </td>
                            <td style="border: none; border-bottom: 1px gray solid; text-align: right;">
                                {{ number_format($paymentDetails['subtotal'], 2) }}
                            </td>
                        </tr>
                        <tr>
                            <td colspan="5" style="text-align: right; font-weight: bold; border: none; color: #6f6f6f;">
                                Tax Rate:
                            </td>
                            <td style="border: none; border-bottom: 1px gray solid; text-align: right;">
                                {{ number_format($paymentDetails['tax'], 2) }}%
                            </td>
                        </tr>
                        @if ($paymentDetails['depositRate'] > 0)
                        <tr>
                            <td colspan="5" style="text-align: right; font-weight: bold; border: none; color: #6f6f6f;">
                                Deposit ({{ $paymentDetails['depositRate'] }}%):
                            </td>
                            <td style="border: none; border-bottom: 1px gray solid; text-align: right;">
                                {{ number_format($paymentDetails['depositAmount'], 2) }}
                            </td>
                        </tr>
                        @endif

                        @php
                        $hasPreviousPayments = isset($paymentDetails['previousPayments']) && count($paymentDetails['previousPayments']) > 0;
                        @endphp


                        <tr>
                            <td colspan="5" style="text-align: right; font-weight: bold; border: none; {{ $hasPreviousPayments ? 'color: #6f6f6f;' : 'border-bottom: 1px solid black; font-size: 17px;' }}">
                                Total (RM):
                            </td>
                            <td style="font-weight: {{ $hasPreviousPayments ? 'normal' : 'bold' }};
                                font-size: {{ $hasPreviousPayments ? 'inherit' : '18px' }};
                                border: none;
                                {{ $hasPreviousPayments ? 'border-bottom: 1px gray solid;' : 'border-bottom: 1px gray solid; background-color: #c2ddf8;' }}
                                text-align: right;">
                                {{ number_format($paymentDetails['amountDue'] - $paymentDetails['depositAmount'], 2) }}
                            </td>
                        </tr>


                        @if (isset($paymentDetails['previousPayments']) && count($paymentDetails['previousPayments']) > 0)
                        @foreach ($paymentDetails['previousPayments'] as $payment)
                        <tr>
                            <td colspan="5" style="text-align: right; font-weight: bold; border: none; color: #6f6f6f;">
                                {{ $payment['paymentType'] ?? 'N/A' }} - {{ $payment['paymentID'] ?? 'N/A' }}:
                            </td>
                            <td style="border: none; border-bottom: 1px gray solid; text-align: right;">
                                - RM{{ number_format($payment['paymentAmount'] ?? 0, 2) }}
                            </td>
                        </tr>
                        @endforeach

                        <tr>
                            <td colspan="5" style="text-align: right; font-weight: bold; border: none; border-bottom: 1px solid black; font-size: 17px;">
                                Balance (RM):
                            </td>
                            <td style="font-weight: bold; font-size: 18px; border: none; border-bottom: 1px gray solid; text-align: right; background-color: #c2ddf8;">
                                {{ number_format($paymentDetails['balance'], 2) }}
                            </td>
                        </tr>
                        @endif
                    </tfoot>
                </table>

                <div class="remarks">
                    @php
                    $paymentTerms = $quotationInfo['paymentTerm'] ?? 0;
                    $depositPercentage = $paymentDetails['depositRate'] ?? 0;
                    $remarks = $remarks ?? '';
                    $paymentInstruction = $paymentDetails['paymentInstruction'] ?? '';
                    @endphp

                    <p>1. This quotation is valid for {{ $paymentTerms }} days from the date of issue.</p>

                    @php
                    $remarkNumber = 2; // Start numbering from 2
                    @endphp

                    @if (!empty($paymentInstruction))
                    <p>2. {{ $paymentInstruction }}</p>
                    @php $remarkNumber = 3; @endphp
                    @endif

                    @if (!empty($remarks))
                    <p>{{ $remarkNumber }}. {{ $remarks }}</p>
                    @endif
                </div>

                <div class="signature">
                    <div>Authorized Signature</div>
                    <div>Customer Signature</div>
                </div>
            </div>
            <div class="blue-padding"></div>
        </div>
    </body>
</html>