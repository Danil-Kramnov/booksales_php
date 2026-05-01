<?php
$page_title = "Purchase Book";
$active_page = 'purchase';
require_once 'config/db_connection.php';
include 'elements/header.php';

$error = '';
$success = '';
$step = 1;
$book = null;
$account = null;

$pdo = getConnection();

// pre-select book
if (isset($_GET['book_id']) && is_numeric($_GET['book_id'])) {
    $bookId = $_GET['book_id'];
    $sql = "SELECT * FROM Books WHERE BookID = :bookid AND BookStatus = 'A'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['bookid' => $bookId]);
    // reference: https://www.php.net/manual/en/pdostatement.fetch.php
    $book = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($book) {
        $step = 2;
    }
}

// step 1: select book manualy
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['select_book'])) {
    $bookId = $_POST['book_id'];
    $sql = "SELECT * FROM Books WHERE BookID = :bookid AND BookStatus = 'A'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['bookid' => $bookId]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);
    $step = 2;
}

// step 2: login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $bookId = $_POST['book_id'];
    
    $sql = "SELECT * FROM Accounts WHERE Email = :email AND Password = :password AND AccountStatus = 'A'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['email' => $email, 'password' => $password]);
    $account = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$account) {
        $error = "Invalid email or password.";
        $step = 2;
    } else {
        $sqlBook = "SELECT * FROM Books WHERE BookID = :bookid";
        $stmtBook = $pdo->prepare($sqlBook);
        $stmtBook->execute(['bookid' => $bookId]);
        $book = $stmtBook->fetch(PDO::FETCH_ASSOC);
        $step = 3;
    }
}

// step 3: purchase book
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_purchase'])) {
    $bookId = $_POST['book_id'];
    $accountId = $_POST['account_id'];
    $quantity = (int)$_POST['quantity'];
    
    try {
        $pdo->beginTransaction();
        
        // check stock and lock row
        $sqlCheck = "SELECT StockAmount, Price, BookTitle FROM Books WHERE BookID = :bookid FOR UPDATE";
        $stmtCheck = $pdo->prepare($sqlCheck);
        $stmtCheck->execute(['bookid' => $bookId]);
        $bookData = $stmtCheck->fetch(PDO::FETCH_ASSOC);
        
        $currentStock = $bookData['StockAmount'];
        $bookPrice = $bookData['Price'];
        $bookTitle = $bookData['BookTitle'];
        
        if ($currentStock < $quantity) {
            throw new Exception("Insufficient stock. Available: " . $currentStock);
        }
        
        // calculate total price
        $orderPrice = $bookPrice * $quantity;
        $totalPrice = $orderPrice;
        
        // update stock
        $newStock = $currentStock - $quantity;
        $sqlUpdateStock = "UPDATE Books SET StockAmount = :newstock WHERE BookID = :bookid";
        $stmtUpdateStock = $pdo->prepare($sqlUpdateStock);
        $stmtUpdateStock->execute(['newstock' => $newStock, 'bookid' => $bookId]);
        
        // create an order
        $sqlOrder = "INSERT INTO Orders (AccountID, TotalPrice, DateOrdered) 
                     VALUES (:accountid, :totalprice, CURDATE())";
        $stmtOrder = $pdo->prepare($sqlOrder);
        $stmtOrder->execute(['accountid' => $accountId, 'totalprice' => $totalPrice]);
        $orderId = $pdo->lastInsertId();
        
        // add book to ordered books
        $sqlOrderedBook = "INSERT INTO OrderedBooks (OrderID, BookID, QtyOrdered, OrderPrice)
                          VALUES (:orderid, :bookid, :qty, :orderprice)";
        $stmtOrderedBook = $pdo->prepare($sqlOrderedBook);
        $stmtOrderedBook->execute([
            'orderid' => $orderId,
            'bookid' => $bookId,
            'qty' => $quantity,
            'orderprice' => $orderPrice
        ]);
        
        $pdo->commit();
        
        $success = "Purchase successful!<br>
                    Order #$orderId created<br>
                    Book: $bookTitle<br>
                    Quantity: $quantity<br>
                    Total: €" . number_format($totalPrice, 2);
        
        $book = null;
        $account = null;
        $step = 1;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Purchase failed: " . $e->getMessage();
        $step = 3;
    }
}

// Show registered message
if (isset($_GET['registered'])) {
    $success = "Registration successful! You can now purchase books.";
}
?>

<div class="container">
    
    <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if ($step == 1): ?>

        <form method="POST">
            <div class="form-group">
                <label>Choose Book:</label>
                <select name="book_id" required>
                    <option value="">-- Select a Book --</option>
                    <?php
                    $sql = "SELECT BookID, BookTitle, Author, Price, StockAmount 
                            FROM Books WHERE BookStatus = 'A' AND StockAmount > 0
                            ORDER BY BookTitle";
                    $stmt = $pdo->query($sql);
                    
                    while ($b = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option value='{$b['BookID']}'>";
                        echo htmlspecialchars($b['BookTitle']) . " by " . htmlspecialchars($b['Author']);
                        echo " - €" . number_format($b['Price'], 2);
                        echo " (Stock: {$b['StockAmount']})";
                        echo "</option>";
                    }
                    ?>
                </select>
            </div>
            <button type="submit" name="select_book">Login</button>
        </form>
        
    <?php elseif ($step == 2): ?>
        <h3>Login to Your Account</h3>
        <p><strong>Selected:</strong> <?php echo htmlspecialchars($book['BookTitle']); ?> - €<?php echo number_format($book['Price'], 2); ?></p>
        
        <form method="POST">
            <input type="hidden" name="book_id" value="<?php echo $book['BookID']; ?>">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" name="login">Confirm Purchase</button>
        </form>
        
        <p style="margin-top: 1rem;">Don't have an account? <a href="register_account.php">Register here</a></p>
        
    <?php elseif ($step == 3): ?>
        <h3>Confirm Your Purchase</h3>
        
        <p><strong>Book:</strong> <?php echo htmlspecialchars($book['BookTitle']); ?></p>
        <p><strong>Author:</strong> <?php echo htmlspecialchars($book['Author']); ?></p>
        <p><strong>Price per unit:</strong> €<?php echo number_format($book['Price'], 2); ?></p>
        <p><strong>Available stock:</strong> <?php echo $book['StockAmount']; ?></p>
        <p><strong>Account:</strong> <?php echo htmlspecialchars($account['Forename'] . ' ' . $account['Surname']); ?></p>
        
        <form method="POST">
            <input type="hidden" name="book_id" value="<?php echo $book['BookID']; ?>">
            <input type="hidden" name="account_id" value="<?php echo $account['AccountID']; ?>">
            
            <div class="form-group">
                <label>Quantity:</label>
                <input type="number" 
                    name="quantity" 
                    min="1" 
                    max="<?php echo $book['StockAmount']; ?>" 
                    value="1" 
                    id="qty" 
                    data-price="<?php echo $book['Price']; ?>" 
                    required>
            </div>

            <p><strong>Total: €<span id="total"><?php echo number_format($book['Price'], 2); ?></span></strong></p>
            
            <button type="submit" name="confirm_purchase">Confirm Purchase</button>
        </form>
    <?php endif; ?>
</div>

<?php include 'elements/footer.php'; ?>