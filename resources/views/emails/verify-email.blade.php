<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác thực tài khoản</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 10px 0 0;
            opacity: 0.9;
        }
        .content {
            padding: 30px;
        }
        .welcome-icon {
            font-size: 48px;
            text-align: center;
            margin-bottom: 20px;
        }
        .btn-container {
            text-align: center;
            margin: 30px 0;
        }
        .btn-verify {
            display: inline-block;
            background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
            color: white !important;
            text-decoration: none;
            padding: 15px 40px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            box-shadow: 0 4px 15px rgba(79, 70, 229, 0.4);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn-verify:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.5);
        }
        .info-box {
            background-color: #f0f9ff;
            border-left: 4px solid #0ea5e9;
            padding: 15px;
            margin: 20px 0;
            border-radius: 0 8px 8px 0;
        }
        .warning-box {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
            border-radius: 0 8px 8px 0;
        }
        .link-fallback {
            background-color: #f9fafb;
            padding: 15px;
            border-radius: 8px;
            word-break: break-all;
            font-size: 12px;
            color: #6b7280;
            margin-top: 20px;
        }
        .footer {
            text-align: center;
            color: #6b7280;
            font-size: 12px;
            padding: 20px;
            border-top: 1px solid #e5e7eb;
        }
        .features {
            display: flex;
            justify-content: space-around;
            margin: 20px 0;
            text-align: center;
        }
        .feature {
            flex: 1;
            padding: 10px;
        }
        .feature-icon {
            font-size: 24px;
            margin-bottom: 5px;
        }
        .feature-text {
            font-size: 12px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Workspace Booking</h1>
            <p>Chào mừng bạn đến với nền tảng đặt phòng làm việc</p>
        </div>
        
        <div class="content">
            <div class="welcome-icon">👋</div>
            
            <p>Xin chào <strong>{{ $userName }}</strong>,</p>
            
            <p>Cảm ơn bạn đã đăng ký tài khoản tại <strong>Workspace Booking</strong>!</p>
            
            <p>Để hoàn tất quá trình đăng ký và bắt đầu sử dụng dịch vụ, vui lòng xác thực email của bạn bằng cách nhấn vào nút bên dưới:</p>
            
            <div class="btn-container">
                <a href="{{ $verificationUrl }}" class="btn-verify">✅ Xác Thực Email</a>
            </div>
            
            <div class="info-box">
                <strong>💡 Sau khi xác thực, bạn có thể:</strong>
                <ul style="margin: 10px 0 0 0; padding-left: 20px;">
                    <li>Tìm kiếm và đặt phòng làm việc</li>
                    <li>Quản lý lịch đặt phòng của bạn</li>
                    <li>Nhận thông báo và ưu đãi đặc biệt</li>
                </ul>
            </div>
            
            <div class="warning-box">
                <strong>⚠️ Lưu ý:</strong>
                <ul style="margin: 10px 0 0 0; padding-left: 20px;">
                    <li>Link xác thực có hiệu lực trong <strong>24 giờ</strong></li>
                    <li>Nếu bạn không đăng ký tài khoản này, vui lòng bỏ qua email</li>
                </ul>
            </div>
            
            <div class="link-fallback">
                <strong>Nếu nút không hoạt động, copy link sau vào trình duyệt:</strong><br>
                {{ $verificationUrl }}
            </div>
            
            <p style="margin-top: 30px;">Trân trọng,<br><strong>Đội ngũ Workspace Booking</strong></p>
        </div>
        
        <div class="footer">
            <p>Email này được gửi tự động. Vui lòng không trả lời email này.</p>
            <p>&copy; {{ date('Y') }} Workspace Booking. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
