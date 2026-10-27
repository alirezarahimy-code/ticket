<?php
declare(strict_types=1);
/** @var string $title */
/** @var array $user */
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl" data-theme-mode="<?= htmlspecialchars(function_exists('app_theme_mode') ? app_theme_mode() : 'light', ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="سامانه درخواست پشتیبانی">
    <title><?= htmlspecialchars($title ?? 'سامانه درخواست پشتیبانی') ?> | سامانه درخواست پشتیبانی</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/custom.css">
    <link rel="stylesheet" href="assets/theme-mode.css?v=3">
</head>
<body data-theme-mode="<?= htmlspecialchars(function_exists('app_theme_mode') ? app_theme_mode() : 'light', ENT_QUOTES, 'UTF-8') ?>">
    <div class="app-shell">
        <header class="topbar">
            <div class="topbar-inner">
                <a class="brand" href="index.php">
                    <span class="brand-mark">پ</span>
                    <span>
                        <strong>سامانه درخواست پشتیبانی</strong>
                        <small>مرکز خدمات و پشتیبانی</small>
                    </span>
                </a>
                <?php if (!empty($user)): ?>
                <div class="header-tools">
                    <?= function_exists('app_theme_toggle_markup') ? app_theme_toggle_markup() : '' ?>
                    <form class="global-search header-search" method="get" action="index.php">
                        <input type="hidden" name="page" value="search">
                        <input name="q" placeholder="جست‌وجوی درخواست، دارایی یا دانش‌نامه">
                    </form>
                    <a class="profile-shortcut" href="index.php?page=profile">
                        پروفایل: <?= htmlspecialchars($user['username']) ?>
                    </a>
                </div>
                <?php else: ?>
                <div class="header-tools">
                    <?= function_exists('app_theme_toggle_markup') ? app_theme_toggle_markup() : '' ?>
                </div>
                <?php endif; ?>
            </div>
            <?php if (!empty($user)): ?>
            <nav class="navbar" aria-label="ناوبری اصلی">
                <ul class="menu">
                    <li><a href="index.php">داشبورد</a></li>
                    <li><a href="index.php?page=tickets">درخواست‌ها</a></li>
                    <li><a href="index.php?page=reports">گزارش‌ها</a></li>
                    <li><a href="index.php?page=settings">تنظیمات</a></li>
                </ul>
            </nav>
            <?php endif; ?>
        </header>
        <div class="content-wrap">
