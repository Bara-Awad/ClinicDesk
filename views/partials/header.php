<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= sanitize($pageTitle ?? APP_NAME) ?> | <?= sanitize(APP_NAME) ?></title>

  <!-- Google Font -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- AdminLTE CSS (local — no CDN) -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/adminlte/plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/adminlte/dist/css/adminlte.min.css">

  <style>
    body { font-family: 'Inter', sans-serif; }
    .brand-text { font-weight: 700; letter-spacing: 0.5px; }
    .sidebar-dark-primary .nav-sidebar > .nav-item > .nav-link.active {
      background-color: rgba(255,255,255,.15);
      border-left: 3px solid #fff;
    }
    .card { border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,.06); }
    .small-box { border-radius: 10px; }
    .badge { font-size: .75em; padding: .35em .6em; }
    .table th { font-weight: 600; font-size: .85rem; text-transform: uppercase; letter-spacing: .5px; }
  </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
