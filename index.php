<?php
$page_title = "Browse Books";
$active_page = 'browse';

require_once 'config/db_connection.php';


include 'elements/header.php';

require_once 'functions/search_book.php';

$pdo = getConnection();

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$stmt = searchBooks($pdo, $search);

?>

    <form method="GET" action="index.php" style="margin-bottom: 2rem;">
        <div style="display: flex; gap: 0.5rem;">
            <input type="text" 
                   name="search" 
                   placeholder="Search by title or author..." 
                   value="<?php echo htmlspecialchars($search); ?>"
                   style="flex: 1;">
            <button type="submit">Search</button>
            <?php if ($search): ?>
                <a href="index.php" class="btn" style="background: #95a5a6;">Clear</a>
            <?php endif; ?>
        </div>
    </form>

    <div class="book-grid">
        <?php while ($book = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
            <div class="book-card">
                <img src="img/placeholder.png" alt="<?php echo htmlspecialchars($book['BookTitle']); ?>">
                <h3><?php echo htmlspecialchars($book['BookTitle']); ?></h3>
                <p><?php echo htmlspecialchars($book['Author']); ?></p>
                <p style="color: #95a5a6; font-size: 0.9rem;"><?php echo htmlspecialchars($book['Genre']); ?></p>
                <p class="price">€<?php echo number_format($book['Price'], 2); ?></p>
                <a href="purchase_book.php?book_id=<?php echo $book['BookID']; ?>" class="btn">Buy</a>
            </div>
        <?php endwhile; ?>
    </div>
    
    <?php if ($stmt->rowCount() == 0): ?>
        <p style="text-align: center; color: #7f8c8d; margin-top: 2rem;">
            No books found<?php echo $search ? ' matching your search' : ''; ?>.
        </p>
    <?php endif; ?>
</div>

<?php include 'elements/footer.php'; ?>