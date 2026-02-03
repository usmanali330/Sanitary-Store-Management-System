<?php
require_once 'includes/config.php';
requireAuth();
$page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Haji Baba - Management System</title>
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script>
        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            if(!container) return;
            
            const toast = document.createElement('div');
            toast.className =`toast ${type}`;
            
            let icon = 'fa-check-circle';
            if (type === 'error') icon = 'fa-circle-xmark';
            if (type === 'warning') icon = 'fa-triangle-exclamation';
            
            toast.innerHTML = `
                <i class="fa-solid ${icon}"></i>
                <span>${message}</span>
            `;
            
            container.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.add('fade-out');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
    </script>
</head>
<body class="page-<?= $page ?>">
    <div id="toast-container"></div>
    <div class="app-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <div class="page-title">
                    <h2>
                        <?php 
                            echo ucfirst($page) === 'Index' ? 'Dashboard' : ucfirst($page); 
                        ?>
                    </h2>
                </div>
                <div class="user-profile">
                    <div style="text-align: right; margin-right: 8px;">
                        <span style="display: block; font-weight: 700; font-size: 0.95rem; color: var(--text-main);"><?= htmlspecialchars($_SESSION['username'] ?? 'Guest') ?></span>
                        <span style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;"><?= ucfirst(htmlspecialchars($_SESSION['role'] ?? 'user')) ?></span>
                    </div>
                    <div class="user-avatar">
                        <?= strtoupper(substr($_SESSION['username'] ?? 'G', 0, 1)) ?>
                    </div>
                </div>
            </div>
