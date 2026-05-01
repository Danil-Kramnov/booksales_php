<?php
$page_title = "Register Account";
$active_page = 'register';
require_once 'config/db_connection.php';

include 'elements/header.php';

$error = '';

// to store input data
$forename = isset($_POST['forename']) ? $_POST['forename'] : '';
$surname = isset($_POST['surname']) ? $_POST['surname'] : '';
$email = isset($_POST['email']) ? $_POST['email'] : '';
$eircode = isset($_POST['eircode']) ? $_POST['eircode'] : '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $forename = trim($_POST['forename']);
    $surname = trim($_POST['surname']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $eircode = trim($_POST['eircode']);
    
    if (empty($forename) || empty($surname) || empty($email) || empty($password) || empty($eircode)) {
        $error = "All fields are required.";
    } elseif (strlen($eircode) != 7) {
        $error = "Eircode must be exactly 7 characters.";
    } else {
        try {
            $pdo = getConnection();
            $sql = "INSERT INTO Accounts (Forename, Surname, Email, Password, Eircode, AccountStatus) 
                    VALUES (:forename, :surname, :email, :password, :eircode, 'A')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'forename' => $forename,
                'surname' => $surname,
                'email' => $email,
                'password' => $password,
                'eircode' => $eircode
            ]);
            
            header("Location: purchase.php?registered=1");
            exit;
            
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = "This email is already registered.";
            } else {
                $error = "Registration failed.";
            }
        }
    }
}
?>

<div class="container">
    
    <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label>First Name</label>
            <input type="text" name="forename" value="<?php echo htmlspecialchars($forename); ?>" maxlength="30" required>
        </div>
        
        <div class="form-group">
            <label>Surname</label>
            <input type="text" name="surname" value="<?php echo htmlspecialchars($surname); ?>" maxlength="30" required>
        </div>
        
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" maxlength="50" required>
        </div>
        
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" maxlength="30" required>
        </div>
        
        <div class="form-group">
            <label>Eircode</label>
            <input type="text" name="eircode" value="<?php echo htmlspecialchars($eircode); ?>" maxlength="7" required>
        </div>
        
        <button type="submit">Register Account</button>
    </form>
</div>

<?php include 'elements/footer.php'; ?>