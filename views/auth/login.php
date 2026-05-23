<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | ClinicDesk</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/adminlte/plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/adminlte/dist/css/adminlte.min.css">
  <style>
    body { background: linear-gradient(135deg, #1a237e 0%, #0d47a1 50%, #01579b 100%); min-height: 100vh; }
    .login-card {
      border-radius: 16px;
      box-shadow: 0 20px 60px rgba(0,0,0,.3);
      overflow: hidden;
    }
    .login-card .card-header {
      background: linear-gradient(135deg, #1565c0, #0288d1);
      padding: 2rem;
      text-align: center;
    }
    .login-card .card-body { padding: 2rem; }
    .brand-logo { font-size: 2rem; font-weight: 800; color: #fff; letter-spacing: 1px; }
    .brand-sub  { color: rgba(255,255,255,.75); font-size: .9rem; margin-top: .25rem; }
    .input-group-text { background: #f8f9fa; border-right: 0; }
    .form-control { border-left: 0; }
    .form-control:focus { box-shadow: none; border-color: #0288d1; }
    .btn-login {
      background: linear-gradient(135deg, #1565c0, #0288d1);
      border: none; border-radius: 8px;
      font-weight: 600; letter-spacing: .5px;
      padding: .7rem;
      transition: opacity .2s;
    }
    .btn-login:hover { opacity: .9; }
    .demo-creds { background: #f0f7ff; border-left: 3px solid #0288d1; }
  </style>
</head>
<body class="d-flex align-items-center justify-content-center">

<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-5 col-lg-4">

      <?php require_once __DIR__ . '/../partials/alerts.php'; ?>

      <div class="card login-card">
        <!-- Header -->
        <div class="card-header">
          <div class="brand-logo">
            <i class="fas fa-hospital-user mr-2"></i>ClinicDesk
          </div>
          <div class="brand-sub">Clinic Management System</div>
        </div>

        <!-- Body -->
        <div class="card-body bg-white">
          <p class="text-muted text-center mb-4 small">Sign in to your account</p>

          <form method="POST" action="index.php?page=login&action=process" novalidate>
            <?= \CSRF::field() ?>

            <!-- Email -->
            <div class="form-group">
              <label for="email" class="font-weight-600 small">Email Address</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-envelope text-muted"></i></span>
                </div>
                <input type="email" name="email" id="email"
                       class="form-control" placeholder="admin@clinic.local"
                       value="<?= e($_POST['email'] ?? '') ?>" required autofocus>
              </div>
            </div>

            <!-- Password -->
            <div class="form-group">
              <label for="password" class="font-weight-600 small">Password</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-lock text-muted"></i></span>
                </div>
                <input type="password" name="password" id="password"
                       class="form-control" placeholder="••••••••" required>
              </div>
            </div>

            <button type="submit" class="btn btn-primary btn-login btn-block text-white mt-3">
              <i class="fas fa-sign-in-alt mr-2"></i> Sign In
            </button>
          </form>
        </div>

        <!-- Demo credentials info -->
        <div class="card-footer bg-white py-3">
          <div class="demo-creds p-2 rounded small text-muted">
            <strong>Demo credentials:</strong><br>
            Email: <code>admin@clinic.local</code><br>
            Password: <code>Admin@1234</code>
          </div>
        </div>
      </div>

      <p class="text-center text-white-50 small mt-3">
        &copy; <?= date('Y') ?> ClinicDesk &mdash; All rights reserved
      </p>
    </div>
  </div>
</div>

<script src="<?= BASE_URL ?>/public/assets/adminlte/plugins/jquery/jquery.min.js"></script>
<script src="<?= BASE_URL ?>/public/assets/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
