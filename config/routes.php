<?php
return [
    // Format : 'chemin' => ['Contrôleur', 'méthode']
    '' => ['HomeController', 'index'],          // Page d'accueil (/)
    'login' => ['AuthController', 'login'],      // /login
    'register' => ['AuthController', 'register'], // /register*
    'dashboard' => ['HomeController', 'dashboard'], // /dashboard
    'logout' => ['HomeController', 'logout'],
    'mentions-legales' => ['HomeController', 'mentionsLegales'],
    'forgot-password' => ['AuthController', 'forgotPassword'],
    'reset-password'  => ['AuthController', 'resetPassword'],
];
?>