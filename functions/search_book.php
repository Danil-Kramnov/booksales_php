<?php
function searchBooks($pdo, $search = '') {
    if ($search) {
        $sql = "SELECT b.*, g.Description as Genre 
                FROM Books b 
                JOIN Genres g ON b.GenreCode = g.GenreCode 
                WHERE b.BookStatus = 'A' 
                AND (b.BookTitle LIKE :search OR b.Author LIKE :search) #reference: https://www.w3schools.com/sql/sql_like.asp
                ORDER BY b.BookTitle";
        $stmt = $pdo->prepare($sql);
        $searchParam = '%' . $search . '%';
        $stmt->execute(['search' => $searchParam]);
    } else {
        $sql = "SELECT b.*, g.Description as Genre 
                FROM Books b 
                JOIN Genres g ON b.GenreCode = g.GenreCode 
                WHERE b.BookStatus = 'A' 
                ORDER BY b.BookTitle";
        $stmt = $pdo->query($sql);
    }
    
    return $stmt;
}
?>