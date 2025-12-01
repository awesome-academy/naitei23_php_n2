<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mật khẩu mới</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #4F46E5;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #f9fafb;
            padding: 30px;
            border: 1px solid #e5e7eb;
            border-top: none;
            border-radius: 0 0 8px 8px;
        }
        .password-box {
            background-color: #fff;
            border: 2px dashed #4F46E5;
            padding: 15px;
            text-align: center;
            margin: 20px 0;
            border-radius: 8px;
        }
        .password {
            font-size: 24px;
            font-weight: bold;
            color: #4F46E5;
            letter-spacing: 2px;
        }
        .warning {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 12px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            color: #6b7280;
            font-size: 12px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔐 Workspace Booking</h1>
    </div>
    
    <div class="content">
        <p>Xin chào <strong>{{ $userName }}</strong>,</p>
        
        <p>Chúng tôi đã nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn.</p>
        
        <p>Mật khẩu mới của bạn là:</p>
        
        <div class="password-box">
            <span class="password">{{ $newPassword }}</span>
        </div>
        
        <div class="warning">
            <strong>⚠️ Lưu ý quan trọng:</strong>
            <ul style="margin: 10px 0 0 0; padding-left: 20px;">
                <li>Vui lòng đăng nhập và đổi mật khẩu ngay sau khi nhận được email này.</li>
                <li>Không chia sẻ mật khẩu này với bất kỳ ai.</li>
                <li>Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng liên hệ với chúng tôi ngay.</li>
            </ul>
        </div>
        
        <p>Trân trọng,<br><strong>Đội ngũ Workspace Booking</strong></p>
    </div>
    
    <div class="footer">
        <p>Email này được gửi tự động. Vui lòng không trả lời email này.</p>
        <p>&copy; {{ date('Y') }} Workspace Booking. All rights reserved.</p>
    </div>
</body>
</html>
