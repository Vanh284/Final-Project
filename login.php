<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập – <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/style.css">
</head>
<body class="bg-primary min-vh-100 d-flex align-items-center">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="card shadow-lg border-0">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <div class="bg-primary bg-gradient text-white rounded-circle d-inline-flex p-3 mb-3">
                            <i class="bi bi-headset fs-2"></i>
                        </div>
                        <h4 class="fw-bold mb-0"><?= APP_NAME ?></h4>
                        <p class="text-muted small">ISchool – ĐHQGHN</p>
                    </div>

                    <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="<?= APP_URL ?>/index.php?page=auth&action=login" id="loginForm" novalidate>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="email" class="form-control"
                                       placeholder="you@ischool.edu.vn"
                                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                       required autofocus>
                                <div class="invalid-feedback">Vui lòng nhập email hợp lệ.</div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Mật khẩu</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" name="password" class="form-control"
                                       placeholder="••••••••" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePwd">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <div class="invalid-feedback">Vui lòng nhập mật khẩu.</div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 fw-semibold">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Đăng nhập
                        </button>
                    </form>

                    <hr class="my-4">
                    <div class="text-muted small">
                        <p class="mb-1"><strong>Demo accounts:</strong></p>
                        <p class="mb-0">Admin: admin@ischool.edu.vn</p>
                        <p class="mb-0">Staff IT: it@ischool.edu.vn</p>
                        <p class="mb-0">Sinh viên: sv001@ischool.edu.vn</p>
                        <p class="mb-0 text-muted">Password: <code>Password@123</code></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="<?= APP_URL ?>/public/js/bootstrap.bundle.min.js"></script>
<script>
// Toggle password visibility
document.getElementById('togglePwd').addEventListener('click', function() {
    const pwd = this.previousElementSibling;
    const icon = this.querySelector('i');
    if (pwd.type === 'password') {
        pwd.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        pwd.type = 'password';
        icon.className = 'bi bi-eye';
    }
});
// Bootstrap validation
document.getElementById('loginForm').addEventListener('submit', function(e) {
    if (!this.checkValidity()) { e.preventDefault(); e.stopPropagation(); }
    this.classList.add('was-validated');
});
</script>
</body>
</html>
