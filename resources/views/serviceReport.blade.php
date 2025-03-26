<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Customer Service</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                font-size: 14px;
            }
            .service-report-container {
                display: inline-block;
                width: 100%;
                height: auto;
            }
            .container {
                max-width: 800px;
                padding: 20px;
                margin: 0 auto;
            }
            /* Green Padding Line */
            .green-padding {
                width: 100%;
                height: 20px;
                background-color: #067e0a;
                margin-bottom: 10px;
            }
            hr {
                margin: 0 20px;
            }
            /* Header Layout */
            .view-content {
                display: block;
                width: 100%;
                margin: 0 30px;
            }
            .view-content img {
                width: 30%;
                max-width: 120px;
                float: left; /* Float the image to the left */
            }
            .header-text {
                display: block;
                text-align: center;

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
            #invoice-title {
                font-weight: bold;
                margin: 10px 30px 20px 30px;
                text-align: center;
                padding-bottom: 5px;
                border-bottom: 1px solid #6e6e6e;
                font-size: 22px;
            }
            .info-section {

                margin: 0 30px 20px 30px;

                border-bottom: 1px solid #6e6e6e;
            }
            .info-section strong, .project-section strong {
                font-size: 16px;
            }
            .info-section p, .project-section p {
                font-size: 13px;
                margin: 10px 0;
            }
            .info-section div {
                width: 48%;
                display: inline-block;
                vertical-align: top;
            }
            .issue-section {
                margin: 0 20px 20px 20px;
            }
            .quotation {
                width: 90%;
                border-collapse: collapse;
                margin: 20px 30px 20px 30px;
            }
            .quotation tr {
                height: 30px;
            }
            .quotation th, .quotation td {
                border: 1px solid #c0bdbd;
                padding: 8px;
                text-align: center;
            }
            .quotation th {
                background-color: #067e0a;
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
                margin: 10px 20px;
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
                margin: 50px 20px;
                text-align: right;
            }
            .signature div {
                display: inline-block;
                text-align: center;
                width: 30%;
                border-top: 1px solid black;
                padding-top: 5px;
                font-weight: bold;
                margin: 0 20px;
            }

            @media print {
                .info-section div {
                    display: inline-block;
                }
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="green-padding"></div>
            <div class="service-report-container">
                <!-- Header -->
                <div class="view-content">
                    <div class="header-text">
                        <h2>{{ $reportData['companyName'] }} <small id="business-id">{{ $reportData['businessId'] }}</small></h2>
                        <p id="business-address">{{ $reportData['businessAddress'] }}</p>
                        <p>Tel: <span id="business-tel">{{ $reportData['businessTel'] }}</span></p>
                        <p>Email: <span id="business-email">{{ $reportData['businessEmail'] }}</span></p>
                    </div>
                </div>
                <h1 id="invoice-title">CUSTOMER SERVICE REPORT</h1>

                <div class="info-section">
                    <div class="client-info">
                        <p><strong id="client-name">{{ $reportData['clientName'] }}</strong></p>
                        <div class="project-section">
                            <div style="width: 100%;">
                                <p><strong style="font-size: 15px;">{{ $reportData['projectName'] }}</strong></p>
                                <p style="margin-bottom: 18px; width: 120%;" id="project-address">{{ $reportData['projectAddress'] }}</p>
                                <p>Tel: <span id="attn-tel">{{ $reportData['clientTel'] }}</span></p>
                                <p>Email: <span id="attn-name">{{ $reportData['clientEmail'] }}</span></p>
                            </div>
                        </div>
                    </div>
                    <div>
                        <p><strong id="service-no">SERVICE NO: {{ $reportData['serviceNo'] }}</strong></p>
                        <p>Warranty Expiry Date: <span id="warranty-expiry">{{ $reportData['warrantyExpiry'] }}</span></p>
                        <p>Date of Report: <span id="report-date">{{ $reportData['reportDate'] }}</span></p>
                        <p style="padding-bottom: 5px;">Page: 1 of 1</p>
                        <p>Person in Charge: <span id="person-in-charge">{{ $reportData['personInCharge'] }}</span></p>
                        <p>Tel: <span id="person-tel">{{ $reportData['personTel'] }}</span></p>
                    </div>
                </div>

                <div class="issue-section">
                    <div style="width: 100%;">
                        <p><strong style="font-size: 15px; margin: 0 10px; display: none;">Issue Name: </strong><span id="issue-name" style="display: none;">{{ $reportData['issueName'] }}</span></p>
                    </div>
                </div>
                <table class="quotation">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Task Name</th>
                            <th>QTY</th>
                            <th>Unit Price</th>
                            <th>Total (RM)</th>
                        </tr>
                    </thead>
                    <tbody id="quotation-body">
                        @foreach ($reportData['quotationRows'] as $index => $row)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $row['description'] }}</td>
                            <td>{{ $row['quantity'] }}</td>
                            <td>{{ number_format($row['unitPrice'], 2) }}</td>
                            <td>{{ number_format($row['total'], 2) }}</td>
                        </tr>
                        @endforeach

                        <!-- 3 Empty Rows (Always) -->
                        @for ($i = 0; $i < 3; $i++)
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                        @endfor
                    </tbody>

                    <tfoot>
                        <tr>
                            <td colspan="4" style="text-align: right; font-weight: bold; border-bottom: 1px solid black; border: none; border-bottom: 1px gray solid; font-size: 17px;">
                                Total (RM):
                            </td>
                            <td style="font-weight: bold; font-size: 18px; border: none; border-bottom: 1px gray solid;  text-align: right; background-color: #d7ffde;" id="total-amount">
                                {{ number_format($reportData['totalAmount'], 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
                <div class="remarks">
                    <h4>Remarks / Payment Instructions:</h4>
                    @if ($reportData['totalAmount'] == 0)
                    <p>1. <strong>No charge applicable</strong> as the service is fully covered under the warranty time.</p>
                    @else
                    <p>1. RM{{ number_format($reportData['totalAmount'], 2) }} is charge for <strong>{{ $reportData['issueName'] }}</strong>.</p>
                    <p>2. Payment required, please contact the person in charge for payment.</p>
                    @endif
                </div>
                <div class="signature">
                    <div>Customer Signature</div>
                </div>
            </div>
            <div class="green-padding"></div>
        </div>
    </body>
</html>
