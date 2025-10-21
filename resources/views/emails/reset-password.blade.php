<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>Your Account Credentials</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      padding: 20px;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f5f5f5;
      color: #333;
      line-height: 1.6;
    }

    .container {
      max-width: 600px;
      margin: 0 auto;
      background: #fff;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
      border: 1px solid #e8e8e8;
    }

    .header {
      background-color: #073c82;
      color: #ffffff;
      padding: 30px 20px;
      text-align: center;
    }

    .header-icon {
      width: 50px;
      height: 50px;
      background-color: rgba(255, 255, 255, 0.1);
      border-radius: 50%;
      margin: 0 auto 15px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      border: 1px solid #ffffff;
    }

    .header-title {
      font-size: 24px;
      font-weight: 600;
      margin: 0;
    }

    .header-subtitle {
      font-size: 14px;
      opacity: 0.9;
      margin-top: 5px;
    }

    .content {
      padding: 40px 30px;
    }

    .greeting {
      font-size: 18px;
      font-weight: 600;
      color: #073c82;
      margin-bottom: 20px;
    }

    .message {
      font-size: 16px;
      color: #555;
      margin-bottom: 30px;
    }

    .credentials-card {
      background-color: #fafafa;
      border: 1px solid #e0e0e0;
      border-radius: 6px;
      padding: 20px;
      margin: 25px 0;
    }

    .credentials-title {
      font-size: 18px;
      font-weight: 600;
      color: #073c82;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .credentials-title::before {
      content: '🔐';
      font-size: 20px;
    }

    .button-wrapper {
      text-align: left;
      margin: 20px 0;
    }

    .reset-button {
      display: inline-block;
      background-color: #073c82;
      color: #ffffff !important;
      text-decoration: none;
      padding: 12px 30px;
      border-radius: 5px;
      font-weight: 600;
      font-size: 16px;
    }

    .reset-button:hover {
      background-color: #052a5c;
    }

    .link-note {
      text-align: left;
      color: #666;
      font-size: 13px;
      margin-top: 15px;
      font-style: italic;
    }

    .credential-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 10px 0;
      border-bottom: 1px solid #e8e8e8;
    }

    .credential-item:last-child {
      border-bottom: none;
    }

    .credential-label {
      font-weight: 600;
      color: #2c3e50;
      min-width: 100px;
    }

    .credential-value {
      color: #073c82;
      font-family: 'Courier New', monospace;
      background-color: #f5f5f5;
      padding: 5px 10px;
      border-radius: 4px;
      font-size: 14px;
      border: 1px solid #ddd;
    }

    .warning-box {
      background-color: #fff8e1;
      border: 1px solid #ffcc02;
      border-radius: 4px;
      padding: 15px;
      margin: 20px 0;
      display: flex;
      align-items: flex-start;
      gap: 10px;
    }

    .warning-text {
      color: #856404;
      font-size: 15px;
      font-weight: 500;
    }

    .footer {
      background: #f8f9fa;
      color: #6c757d;
      text-align: center;
      padding: 30px 20px;
      font-size: 14px;
      border-top: 1px solid #e9ecef;
    }

    .footer-link {
      color: #073c82;
      text-decoration: none;
      margin: 0 10px;
      font-weight: 500;
    }

    .footer-link:hover {
      text-decoration: underline;
    }

    @media (max-width: 600px) {
      .content {
        padding: 20px 15px;
      }

      .header-title {
        font-size: 20px;
      }

      .greeting {
        font-size: 16px;
      }

      .credential-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
      }

      .credential-value {
        width: 100%;
        text-align: left;
        font-size: 13px;
      }

      .footer {
        font-size: 12px;
      }

      .reset-button {
        padding: 10px 25px;
        font-size: 14px;
      }
    }
  </style>
</head>

<body>
  <div class="container">
    <div class="header">
      <img src="{{ $message->embed('icons/logo.svg') }}" alt="PCNL Logo" style="width: 80px; margin-bottom: 10px;">
      <h1 class="header-title" style="margin-top: 5px;">PCNL Student Affairs</h1>
      <p class="header-subtitle">Your account is ready to go</p>
    </div>

    <div class="content">
      <div class="greeting">Hi {{ $name }},</div>
      <div class="message">
        We received a request to reset your password. Click the button below to create a new password for your account.
      </div>
      <div class="credentials-card">
        <div class="credentials-title">Reset Your Password</div>
        <div class="button-wrapper">
          <a href="{{ $url }}" class="reset-button">Reset Password</a>
        </div>
        <div class="link-note">
          This password reset link will expire in 60 minutes.
        </div>
      </div>
      <div class="warning-box">
        <div class="warning-text">
          <strong>Security Notice:</strong> If you did not request a password reset, please ignore this email or contact
          support if you have concerns.
        </div>
      </div>
      <p style="margin-top: 30px; color: #666;">
        If you have any questions or need assistance, don't hesitate to reach out to our support team.
      </p>
      <p style="color: #666;">
        Best regards,<br>
        <strong>The Team</strong>
      </p>
    </div>
    <div class="footer">
      <p>&copy; {{ date('Y') }} PCNL Student Affairs. All rights reserved.</p>
    </div>
  </div>
</body>

</html>