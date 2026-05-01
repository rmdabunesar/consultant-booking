<?php
/**
 * Invoice Template
 */

defined('ABSPATH') || die('No script kiddies please!');
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <style>
    /* Reset */
    * {
      box-sizing: border-box;
    }

    body {
      font-family: DejaVu Sans, Arial, sans-serif;
      font-size: 14px;
      color: #333;
      background: #f5f7fa;
      margin: 0;
      padding: 30px;
    }

    /* Invoice container */
    .invoice {
      max-width: 700px;
      margin: 0 auto;
      background: #ffffff;
      border-radius: 6px;
      padding: 30px;
      border: 1px solid #e5e7eb;
    }

    /* Header */
    .header {
      border-bottom: 2px solid #e5e7eb;
      padding-bottom: 15px;
      margin-bottom: 25px;
    }

    .header h1 {
      font-size: 24px;
      margin: 0 0 5px 0;
      color: #111827;
    }

    .header div {
      font-size: 13px;
      color: #6b7280;
    }

    /* Meta info */
    .meta {
      margin-bottom: 25px;
    }

    .meta p {
      margin: 4px 0;
      font-size: 14px;
    }

    .meta strong {
      color: #111827;
    }

    /* Table */
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }

    thead th {
      background: #f3f4f6;
      text-align: left;
      padding: 12px;
      font-size: 13px;
      text-transform: uppercase;
      letter-spacing: 0.04em;
      border-bottom: 2px solid #e5e7eb;
    }

    tbody td {
      padding: 12px;
      border-bottom: 1px solid #e5e7eb;
      font-size: 14px;
    }

    .right {
      text-align: right;
    }

    /* Total row */
    .total td {
      font-weight: bold;
      font-size: 15px;
      background: #f9fafb;
      border-top: 2px solid #e5e7eb;
    }

    /* Footer spacing (optional) */
    .footer {
      margin-top: 30px;
      font-size: 12px;
      color: #6b7280;
      text-align: center;
    }
  </style>
</head>
<body>

  <div class="invoice">
    <div class="header">
      <h1>Invoice <?php echo esc_html($booking_number); ?></h1>
      <div>Date: <?php echo esc_html(date('d M Y, h:i A', strtotime($appointment_datetime))); ?></div>
    </div>

    <div class="meta">
      <p><strong>Student:</strong> <?php echo esc_html($student_name); ?></p>
      <p><strong>Consultant:</strong> <?php echo esc_html($consultant_name); ?></p>
    </div>

    <table>
      <thead>
        <tr>
          <th>Description</th>
          <th class="right">Amount</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Consultation Booking</td>
          <td class="right"><?php echo esc_html($consultant_fee); ?></td>
        </tr>
        <tr class="total">
          <td>Total</td>
          <td class="right"><?php echo esc_html($consultant_fee); ?></td>
        </tr>
      </tbody>
    </table>
  </div>

</body>
</html>