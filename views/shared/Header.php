<a href="/">EnergyDash</a>



<form method="POST" action="IndexTeste.php" style="display: inline;"> <!-- Appel via une requete post -->
    <input type="hidden" name="action" value="switchTheme"> <!-- défini l'action à "switchTheme" -->
    <button type="submit" id="theme-toggle"><?php require $_COOKIE['toggleTheme'];?></button> <!-- confirme l'envoie de la requete -->
</form>

<div class="d-flex align-items-center">
    <a class="btn btn-outline-primary pe-3 ps-3 fw-bold me-3" href="/login">Se connecter</a>
    <a class="btn btn-primary pe-4 ps-4 fw-bold" href="/register">S'inscrire</a>
</div>