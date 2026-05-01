<?php
$page_title = "Update Account";
$active_page = 'update';
require_once 'config/db_connection.php';
require_once 'functions/delete_account.php';
require_once 'functions/suspend_account.php';

include 'elements/header.php';

$error = '';
$success = '';
$account = null;


// reactivate suspended account
if (isset($_GET['reactivate']) && is_numeric($_GET['reactivate'])) {
    $pdo = getConnection();
    $sql = "UPDATE Accounts SET AccountStatus = 'A' WHERE AccountID = :accountid";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['accountid' => $_GET['reactivate']]);
    $success = "Account reactivated! Please login again.";
}

// find account with matching email and password
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['find_account'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    $pdo = getConnection();
    $sql = "SELECT * FROM Accounts WHERE Email = :email AND Password = :password";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['email' => $email, 'password' => $password]);
    $account = $stmt->fetch(PDO::FETCH_ASSOC); //reference: https://www.php.net/manual/en/pdostatement.fetch.php
    
    if (!$account) {
        $error = "Invalid email or password.";
    } elseif ($account['AccountStatus'] == 'C') {
        $error = 'Account is suspended. <a href="update_account.php?reactivate=' . $account['AccountID'] . 
                    '" style="color: #3498db; text-decoration: underline;">Click here to reactivate</a>';
        $account = null;
    }
}

// Update account details
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_account'])) {
    $accountId = $_POST['account_id'];
    $forename = trim($_POST['forename']);
    $surname = trim($_POST['surname']);
    $eircode = trim($_POST['eircode']);
    $newPassword = trim($_POST['password']);
    
    if (strlen($eircode) != 7) {
        $error = "Eircode must be 7 characters.";
    } else {
        try {
            $pdo = getConnection();
            
            // Check if password should be updated
            if (!empty($newPassword)) {
                $sql = "UPDATE Accounts SET Forename = :forename, Surname = :surname, Eircode = :eircode, Password = :password
                        WHERE AccountID = :accountid";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'forename' => $forename,
                    'surname' => $surname,
                    'eircode' => $eircode,
                    'password' => $newPassword,
                    'accountid' => $accountId
                ]);
            } else {
                $sql = "UPDATE Accounts SET Forename = :forename, Surname = :surname, Eircode = :eircode
                        WHERE AccountID = :accountid";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'forename' => $forename,
                    'surname' => $surname,
                    'eircode' => $eircode,
                    'accountid' => $accountId
                ]);
            }
            
            $success = "Account updated successfully!";
            $account = null;
        } catch (PDOException $e) {
            $error = "Update failed.";
        }
    }
}

// Suspend account
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['suspend_account'])) {
    $accountId = $_POST['account_id'];
    $pdo = getConnection();
    $result = suspendAccount($pdo, $accountId);
    
    if ($result['success']) {
        $success = $result['message'];
        $account = null;
    } else {
        $error = $result['message'];
    }
}

// Delete account
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_account'])) {
    $accountId = $_POST['account_id'];
    $pdo = getConnection();
    $result = deleteAccount($pdo, $accountId);
    
    if ($result['success']) {
        $success = $result['message'];
        $account = null;
    } else {
        $error = $result['message'];
    }
}
?>

<div class="container">
    
    <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if (!$account): ?>
        <form method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" name="find_account">Login</button>
        </form>
    <?php else: ?>
        <form method="POST">
            <input type="hidden" name="account_id" value="<?php echo $account['AccountID']; ?>">
            
            <div class="form-group">
                <label>First Name</label>
                <input type="text" name="forename" value="<?php echo htmlspecialchars($account['Forename']); ?>" maxlength="30" required>
            </div>
            
            <div class="form-group">
                <label>Surname</label>
                <input type="text" name="surname" value="<?php echo htmlspecialchars($account['Surname']); ?>" maxlength="30" required>
            </div>
            
            <div class="form-group">
                <label>Email</label>
                <input type="email" value="<?php echo htmlspecialchars($account['Email']); ?>" disabled>
            </div>

            <div class="form-group">
                <label>New Password (leave blank to keep current)</label>
                <input type="password" name="password" maxlength="30">
            </div>
            
            <div class="form-group">
                <label>Eircode</label>
                <input type="text" name="eircode" value="<?php echo htmlspecialchars($account['Eircode']); ?>" maxlength="7" required>
            </div>
            
            <div style="display: flex; gap: 1rem;">
                <button type="submit" name="update_account">Update Account</button>
                <button type="submit" name="suspend_account" style="background: #f39c12;" onclick="return confirm('Suspend this account?')">Suspend Account</button>
                <button type="submit" name="delete_account" style="background: #e74c3c;" onclick="return confirm('Delete this account permanently?')">Delete Account</button>
            </div>
        </form>

        <h3 style="margin-top: 2rem; padding-top: 2rem; border-top: 2px solid #ddd;">Order History</h3>
        
        <?php
        // get orders for this account
        $sqlOrders = "SELECT b.BookTitle, o.TotalPrice, o.DateOrdered, ob.QtyOrdered
              FROM Orders o 
              JOIN OrderedBooks ob ON o.OrderID = ob.OrderID
              JOIN Books b ON ob.BookID = b.BookID
              WHERE o.AccountID = :accountid 
              ORDER BY o.DateOrdered DESC";
        $stmtOrders = $pdo->prepare($sqlOrders);
        $stmtOrders->execute(['accountid' => $account['AccountID']]);
        ?>

        <?php if ($stmtOrders->rowCount() > 0): ?>
            <table>
                <tr>
                    <th>Book Title</th>
                    <th>Quantity</th>
                    <th>Date</th>
                    <th>Total</th>
                </tr>
                <?php while ($order = $stmtOrders->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($order['BookTitle']); ?></td>
                        <td><?php echo $order['QtyOrdered']; ?></td>
                        <td><?php echo $order['DateOrdered']; ?></td>
                        <td>€<?php echo number_format($order['TotalPrice'], 2); ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p style="color: #7f8c8d; margin-top: 1rem;">No orders yet.</p>
        <?php endif; ?>
        
    <?php endif; ?>
</div>

<?php include 'elements/footer.php'; ?>