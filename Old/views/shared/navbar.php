
<nav class="navbar navbar-expand-md">
  <div class="container-fluid">
    <a class="navbar-brand fw-semibold text-body-secondary" href="/">EnergyDash</a>
    <?php if (!isset($_SESSION['user'])): ?>
      <div class="d-flex align-items-center">
        <a class="btn btn-outline-primary pe-3 ps-3 fw-bold me-3" href="/login">Se connecter</a>
        <a class="btn btn-primary pe-4 ps-4 fw-bold" href="/register">S'inscrire</a>
      </div>
    <?php endif; ?>
  </div>
</nav>  