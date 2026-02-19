<?php
require_once 'database.php';

session_start();

// معالجة تسجيل الدخول
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($username && $password) {
        $db = new Database();
        $user = $db->login($username, $password);
        
        if ($user) {
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['login_time'] = date('Y-m-d H:i:s');
            header('Location: private_page_db.php');
            exit();
        } else {
            $error = 'اسم المستخدم أو كلمة المرور غير صحيحة';
        }
    }
}

// تسجيل الخروج
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login_db.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - مفكرة مستثمر</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        
        .login-header {
            margin-bottom: 30px;
        }
        
        .login-header h1 {
            color: #2c3e50;
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .login-header p {
            color: #666;
            font-size: 0.9rem;
        }
        
        .db-status {
            background: #e8f5e8;
            border: 1px solid #d4edda;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 0.9rem;
        }
        
        .db-status.success {
            background: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        
        .form-group {
            margin-bottom: 20px;
            text-align: right;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #495057;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .login-btn {
            width: 100%;
            padding: 15px;
            background: #2c3e50;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
            margin-top: 10px;
        }
        
        .login-btn:hover {
            background: #34495e;
        }
        
        .back-link {
            margin-top: 20px;
            text-align: center;
        }
        
        .back-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: bold;
        }
        
        .back-link a:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 480px) {
            .login-container {
                padding: 20px;
                margin: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>🔐 مفكرة مستثمر</h1>
            <p>تسجيل الدخول للوصول للمحتوى الخاص</p>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="db-status">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">اسم المستخدم:</label>
                <input type="text" id="username" name="username" required autocomplete="username">
            </div>
            
            <div class="form-group">
                <label for="password">كلمة المرور:</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>
            
            <button type="submit" class="login-btn">تسجيل الدخول</button>
        </form>
        
        <div class="back-link">
            <a href="index.html">🏠 العودة للصفحة الرئيسية</a>
        </div>
    </div>
</body>
</html>
