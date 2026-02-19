<?php
require_once 'database.php';

session_start();

// التحقق من تسجيل الدخول
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login_db.php');
    exit();
}

// معالجة تسجيل الخروج
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login_db.php');
    exit();
}

// تهيئة قاعدة البيانات
$db = new Database();
$userId = $_SESSION['user_id'];

// معالجة إضافة توزيعة جديدة
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add_dividend') {
        $date = $_POST['date'] ?? '';
        $stockName = $_POST['stock_name'] ?? '';
        $amount = floatval($_POST['amount'] ?? 0);
        
        if ($date && $stockName && $amount > 0) {
            $db->addDividend($userId, $date, $stockName, $amount);
        }
        // إعادة التوجيه لمنع إعادة الإرسال
        header('Location: received_dividends.php');
        exit();
    }
}

// الحصول على البيانات من قاعدة البيانات
$dividendsData = $db->getDividends($userId);
$dividendSummary = $db->getDividendSummary($userId);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>توزيعات الأرباح - مفكرة مستثمر</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Arial, sans-serif;
            background: #2c3e50;
            color: #ffffff;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            background: linear-gradient(135deg, #34495e, #2c3e50);
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
            text-align: center;
        }
        
        .header h1 {
            color: white;
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .header p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
        }
        
        .logout-btn {
            background: #dc3545;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-weight: bold;
            transition: background 0.3s;
        }
        
        .logout-btn:hover {
            background: #c82333;
        }
        
        .nav-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 30px;
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
        
        .dashboard-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .section-title {
            color: #2c3e50;
            font-size: 1.5rem;
            margin-bottom: 20px;
            text-align: center;
            border-right: 4px solid #667eea;
            padding-right: 15px;
        }
        
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .summary-card {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 25px;
            border-radius: 10px;
            text-align: center;
        }
        
        .summary-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .summary-label {
            font-size: 1rem;
            opacity: 0.9;
        }
        
        .form-section {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            border: 2px solid #e9ecef;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group label {
            margin-bottom: 8px;
            font-weight: bold;
            color: #495057;
        }
        
        .form-group input {
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
            background: white;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .add-btn {
            background: #667eea;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s;
        }
        
        .add-btn:hover {
            background: #5a67d8;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .data-table th {
            background: #667eea;
            color: white;
            padding: 15px;
            text-align: right;
            font-weight: bold;
        }
        
        .data-table td {
            padding: 12px 15px;
            text-align: right;
            border-bottom: 1px solid #e9ecef;
        }
        
        .data-table tr:hover {
            background: #f8f9fa;
        }
        
        .total-row {
            background: #e9ecef;
            font-weight: bold;
        }
        
        .total-row td {
            padding: 15px;
            font-size: 1.1rem;
            color: #2c3e50;
        }
        
        .year-selector {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .year-btn {
            background: #667eea;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s;
        }
        
        .year-btn.active {
            background: #764ba2;
        }
        
        .year-btn:hover {
            background: #5a67d8;
        }
        
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .summary-cards {
                grid-template-columns: 1fr;
            }
            
            .nav-buttons {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>💰 RECEIVED DIVIDENDS</h1>
            <p>تتبع وإدارة توزيعات الأرباح المستلمة</p>
            <a href="private_page_db.php?logout=1" class="logout-btn">🚪 تسجيل الخروج</a>
        </div>
        
        <div class="nav-buttons">
            <a href="private_page_db.php" class="nav-btn">🏛️ لوحة التحكم</a>
            <a href="received_dividends.php" class="nav-btn">💰 توزيعات الأرباح</a>
        </div>
        
        <!-- Dashboard Section -->
        <div class="dashboard-section">
            <h2 class="section-title">📊 ملخص التوزيعات</h2>
            
            <div class="summary-cards">
                <div class="summary-card">
                    <div class="summary-number"><?php echo number_format($dividendSummary['total_dividends'], 2); ?></div>
                    <div class="summary-label">إجمالي التوزيعات</div>
                </div>
                <div class="summary-card">
                    <div class="summary-number"><?php echo $dividendSummary['dividend_count']; ?></div>
                    <div class="summary-label">عدد التوزيعات</div>
                </div>
                <div class="summary-card">
                    <div class="summary-number"><?php echo $dividendSummary['earliest_date']; ?></div>
                    <div class="summary-label">أول توزيعة</div>
                </div>
                <div class="summary-card">
                    <div class="summary-number"><?php echo $dividendSummary['latest_date']; ?></div>
                    <div class="summary-label">آخر توزيعة</div>
                </div>
            </div>
        </div>
        
        <!-- Year Selection -->
        <div class="dashboard-section">
            <h2 class="section-title">📅 عرض حسب السنة</h2>
            
            <div class="year-selector">
                <?php
                // الحصول على السنوات المتاحة
                $years = [];
                if (!empty($dividendsData)) {
                    foreach ($dividendsData as $dividend) {
                        $year = date('Y', strtotime($dividend['date']));
                        if (!in_array($year, $years)) {
                            $years[] = $year;
                        }
                    }
                }
                rsort($years);
                
                foreach ($years as $year) {
                    $isActive = isset($_GET['year']) && $_GET['year'] == $year;
                    echo "<button class='year-btn" onclick=\"window.location.href='received_dividends.php?year=" . $year . "'\">" . $year . "</button>";
                }
                ?>
            </div>
        </div>
        
        <!-- Input Form -->
        <div class="dashboard-section">
            <h2 class="section-title">➕ إضافة توزيعة جديدة</h2>
            
            <form method="POST">
                <input type="hidden" name="action" value="add_dividend">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="date">التاريخ:</label>
                        <input type="date" id="date" name="date" required>
                    </div>
                    <div class="form-group">
                        <label for="stock_name">اسم السهم:</label>
                        <input type="text" id="stock_name" name="stock_name" required placeholder="مثال: ARAMCO">
                    </div>
                    <div class="form-group">
                        <label for="amount">المبلغ المستلم:</label>
                        <input type="number" id="amount" name="amount" step="0.01" required placeholder="0.00">
                    </div>
                </div>
                <button type="submit" class="add-btn">إضافة توزيعة</button>
            </form>
        </div>
        
        <!-- Data Display -->
        <div class="dashboard-section">
            <h2 class="section-title">📋 بيانات التوزيعات</h2>
            
            <?php
            // فلترة البيانات حسب السنة المختارة
            $filteredData = $dividendsData;
            if (isset($_GET['year'])) {
                $selectedYear = $_GET['year'];
                $filteredData = array_filter($dividendsData, function($dividend) use ($selectedYear) {
                    return date('Y', strtotime($dividend['date'])) == $selectedYear;
                });
            }
            
            if (!empty($filteredData)): ?>
                <div style="text-align: center; padding: 40px; color: #999;">
                    <h3>لا توجد توزيعات للسنة <?php echo htmlspecialchars($_GET['year'] ?? ''); ?></h3>
                    <p>ابدأ بإضافة توزيعات من النموذج أعلاه</p>
                </div>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>التاريخ</th>
                            <th>اسم السهم</th>
                            <th>المبلغ المستلم</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($filteredData as $dividend): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($dividend['date']); ?></td>
                            <td><?php echo htmlspecialchars($dividend['stock_name']); ?></td>
                            <td><?php echo number_format($dividend['amount'], 2); ?> ريال</td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="total-row">
                            <td colspan="2">الإجمالي للسنة <?php echo htmlspecialchars($_GET['year'] ?? ''); ?></td>
                            <td><?php 
                                $yearTotal = array_sum(array_column($filteredData, 'amount')); 
                                echo number_format($yearTotal, 2); 
                            ?> ريال</td>
                        </tr>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
