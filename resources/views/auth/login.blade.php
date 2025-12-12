<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập Admin - BLX</title>
    <link href="admin_asset/bower_components/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="admin_asset/bower_components/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            width: 100%;
            max-width: 420px;
            padding: 40px;
            animation: slideUp 0.6s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header .logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .login-header .logo i {
            font-size: 36px;
            color: #fff;
        }
        
        .login-header h2 {
            color: #333;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .login-header p {
            color: #888;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }
        
        .form-group label {
            color: #555;
            font-weight: 500;
            margin-bottom: 8px;
            display: block;
        }
        
        .form-group .input-icon {
            position: absolute;
            left: 15px;
            top: 42px;
            color: #999;
        }
        
        .form-control {
            height: 50px;
            padding-left: 45px;
            border: 2px solid #e1e5eb;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .remember-forgot label {
            display: flex;
            align-items: center;
            color: #666;
            font-size: 14px;
            cursor: pointer;
        }
        
        .remember-forgot input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin-right: 8px;
            accent-color: #667eea;
        }
        
        .btn-login {
            width: 100%;
            height: 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .btn-login i {
            margin-right: 8px;
        }
        
        .alert {
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .back-link {
            text-align: center;
            margin-top: 25px;
        }
        
        .back-link a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s;
        }
        
        .back-link a:hover {
            color: #764ba2;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo">
                <i class="fa fa-car"></i>
            </div>
            <h2>Đăng nhập Admin</h2>
            <p>Hệ thống quản lý Lý thuyết Lái xe</p>
        </div>
        
        @if(session('error'))
            <div class="alert alert-danger">
                <i class="fa fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif
        
        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <div><i class="fa fa-exclamation-circle"></i> {{ $error }}</div>
                @endforeach
            </div>
        @endif
        
        <form method="POST" action="{{ route('login') }}">
            @csrf
            
            <div class="form-group">
                <label for="email">Email</label>
                <i class="fa fa-envelope input-icon"></i>
                <input type="email" 
                       class="form-control" 
                       id="email" 
                       name="email" 
                       value="{{ old('email') }}"
                       placeholder="Nhập email của bạn"
                       required 
                       autofocus>
            </div>
            
            <div class="form-group">
                <label for="password">Mật khẩu</label>
                <i class="fa fa-lock input-icon"></i>
                <input type="password" 
                       class="form-control" 
                       id="password" 
                       name="password" 
                       placeholder="Nhập mật khẩu"
                       required>
            </div>
            
            <div class="remember-forgot">
                <label>
                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    Ghi nhớ đăng nhập
                </label>
            </div>
            
            <button type="submit" class="btn btn-login">
                <i class="fa fa-sign-in"></i> Đăng nhập
            </button>
        </form>
        
        <div class="back-link">
            <a href="{{ route('home') }}"><i class="fa fa-arrow-left"></i> Quay về trang chủ</a>
        </div>
    </div>
</body>
</html>
