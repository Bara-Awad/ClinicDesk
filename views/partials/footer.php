    </div><!-- /.content-wrapper -->

    <!-- Footer -->
    <footer class="main-footer">
      <strong>© <?= date('Y') ?> <a href="#"><?= e(APP_NAME) ?></a></strong>
      &mdash; Clinic Management Dashboard
      <div class="float-right d-none d-sm-inline-block">
        <b>Version</b> <?= APP_VERSION ?>
      </div>
    </footer>

    <!-- Control Sidebar — required by AdminLTE -->
    <aside class="control-sidebar control-sidebar-dark"></aside>
  </div><!-- /.wrapper -->

  <!-- AdminLTE JS (local — no CDN) -->
  <script src="<?= BASE_URL ?>/public/assets/adminlte/plugins/jquery/jquery.min.js"></script>
  <script src="<?= BASE_URL ?>/public/assets/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="<?= BASE_URL ?>/public/assets/adminlte/plugins/datatables/jquery.dataTables.min.js"></script>
  <script src="<?= BASE_URL ?>/public/assets/adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
  <script src="<?= BASE_URL ?>/public/assets/adminlte/plugins/chart.js/Chart.min.js"></script>
  <script src="<?= BASE_URL ?>/public/assets/adminlte/dist/js/adminlte.min.js"></script>

  <!-- Initialise DataTables on any table with .data-table class -->
  <script>
    $(function () {
      if ($.fn.DataTable) {
        $('.data-table').DataTable({
          "paging": false,        // we use server-side pagination
          "ordering": true,
          "info": false,
          "searching": false,     // we have our own search
          "responsive": true,
          "language": { "emptyTable": "No records found." }
        });
      }
    });
  </script>

  <?php if (isset($extraScripts)) echo $extraScripts; ?>
</body>
</html>
