<?php
function deleteAccount($pdo, $accountId) {
    $sqlCheck = "SELECT COUNT(*) as cnt FROM Orders WHERE AccountID = :accountid";
    $stmtCheck = $pdo->prepare($sqlCheck);
    $stmtCheck->execute(['accountid' => $accountId]);
    
    if ($stmtCheck->fetch(PDO::FETCH_ASSOC)['cnt'] > 0) {
        return ['success' => false, 'message' => 'Cannot delete account with orders. Use Suspend instead.'];
    }
    
    $sql = "DELETE FROM Accounts WHERE AccountID = :accountid";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['accountid' => $accountId]);
    
    return ['success' => true, 'message' => 'Account deleted successfully!'];
}
?>