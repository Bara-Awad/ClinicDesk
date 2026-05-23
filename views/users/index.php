<?php
require_once __DIR__ . '/../../core/Auth.php';
Auth::requireRole('admin');
require_once __DIR__ . '/../../core/helpers.php';
require_once __DIR__ . '/../../core/CSRF.php';
$pageTitle = $pageTitle ?? 'Manage Users';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
require_once __DIR__ . '/../partials/sidebar.php';
?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0">Manage Users</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active">Users</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">

      <?php require_once __DIR__ . '/../partials/alerts.php'; ?>

      <!-- Filter & Search Bar -->
      <div class="card card-outline card-primary">
        <div class="card-body">
          <form method="GET" action="index.php" class="form-inline flex-wrap gap-2">
            <input type="hidden" name="page" value="users">
            <div class="form-group mr-2 mb-2">
              <select name="role" class="form-control form-control-sm">
                <option value="">All Roles</option>
                <?php foreach (['admin','doctor','patient'] as $r): ?>
                  <option value="<?= $r ?>" <?= ($_GET['role'] ?? '') === $r ? 'selected' : '' ?>>
                    <?= ucfirst($r) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group mr-2 mb-2">
              <input type="text" name="search" class="form-control form-control-sm"
                     placeholder="Search name or email…"
                     value="<?= e($_GET['search'] ?? '') ?>">
            </div>
            <button type="submit" class="btn btn-sm btn-primary mr-2 mb-2">
              <i class="fas fa-search mr-1"></i> Search
            </button>
            <a href="index.php?page=users" class="btn btn-sm btn-outline-secondary mb-2">
              <i class="fas fa-times mr-1"></i> Clear
            </a>
            <a href="index.php?page=users&action=create" class="btn btn-sm btn-success ml-auto mb-2">
              <i class="fas fa-user-plus mr-1"></i> Add User
            </a>
          </form>
        </div>
      </div>

      <!-- Users Table -->
      <div class="card">
        <div class="card-body p-0">
          <table class="table table-hover table-striped mb-0">
            <thead class="thead-light">
              <tr>
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Phone</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($usersList)): ?>
                <tr><td colspan="8" class="text-center text-muted py-4">No users found.</td></tr>
              <?php else: ?>
                <?php foreach ($usersList as $u): ?>
                <tr>
                  <td><?= (int) $u['id'] ?></td>
                  <td>
                    <div class="d-flex align-items-center">
                      <div class="rounded-circle bg-gradient-primary text-white d-flex align-items-center
                                  justify-content-center mr-2 font-weight-bold"
                           style="width:34px;height:34px;font-size:.85rem;background:#4e73df">
                        <?= strtoupper(mb_substr($u['name'], 0, 1)) ?>
                      </div>
                      <span><?= e($u['name']) ?></span>
                    </div>
                  </td>
                  <td><small><?= e($u['email']) ?></small></td>
                  <td>
                    <span class="badge <?=
                      $u['role'] === 'admin'   ? 'badge-danger' :
                      ($u['role'] === 'doctor' ? 'badge-info'   : 'badge-success')
                    ?>">
                      <?= e(ucfirst($u['role'])) ?>
                    </span>
                  </td>
                  <td><small><?= e($u['phone'] ?? '—') ?></small></td>
                  <td>
                    <?php if ((int) $u['is_active']): ?>
                      <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Active</span>
                    <?php else: ?>
                      <span class="badge badge-danger"><i class="fas fa-ban mr-1"></i>Suspended</span>
                    <?php endif; ?>
                  </td>
                  <td><small><?= e(formatDate($u['created_at'])) ?></small></td>
                  <td>
                    <a href="index.php?page=users&action=edit&id=<?= (int) $u['id'] ?>"
                       class="btn btn-xs btn-outline-primary mr-1" title="Edit">
                      <i class="fas fa-edit"></i>
                    </a>

                    <?php $currentId = \Auth::currentUser()['id']; ?>
                    <?php if ((int) $u['id'] !== $currentId): ?>
                    <form method="POST" action="index.php?page=users&action=toggle" class="d-inline"
                          onsubmit="return confirm('Toggle account status?')">
                      <?= \CSRF::field() ?>
                      <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
                      <button type="submit" class="btn btn-xs <?= $u['is_active'] ? 'btn-outline-warning' : 'btn-outline-success' ?>"
                              title="<?= $u['is_active'] ? 'Deactivate' : 'Activate' ?>">
                        <i class="fas <?= $u['is_active'] ? 'fa-user-slash' : 'fa-user-check' ?>"></i>
                      </button>
                    </form>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <?php if (isset($paginator)): ?>
        <div class="card-footer">
          <?php require_once __DIR__ . '/../partials/pagination.php'; ?>
        </div>
        <?php endif; ?>
      </div>

    </div>
  </section>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
