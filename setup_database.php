<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إعداد قاعدة البيانات - مفكرة مستثمر</title>
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
        
        .setup-container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            max-width: 600px;
            text-align: center;
        }
        
        .setup-header {
            margin-bottom: 30px;
        }
        
        .setup-header h1 {
            color: #2c3e50;
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .setup-header p {
            color: #666;
            font-size: 0.9rem;
            line-height: 1.6;
        }
        
        .status-message {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 0.9rem;
        }
        
        .status-message.success {
            background: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        
        .status-message.error {
            background: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        
        .database-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border: 2px solid #e9ecef;
            margin-bottom: 20px;
        }
        
        .database-info h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 1.1rem;
        }
        
        .database-info ul {
            list-style: none;
            padding: 0;
        }
        
        .database-info li {
            background: #667eea;
            color: white;
            padding: 10px 15px;
            margin-bottom: 8px;
            border-radius: 5px;
            font-weight: bold;
        }
        
        .nav-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
        }
        
        .nav-btn {
            background: #667eea;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            font-weight: bold;
            transition: background 0.3s;
        }
        
        .nav-btn:hover {
            background: #5a67d8;
        }
        
        @media (max-width: 768px) {
            .setup-container {
                padding: 20px;
                margin: 10px;
            }
            
            .nav-buttons {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <div class="setup-header">
            <h1>🔧 إعداد قاعدة البيانات</h1>
            <p>جاري إعداد قاعدة البيانات والجداول اللازمة لنظام مفكرة المستثمر...</p>
        </div>
        
        <?php
        // إعدادات الاتصال
        $host = 'localhost';
        $username = 'root';
        $password = '';
        $dbname = 'investor_notebook';
        
        try {
            // الاتصال بـ MySQL بدون تحديد قاعدة بيانات
            $pdo = new PDO("mysql:host=$host", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // إنشاء قاعدة البيانات إذا لم تكن موجودة
            $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            echo "<div class='status-message success'>✅ تم إنشاء قاعدة البيانات: $dbname</div>";
            
            // الاتصال بقاعدة البيانات
            $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // إنشاء الجداول
            
            // جدول المستخدمين
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(50) UNIQUE NOT NULL,
                    password VARCHAR(255) NOT NULL,
                    email VARCHAR(100),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    last_login TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            echo "<div class='status-message success'>✅ تم إنشاء جدول المستخدمين</div>";
            
            // جدول توزيع الراتب
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS salary_distribution (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    item_name VARCHAR(200) NOT NULL,
                    amount DECIMAL(15,2) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            echo "<div class='status-message success'>✅ تم إنشاء جدول توزيع الراتب</div>";
            
            // جدول صفقات الأسهم
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS stock_transactions (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    stock_name VARCHAR(100) NOT NULL,
                    buy_price DECIMAL(15,2) NOT NULL,
                    quantity INT NOT NULL,
                    total_amount DECIMAL(15,2) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            echo "<div class='status-message success'>✅ تم إنشاء جدول صفقات الأسهم</div>";
            
            // جدول خطط الاستثمار
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS investment_plans (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    plan_name VARCHAR(200) NOT NULL,
                    target_amount DECIMAL(15,2) NOT NULL,
                    monthly_amount DECIMAL(15,2) NOT NULL,
                    progress DECIMAL(5,2) DEFAULT 0.00,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            echo "<div class='status-message success'>✅ تم إنشاء جدول خطط الاستثمار</div>";
            
            // إنشاء المستخدم الافتراضي
            $checkUser = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $checkUser->execute(['radwan1411']);
            
            if ($checkUser->rowCount() == 0) {
                $hashedPassword = password_hash('1117473137', PASSWORD_DEFAULT);
                $insertUser = $pdo->prepare("
                    INSERT INTO users (username, password, email, created_at) 
                    VALUES (?, ?, ?, ?)
                ");
                $insertUser->execute([
                    'radwan1411',
                    $hashedPassword,
                    'radwan1411@example.com',
                    date('Y-m-d H:i:s')
                ]);
                echo "<div class='status-message success'>✅ تم إنشاء المستخدم الافتراضي: radwan1411</div>";
            } else {
                echo "<div class='status-message'>ℹ️ المستخدم الافتراضي موجود بالفعل</div>";
            }
            
        } catch (PDOException $e) {
            echo "<div class='status-message error'>❌ خطأ في قاعدة البيانات: " . $e->getMessage() . "</div>";
            echo "<div class='database-info'>";
            echo "<h3>🔧 إعدادات الاتصال:</h3>";
            echo "<ul>";
            echo "<li><strong>Host:</strong> $host</li>";
            echo "<li><strong>Database:</strong> $dbname</li>";
            echo "<li><strong>Username:</strong> $username</li>";
            echo "<li><strong>Password:</strong> " . str_repeat('*', strlen($password)) . "</li>";
            echo "</ul>";
            echo "</div>";
        }
        ?>
        
        <?php if (isset($pdo)): ?>
            <div class="database-info">
                <h3>📊 الجداول المنشأة:</h3>
                <ul>
                    <li>✅ users - بيانات المستخدمين</li>
                    <li>✅ salary_distribution - توزيع الراتب</li>
                    <li>✅ stock_transactions - صفقات الأسهم</li>
                    <li>✅ investment_plans - خطط الاستثمار</li>
                </ul>
            </div>
            
            <div class="database-info">
                <h3>👤 المستخدم الافتراضي:</h3>
                <ul>
                    <li><strong>اسم المستخدم:</strong> radwan1411</li>
                    <li><strong>كلمة المرور:</strong> 1117473137</li>
                    <li><strong>البريد الإلكتروني:</strong> radwan1411@example.com</li>
                </ul>
            </div>
        <?php endif; ?>
        
        <div class="nav-buttons">
            <a href="login_db.php" class="nav-btn">🔐 تسجيل الدخول</a>
            <a href="private_page_db.php" class="nav-btn">🏛️ لوحة التحكم</a>
        </div>
    </div>
</body>
</html>
    ");
    echo "✅ تم إنشاء جدول المستخدمين<br>";
    
    // جدول توزيع الراتب
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS salary_distribution (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            item_name VARCHAR(200) NOT NULL,
            amount DECIMAL(15,2) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ تم إنشاء جدول توزيع الراتب<br>";
    
    // جدول صفقات الأسهم
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS stock_transactions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            stock_name VARCHAR(100) NOT NULL,
            buy_price DECIMAL(15,2) NOT NULL,
            quantity INT NOT NULL,
            total_amount DECIMAL(15,2) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ تم إنشاء جدول صفقات الأسهم<br>";
    
    // جدول خطط الاستثمار
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS investment_plans (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            plan_name VARCHAR(200) NOT NULL,
            target_amount DECIMAL(15,2) NOT NULL,
            monthly_amount DECIMAL(15,2) NOT NULL,
            progress DECIMAL(5,2) DEFAULT 0.00,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ تم إنشاء جدول خطط الاستثمار<br>";
    
    // إنشاء المستخدم الافتراضي
    $checkUser = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $checkUser->execute(['radwan1411']);
    
    if ($checkUser->rowCount() == 0) {
        $hashedPassword = password_hash('1117473137', PASSWORD_DEFAULT);
        $insertUser = $pdo->prepare("
            INSERT INTO users (username, password, email, created_at) 
            VALUES (?, ?, ?, ?)
        ");
        $insertUser->execute([
            'radwan1411',
            $hashedPassword,
            'radwan1411@example.com',
            date('Y-m-d H:i:s')
        ]);
        echo "✅ تم إنشاء المستخدم الافتراضي: radwan1411<br>";
    } else {
        echo "ℹ️ المستخدم الافتراضي موجود بالفعل<br>";
    }
    
    echo "<h2>🎉 تم الإعداد بنجاح!</h2>";
    echo "<p><strong>البيانات:</strong></p>";
    echo "<ul>";
    echo "<li>قاعدة البيانات: $dbname</li>";
    echo "<li>المستخدم: radwan1411</li>";
    echo "<li>كلمة المرور: 1117473137</li>";
    echo "</ul>";
    
    echo "<p><strong>الجداول المنشأة:</strong></p>";
    echo "<ul>";
    echo "<li>users - بيانات المستخدمين</li>";
    echo "<li>salary_distribution - توزيع الراتب</li>";
    echo "<li>stock_transactions - صفقات الأسهم</li>";
    echo "<li>investment_plans - خطط الاستثمار</li>";
    echo "</ul>";
    
    echo "<p><a href='login.php' style='background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 اذهب لتسجيل الدخول</a></p>";
    
} catch (PDOException $e) {
    echo "❌ خطأ في قاعدة البيانات: " . $e->getMessage();
    echo "<br>";
    echo "<p>تأكد من أن WampServer يعمل وأن MySQL يعمل بشكل صحيح.</p>";
}
?>
