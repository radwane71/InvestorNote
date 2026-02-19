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

// معالجة إضافة بيانات جديدة
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_salary':
                $item = $_POST['item'] ?? '';
                $amount = floatval($_POST['amount'] ?? 0);
                if ($item && $amount > 0) {
                    $db->addSalaryItem($userId, $item, $amount);
                }
                break;
                
            case 'add_stock':
                $stock_name = $_POST['stock_name'] ?? '';
                $buy_price = floatval($_POST['buy_price'] ?? 0);
                $quantity = intval($_POST['quantity'] ?? 0);
                if ($stock_name && $buy_price > 0 && $quantity > 0) {
                    $db->addStockTransaction($userId, $stock_name, $buy_price, $quantity);
                }
                break;
                
            case 'add_plan':
                $plan_name = $_POST['plan_name'] ?? '';
                $target_amount = floatval($_POST['target_amount'] ?? 0);
                $monthly_amount = floatval($_POST['monthly_amount'] ?? 0);
                if ($plan_name && $target_amount > 0 && $monthly_amount > 0) {
                    $db->addInvestmentPlan($userId, $plan_name, $target_amount, $monthly_amount);
                }
                break;
        }
        // إعادة التوجيه لمنع إعادة الإرسال
        header('Location: private_page_db.php');
        exit();
    }
}

// الحصول على البيانات من قاعدة البيانات
$salaryData = $db->getSalaryData($userId);
$stocksData = $db->getStockTransactions($userId);
$plansData = $db->getInvestmentPlans($userId);
$summary = $db->getFinancialSummary($userId);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم الخاصة - مفكرة مستثمر</title>
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
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .welcome-info h1 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .welcome-info p {
            color: #666;
            font-size: 0.9rem;
        }
        
        .db-status {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 8px 15px;
            border-radius: 5px;
            font-size: 0.8rem;
            margin-bottom: 10px;
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
        
        .tabs {
            display: flex;
            background: white;
            border-radius: 15px 15px 0 0;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .tab {
            flex: 1;
            padding: 15px;
            background: #f8f9fa;
            border: none;
            cursor: pointer;
            font-weight: bold;
            color: #666;
            transition: all 0.3s;
            border-bottom: 3px solid transparent;
        }
        
        .tab.active {
            background: white;
            color: #667eea;
            border-bottom-color: #667eea;
        }
        
        .tab:hover {
            background: #e9ecef;
        }
        
        .content-area {
            background: white;
            border-radius: 0 0 15px 15px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            min-height: 500px;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
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
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }
        
        .summary-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .summary-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .form-section {
            margin-bottom: 30px;
        }
        
        .form-section h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 1.3rem;
            border-right: 4px solid #667eea;
            padding-right: 15px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
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
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .empty-state h3 {
            margin-bottom: 10px;
            color: #999;
        }
        
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .tabs {
                flex-direction: column;
            }
            
            .tab {
                border-bottom: none;
                border-right: 3px solid transparent;
            }
            
            .tab.active {
                border-right-color: #667eea;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="welcome-info">
                <div class="db-status">🗄️ متصل بقاعدة البيانات</div>
                <h1>👤 مرحباً بك يا <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
                <p>آخر دخول: <?php echo htmlspecialchars($_SESSION['login_time']); ?></p>
            </div>
            <a href="private_page_db.php?logout=1" class="logout-btn">🚪 تسجيل الخروج</a>
        </div>
        
        <div class="tabs">
            <button class="tab active" onclick="showTab('salary')">💰 توزيع الراتب</button>
            <button class="tab" onclick="showTab('stocks')">📈 صفقات الأسهم</button>
            <button class="tab" onclick="showTab('plan')">📋 خطة الاستثمار</button>
        </div>
        
        <div class="content-area">
            <!-- توزيع الراتب -->
            <div id="salary-tab" class="tab-content active">
                <div class="summary-cards">
                    <div class="summary-card">
                        <div class="summary-number"><?php echo number_format($summary['salary']['total_salary'], 2); ?></div>
                        <div class="summary-label">إجمالي الراتب</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-number"><?php echo $summary['salary']['salary_items']; ?></div>
                        <div class="summary-label">عدد البنود</div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3>➕ إضافة بند راتب جديد</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="add_salary">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>البند:</label>
                                <input type="text" name="item" required placeholder="مثال: الراتب الأساسي">
                            </div>
                            <div class="form-group">
                                <label>المبلغ:</label>
                                <input type="number" name="amount" step="0.01" required placeholder="0.00">
                            </div>
                        </div>
                        <button type="submit" class="add-btn">إضافة بند</button>
                    </form>
                </div>
                
                <?php if (empty($salaryData)): ?>
                    <div class="empty-state">
                        <h3>📝 لا توجد بيانات بعد</h3>
                        <p>ابدأ بإضافة بنود الراتب من النموذج أعلاه</p>
                    </div>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>البند</th>
                                <th>المبلغ</th>
                                <th>التاريخ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($salaryData as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                                <td><?php echo number_format($item['amount'], 2); ?> ريال</td>
                                <td><?php echo htmlspecialchars($item['created_at']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <tr class="total-row">
                                <td colspan="3">الإجمالي</td>
                                <td><?php echo number_format($summary['salary']['total_salary'], 2); ?> ريال</td>
                            </tr>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
            
            <!-- صفقات الأسهم -->
            <div id="stocks-tab" class="tab-content">
                <div class="summary-cards">
                    <div class="summary-card">
                        <div class="summary-number"><?php echo number_format($summary['stocks']['total_stocks'], 2); ?></div>
                        <div class="summary-label">إجمالي الاستثمار</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-number"><?php echo $summary['stocks']['stock_transactions']; ?></div>
                        <div class="summary-label">عدد الصفقات</div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3>➕ إضافة صفقة سهم جديدة</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="add_stock">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>اسم السهم:</label>
                                <input type="text" name="stock_name" required placeholder="مثال: ARAMCO">
                            </div>
                            <div class="form-group">
                                <label>سعر الشراء:</label>
                                <input type="number" name="buy_price" step="0.01" required placeholder="0.00">
                            </div>
                            <div class="form-group">
                                <label>الكمية:</label>
                                <input type="number" name="quantity" required placeholder="0">
                            </div>
                        </div>
                        <button type="submit" class="add-btn">إضافة صفقة</button>
                    </form>
                </div>
                
                <?php if (empty($stocksData)): ?>
                    <div class="empty-state">
                        <h3>📈 لا توجد صفقات بعد</h3>
                        <p>ابدأ بإضافة صفقات الأسهم من النموذج أعلاه</p>
                    </div>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>اسم السهم</th>
                                <th>سعر الشراء</th>
                                <th>الكمية</th>
                                <th>الإجمالي</th>
                                <th>التاريخ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stocksData as $stock): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($stock['stock_name']); ?></td>
                                <td><?php echo number_format($stock['buy_price'], 2); ?> ريال</td>
                                <td><?php echo number_format($stock['quantity']); ?></td>
                                <td><?php echo number_format($stock['total_amount'], 2); ?> ريال</td>
                                <td><?php echo htmlspecialchars($stock['created_at']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <tr class="total-row">
                                <td colspan="4">الإجمالي</td>
                                <td><?php echo number_format($summary['stocks']['total_stocks'], 2); ?> ريال</td>
                            </tr>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
            
            <!-- خطة الاستثمار -->
            <div id="plan-tab" class="tab-content">
                <div class="summary-cards">
                    <div class="summary-card">
                        <div class="summary-number"><?php echo number_format($summary['plans']['total_targets'], 2); ?></div>
                        <div class="summary-label">إجمالي الأهداف</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-number"><?php echo $summary['plans']['investment_plans']; ?></div>
                        <div class="summary-label">عدد الخطط</div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3>➕ إضافة خطة استثمار جديدة</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="add_plan">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>اسم الخطة:</label>
                                <input type="text" name="plan_name" required placeholder="مثال: التقاعد المبكر">
                            </div>
                            <div class="form-group">
                                <label>المبلغ المستهدف:</label>
                                <input type="number" name="target_amount" step="0.01" required placeholder="0.00">
                            </div>
                            <div class="form-group">
                                <label>المبلغ الشهري:</label>
                                <input type="number" name="monthly_amount" step="0.01" required placeholder="0.00">
                            </div>
                        </div>
                        <button type="submit" class="add-btn">إضافة خطة</button>
                    </form>
                </div>
                
                <?php if (empty($plansData)): ?>
                    <div class="empty-state">
                        <h3>📋 لا توجد خطط بعد</h3>
                        <p>ابدأ بإضافة خطط استثمار من النموذج أعلاه</p>
                    </div>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>اسم الخطة</th>
                                <th>المبلغ المستهدف</th>
                                <th>المبلغ الشهري</th>
                                <th>التقدم</th>
                                <th>التاريخ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($plansData as $plan): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($plan['plan_name']); ?></td>
                                <td><?php echo number_format($plan['target_amount'], 2); ?> ريال</td>
                                <td><?php echo number_format($plan['monthly_amount'], 2); ?> ريال</td>
                                <td><?php echo number_format($plan['progress'], 2); ?>%</td>
                                <td><?php echo htmlspecialchars($plan['created_at']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <tr class="total-row">
                                <td colspan="4">إجمالي الأهداف</td>
                                <td><?php echo number_format($summary['plans']['total_targets'], 2); ?> ريال</td>
                            </tr>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        function showTab(tabName) {
            // إخفاء كل المحتويات
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // إظهار المحتوى المطلوب
            document.getElementById(tabName + '-tab').classList.add('active');
            event.target.classList.add('active');
        }
    </script>
</body>
</html>
