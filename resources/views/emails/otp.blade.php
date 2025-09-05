<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your OTP Code</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            background-color: #f4f4f4;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
        }
        .otp-code {
            font-size: 32px;
            font-weight: bold;
            color: #2c3e50;
            background-color: #ecf0f1;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            letter-spacing: 5px;
            border: 2px dashed #3498db;
        }
        .warning {
            color: #e74c3c;
            font-size: 14px;
            margin-top: 20px;
        }
        .header {
            color: #2c3e50;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="header">🔐 Your OTP Verification Code</h2>
        <p>Use the following One-Time Password (OTP) to complete your verification:</p>
        
        <div class="otp-code">
            {{ $otp }}
        </div>
        
        <p>This code will expire in <strong>5 minutes</strong>.</p>
        <p>If you didn't request this code, please ignore this email.</p>
        
        <div class="warning">
            ⚠️ Do not share this code with anyone for security reasons.
        </div>
    </div>
</body>
</html>