<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!--reference1: https://www.php.net/manual/en/language.operators.comparison.php#language.operators.comparison.ternary-->
    <!--reference2: https://www.php.net/manual/en/function.isset.php-->
    <title><?php echo isset($page_title) ? $page_title : 'BookSales'; ?></title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <header>
        <h1>Book Sales</h1>
    </header>
    <nav>
        <a href="index.php" <?php if(isset($active_page) && $active_page == 'browse') echo 'class="active"'; ?>>Browse Books</a>
        <a href="register_account.php" <?php if(isset($active_page) && $active_page == 'register') echo 'class="active"'; ?>>Register Account</a>
        <a href="update_account.php" <?php if(isset($active_page) && $active_page == 'update') echo 'class="active"'; ?>>Update Account Details</a>
        <a href="purchase_book.php" <?php if(isset($active_page) && $active_page == 'purchase') echo 'class="active"'; ?>>Purchase Book</a>
    </nav>
    <main>