<?php
function suspendAccount($pdo, $accountId) {
    $sql = "UPDATE Accounts SET AccountStatus = 'C' WHERE AccountID = :accountid";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['accountid' => $accountId]);
    
    return ['success' => true, 'message' => 'Account suspended successfully!'];
}
?>