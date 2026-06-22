<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Kantin - <?= ucfirst($title ?? 'Home') ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">🛒 E-Kantin</div>
        <div class="nav-menu">
            <span class="nav-user">👤 <?= htmlspecialchars($_SESSION['username']) ?> (<?= ucfirst($_SESSION['role']) ?>)</span>
            <a href="../logout.php" class="btn btn-sm btn-danger">Logout</a>
        </div>
    </nav>
    <div class="container">