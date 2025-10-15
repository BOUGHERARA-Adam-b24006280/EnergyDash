<a href="/">EnergyDash</a>



<form method="POST" action="IndexTeste.php" style="display: inline;">
    <input type="hidden" name="action" value="switchTheme">
    <button type="submit" id="theme-toggle"><?php require $_COOKIE['toggleTheme'];?></button>
</form>

<div class="d-flex align-items-center">
    <a class="btn btn-outline-primary pe-3 ps-3 fw-bold me-3" href="/login">Se connecter</a>
    <a class="btn btn-primary pe-4 ps-4 fw-bold" href="/register">S'inscrire</a>
</div>