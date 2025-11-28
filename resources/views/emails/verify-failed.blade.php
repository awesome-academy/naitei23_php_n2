<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác thực thất bại</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 20px;
            padding: 50px;
            text-align: center;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .error-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            font-size: 50px;
        }
        h1 {
            color: #1f2937;
            font-size: 28px;
            margin-bottom: 15px;
        }
        .message {
            color: #6b7280;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        .error-detail {
            background: #fef2f2;
            border: 1px solid #fecaca;
            padding: 15px 20px;
            border-radius: 8px;
            color: #dc2626;
            margin-bottom: 30px;
        }
        .btn-resend {
            display: inline-block;
            background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
            color: white;
            text-decoration: none;
            padding: 15px 40px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: bold;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn-resend:hover {
            transform: translateY(-2px);
        }
        .footer {
            margin-top: 40px;
            color: #9ca3af;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-icon">❌</div>
        
        <h1>Xác Thực Thất Bại</h1>
        
        <p class="message">
            Rất tiếc, chúng tôi không thể xác thực tài khoản của bạn.
        </p>
        
        <div class="error-detail">
            {{ $message }}
        </div>
        
        <p class="message">
            Vui lòng thử đăng ký lại hoặc liên hệ hỗ trợ nếu cần giúp đỡ.
        </p>
        
        <div class="footer">
            <p>© {{ date('Y') }} Workspace Booking. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
