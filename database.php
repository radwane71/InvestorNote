<?php
/**
 * إعدادات قاعدة البيانات
 */
class Database {
    private $host = 'localhost';
    private $dbname = 'investor_notebook';
    private $username = 'root';
    private $password = '';
    private $charset = 'utf8mb4';
    private $pdo;
    
    public function __construct() {
        $this->connect();
    }
    
    /**
     * الاتصال بقاعدة البيانات
     */
    private function connect() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}";
            $this->pdo = new PDO($dsn, $this->username, $this->password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->createTables();
        } catch (PDOException $e) {
            die("فشل الاتصال بقاعدة البيانات: " . $e->getMessage());
        }
    }
    
    /**
     * إنشاء الجداول إذا لم تكن موجودة
     */
    private function createTables() {
        // جدول المستخدمين
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                email VARCHAR(100),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                last_login TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        // جدول توزيع الراتب
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS salary_distribution (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                item_name VARCHAR(200) NOT NULL,
                amount DECIMAL(15,2) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        // جدول صفقات الأسهم
        $this->pdo->exec("
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
        
        // جدول خطط الاستثمار
        $this->pdo->exec("
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
        
        // جدول عمليات المحفظة (شراء/بيع الأسهم)
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS portfolio_transactions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                transaction_type ENUM('buy', 'sell') NOT NULL,
                stock_ticker VARCHAR(10) NOT NULL,
                num_shares INT NOT NULL,
                price DECIMAL(10,2) NOT NULL,
                commission DECIMAL(10,2) NOT NULL,
                vat DECIMAL(10,2) NOT NULL,
                total_cost DECIMAL(15,2) NOT NULL,
                transaction_date DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        echo "✅ تم إنشاء جدول عمليات المحفظة<br>";
        
        // جدول توزيعات الأرباح
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS dividends (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                date DATE NOT NULL,
                stock_name VARCHAR(100) NOT NULL,
                amount DECIMAL(15,2) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        echo "✅ تم إنشاء جدول توزيعات الأرباح<br>";
        
        // إنشاء المستخدم الافتراضي إذا لم يكن موجود
        $this->createDefaultUser();
    }
    
    /**
     * إنشاء المستخدم الافتراضي
     */
    private function createDefaultUser() {
        $checkUser = $this->pdo->prepare("SELECT id FROM users WHERE username = ?");
        $checkUser->execute(['radwan1411']);
        
        if ($checkUser->rowCount() == 0) {
            $hashedPassword = password_hash('1117473137', PASSWORD_DEFAULT);
            $insertUser = $this->pdo->prepare("
                INSERT INTO users (username, password, email, created_at) 
                VALUES (?, ?, ?, ?)
            ");
            $insertUser->execute([
                'radwan1411',
                $hashedPassword,
                'radwan1411@example.com',
                date('Y-m-d H:i:s')
            ]);
        }
    }
    
    /**
     * تسجيل الدخول
     */
    public function login($username, $password) {
        $stmt = $this->pdo->prepare("
            SELECT id, username, password, email, last_login 
            FROM users 
            WHERE username = ?
        ");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // تحديث وقت آخر دخول
            $updateLogin = $this->pdo->prepare("
                UPDATE users SET last_login = ? WHERE id = ?
            ");
            $updateLogin->execute([date('Y-m-d H:i:s'), $user['id']]);
            
            return $user;
        }
        
        return false;
    }
    
    /**
     * الحصول على بيانات المستخدم
     */
    public function getUserData($userId) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM users WHERE id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }
    
    /**
     * إضافة بند راتب
     */
    public function addSalaryItem($userId, $itemName, $amount) {
        $stmt = $this->pdo->prepare("
            INSERT INTO salary_distribution (user_id, item_name, amount) 
            VALUES (?, ?, ?)
        ");
        return $stmt->execute([$userId, $itemName, $amount]);
    }
    
    /**
     * الحصول على بيانات توزيع الراتب
     */
    public function getSalaryData($userId) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM salary_distribution 
            WHERE user_id = ? 
            ORDER BY created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    /**
     * إضافة صفقة سهم
     */
    public function addStockTransaction($userId, $stockName, $buyPrice, $quantity) {
        $totalAmount = $buyPrice * $quantity;
        $stmt = $this->pdo->prepare("
            INSERT INTO stock_transactions (user_id, stock_name, buy_price, quantity, total_amount) 
            VALUES (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$userId, $stockName, $buyPrice, $quantity, $totalAmount]);
    }
    
    /**
     * الحصول على صفقات الأسهم
     */
    public function getStockTransactions($userId) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM stock_transactions 
            WHERE user_id = ? 
            ORDER BY created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    /**
     * إضافة خطة استثمار
     */
    public function addInvestmentPlan($userId, $planName, $targetAmount, $monthlyAmount) {
        $stmt = $this->pdo->prepare("
            INSERT INTO investment_plans (user_id, plan_name, target_amount, monthly_amount) 
            VALUES (?, ?, ?, ?)
        ");
        return $stmt->execute([$userId, $planName, $targetAmount, $monthlyAmount]);
    }
    
    /**
     * الحصول على خطط الاستثمار
     */
    public function getInvestmentPlans($userId) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM investment_plans 
            WHERE user_id = ? 
            ORDER BY created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    /**
     * إضافة عملية محفظة جديدة
     */
    public function addPortfolioTransaction($userId, $transactionType, $stockTicker, $numShares, $price, $commission) {
        // حساب الضريبة (VAT 15%)
        $vat = ($numShares * $price) * 0.15;
        
        // حساب التكلفة الإجمالية
        if ($transactionType === 'buy') {
            $totalCost = ($numShares * $price) + $commission + $vat;
        } else { // sell
            $totalCost = ($numShares * $price) + $commission - $vat;
        }
        
        $stmt = $this->pdo->prepare("
            INSERT INTO portfolio_transactions 
            (user_id, transaction_type, stock_ticker, num_shares, price, commission, vat, total_cost, transaction_date) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $userId, 
            $transactionType, 
            $stockTicker, 
            $numShares, 
            $price, 
            $commission, 
            $vat, 
            $totalCost, 
            date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * الحصول على عمليات المحفظة
     */
    public function getPortfolioTransactions($userId) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM portfolio_transactions 
            WHERE user_id = ? 
            ORDER BY transaction_date DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    /**
     * الحصول على ملخصات المحفظة
     */
    public function getPortfolioSummary($userId) {
        $stmt = $this->pdo->prepare("
            SELECT 
                transaction_type,
                COUNT(*) as transaction_count,
                SUM(CASE WHEN transaction_type = 'buy' THEN total_cost ELSE 0 END) as total_buy_cost,
                SUM(CASE WHEN transaction_type = 'sell' THEN total_cost ELSE 0 END) as total_sell_value,
                SUM(CASE WHEN transaction_type = 'buy' THEN vat ELSE 0 END) as total_buy_vat,
                SUM(CASE WHEN transaction_type = 'sell' THEN vat ELSE 0 END) as total_sell_vat,
                SUM(CASE WHEN transaction_type = 'buy' THEN commission ELSE 0 END) as total_buy_commission,
                SUM(CASE WHEN transaction_type = 'sell' THEN commission ELSE 0 END) as total_sell_commission
            FROM portfolio_transactions 
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }
    
    /**
     * إغلاق الاتصال
     */
    public function __destruct() {
        $this->pdo = null;
    }
}

// اختبار الاتصال
try {
    $db = new Database();
    echo "✅ تم الاتصال بقاعدة البيانات بنجاح!";
    echo "<br>";
    echo "📊 الجداول المنشأة: users, salary_distribution, stock_transactions, investment_plans";
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage();
}
?>
