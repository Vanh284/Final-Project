<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Helpdesk') ?> – <?= APP_NAME ?></title>
    <!-- Bootstrap 5 (local) -->
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/bootstrap.min.css">
    <!-- Bootstrap Icons (local) -->
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/bootstrap-icons.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/style.css">
</head>
<body class="bg-light">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container-fluid px-4">
        <a class="navbar-brand fw-bold" href="<?= APP_URL ?>/index.php?page=dashboard">
            <i class="bi bi-headset me-2"></i><?= APP_NAME ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= (($_GET['page']??'')=='dashboard'||!isset($_GET['page']))?'active':'' ?>"
                       href="<?= APP_URL ?>/index.php?page=dashboard">
                        <i class="bi bi-speedometer2 me-1"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (($_GET['page']??'')=='tickets')?'active':'' ?>"
                       href="<?= APP_URL ?>/index.php?page=tickets">
                        <i class="bi bi-ticket-perforated me-1"></i>Tickets
                    </a>
                </li>
                <?php if (($_SESSION['user']['role']??'') === 'admin'): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-gear me-1"></i>Quản trị
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?= APP_URL ?>/index.php?page=users">
                            <i class="bi bi-people me-2"></i>Người dùng</a></li>
                        <li><a class="dropdown-item" href="<?= APP_URL ?>/index.php?page=departments">
                            <i class="bi bi-building me-2"></i>Bộ phận</a></li>
                        <li><a class="dropdown-item" href="<?= APP_URL ?>/index.php?page=categories">
                            <i class="bi bi-tags me-2"></i>Danh mục</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= APP_URL ?>/index.php?page=reports">
                            <i class="bi bi-bar-chart me-2"></i>Báo cáo</a></li>
                    </ul>
                </li>
                <?php endif; ?>
                <?php if (in_array($_SESSION['user']['role']??'', ['admin','staff'])): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= (($_GET['page']??'')=='backend')?'active':'' ?>"
                       href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-cpu me-1"></i>Quản lý App
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?= APP_URL ?>/index.php?page=backend&action=routing">
                            <i class="bi bi-diagram-3 me-2 text-primary"></i>Ticket Routing</a></li>
                        <li><a class="dropdown-item" href="<?= APP_URL ?>/index.php?page=backend&action=sla">
                            <i class="bi bi-alarm me-2 text-danger"></i>SLA & Escalation</a></li>
                        <li><a class="dropdown-item" href="<?= APP_URL ?>/index.php?page=backend&action=survey">
                            <i class="bi bi-star me-2 text-warning"></i>Survey Report</a></li>
                        <?php if (($_SESSION['user']['role']??'') === 'staff'): ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= APP_URL ?>/index.php?page=reports">
                            <i class="bi bi-bar-chart me-2"></i>Báo cáo</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>
            </ul>
            <?php if (!empty($_SESSION['user'])): ?>
            <div class="d-flex align-items-center gap-2">
                <span class="text-white-50 small d-none d-lg-inline">
                    <i class="bi bi-person-circle me-1"></i>
                    <?= htmlspecialchars($_SESSION['user']['full_name']) ?>
                    <span class="badge bg-light text-primary ms-1"><?= $_SESSION['user']['role'] ?></span>
                </span>
                <a href="<?= APP_URL ?>/index.php?page=auth&action=logout"
                   class="btn btn-sm btn-outline-light">
                    <i class="bi bi-box-arrow-right"></i> Đăng xuất
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- Flash messages -->
<div class="container-fluid px-4 mt-2" id="flash-area"></div>

<main class="container-fluid px-4 py-3">
