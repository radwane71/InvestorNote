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

// معالجة إضافة عملية جديدة
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add_transaction') {
        $transactionType = $_POST['transaction_type'] ?? '';
        $stockTicker = $_POST['stock_ticker'] ?? '';
        $numShares = intval($_POST['num_shares'] ?? 0);
        $price = floatval($_POST['price'] ?? 0);
        $commission = floatval($_POST['commission'] ?? 0);
        
        if ($transactionType && $stockTicker && $numShares > 0 && $price > 0) {
            $db->addPortfolioTransaction($userId, $transactionType, $stockTicker, $numShares, $price, $commission);
        }
        // إعادة التوجيه لمنع إعادة الإرسال
        header('Location: transactions.php');
        exit();
    }
}

// الحصول على البيانات من قاعدة البيانات
$transactionsData = $db->getPortfolioTransactions($userId);
$portfolioSummary = $db->getPortfolioSummary($userId);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>عمليات المحفظة - مفكرة مستثمر</title>
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
            max-width: 1400px;
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
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
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
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            border: 2px solid #e9ecef;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr;
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
        
        .buy-row {
            background: rgba(40, 167, 69, 0.1);
        }
        
        .sell-row {
            background: rgba(220, 53, 69, 0.1);
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
            <h1>📈 PORTFOLIO TRANSACTIONS</h1>
            <p>إدارة عمليات الشراء والبيع في سوق الأسهم السعودي</p>
            <a href="private_page_db.php?logout=1" class="logout-btn">🚪 تسجيل الخروج</a>
        </div>
        
        <div class="nav-buttons">
            <a href="private_page_db.php" class="nav-btn">🏛️ لوحة التحكم</a>
            <a href="received_dividends.php" class="nav-btn">💰 توزيعات الأرباح</a>
            <a href="transactions.php" class="nav-btn">📈 عمليات المحفظة</a>
        </div>
        
        <!-- Dashboard Section -->
        <div class="dashboard-section">
            <h2 class="section-title">📊 ملخص المحفظة</h2>
            
            <div class="summary-cards">
                <div class="summary-card">
                    <div class="summary-number"><?php echo number_format($portfolioSummary['transaction_count'], 0); ?></div>
                    <div class="summary-label">إجمالي العمليات</div>
                </div>
                <div class="summary-card">
                    <div class="summary-number"><?php echo number_format($portfolioSummary['total_buy_cost'], 2); ?></div>
                    <div class="summary-label">إجمالي التكلفة الشراء</div>
                </div>
                <div class="summary-card">
                    <div class="summary-number"><?php echo number_format($portfolioSummary['total_sell_value'], 2); ?></div>
                    <div class="summary-label">إجمالي قيمة البيع</div>
                </div>
                <div class="summary-card">
                    <div class="summary-number"><?php echo number_format($portfolioSummary['total_buy_vat'], 2); ?></div>
                    <div class="summary-label">إجمالي ضريبة الشراء</div>
                </div>
                <div class="summary-card">
                    <div class="summary-number"><?php echo number_format($portfolioSummary['total_buy_commission'], 2); ?></div>
                    <div class="summary-label">إجمالي عمولة الشراء</div>
                </div>
                <div class="summary-card">
                    <div class="summary-number"><?php echo number_format($portfolioSummary['total_sell_vat'], 2); ?></div>
                    <div class="summary-label">إجمالي ضريبة البيع</div>
                </div>
                <div class="summary-card">
                    <div class="summary-number"><?php echo number_format($portfolioSummary['total_sell_commission'], 2); ?></div>
                    <div class="summary-label">إجمالي عمولة البيع</div>
                </div>
            </div>
        </div>
        
        <!-- Input Form -->
        <div class="dashboard-section">
            <h2 class="section-title">➕ إضافة عملية جديدة</h2>
            
            <form method="POST">
                <input type="hidden" name="action" value="add_transaction">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="transaction_type">النوع:</label>
                        <select id="transaction_type" name="transaction_type" required>
                            <option value="buy">شراء</option>
                            <option value="sell">بيع</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="stock_ticker">اسم السهم (Ticker):</label>
                        <input type="text" id="stock_ticker" name="stock_ticker" required placeholder="مثال: 2222.SR">
                    </div>
                    <div class="form-group">
                        <label for="num_shares">عدد الأسهم:</label>
                        <input type="number" id="num_shares" name="num_shares" required placeholder="0">
                    </div>
                    <div class="form-group">
                        <label for="price">سعر التنفيذ (ريال):</label>
                        <input type="number" id="price" name="price" step="0.01" required placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label for="commission">العمولة (ريال):</label>
                        <input type="number" id="commission" name="commission" step="0.01" required placeholder="0.00">
                    </div>
                </div>
                <button type="submit" class="add-btn">إضافة عملية</button>
            </form>
        </div>
        
        <!-- Data Display -->
        <div class="dashboard-section">
            <h2 class="section-title">📋 سجل العمليات</h2>
            
            <?php if (empty($transactionsData)): ?>
                <div style="text-align: center; padding: 40px; color: #999;">
                    <h3>لا توجد عمليات بعد</h3>
                    <p>ابدأ بإضافة عمليات من النموذج أعلاه</p>
                </div>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>التاريخ والوقت</th>
                            <th>النوع</th>
                            <th>السهم</th>
                            <th>العدد</th>
                            <th>السعر</th>
                            <th>العمولة</th>
                            <th>الضريبة</th>
                            <th>التكلفة الإجمالية</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactionsData as $transaction): ?>
                        <tr class="<?php echo $transaction['transaction_type'] === 'buy' ? 'buy-row' : 'sell-row'; ?>">
                            <td><?php echo htmlspecialchars($transaction['transaction_date']); ?></td>
                            <td>
                                <?php if ($transaction['transaction_type'] === 'buy'): ?>
                                    🟢 شراء
                                <?php else: ?>
                                    🔴 بيع
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($transaction['stock_ticker']); ?></td>
                            <td><?php echo number_format($transaction['num_shares']); ?></td>
                            <td><?php echo number_format($transaction['price'], 2); ?></td>
                            <td><?php echo number_format($transaction['commission'], 2); ?></td>
                            <td><?php echo number_format($transaction['vat'], 2); ?></td>
                            <td><?php echo number_format($transaction['total_cost'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
