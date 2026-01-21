<?php
require_once 'includes/config.php';
requireAuth();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Intigravity - Sanitary Store System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
</head>
<body>
    <div class="app-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <div class="page-title">
                    <h2 style="font-size: 1.25rem;">
                        <?php 
                            $page = basename($_SERVER['PHP_SELF'], '.php');
                            echo ucfirst($page) === 'Index' ? 'Dashboard' : ucfirst($page); 
                        ?>
                    </h2>
                </div>
                <div class="user-profile">
                    <div style="text-align: right;">
                        <span style="display: block; font-weight: 600;"><?= htmlspecialchars($_SESSION['username']) ?></span>
                        <span style="font-size: 0.8rem; color: var(--text-light);"><?= ucfirst(htmlspecialchars($_SESSION['role'])) ?></span>
                    </div>
                    <div class="user-avatar">
                        <?= strtoupper(substr($_SESSION['username'], 0, 1)) ?>
                    </div>
                </div>
            </div>
