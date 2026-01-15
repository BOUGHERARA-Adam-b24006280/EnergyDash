# EnergyDash - Tableau de Bord Énergie Renouvelable

![PHP Version](https://img.shields.io/badge/PHP-8.4-777BB4?style=flat&logo=php&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green)
![Status](https://img.shields.io/badge/Status-Development-orange)

**EnergyDash** est une solution de monitoring énergétique interactive permettant le suivi, l'analyse et la prévision de la production d'énergie renouvelable (solaire, éolien, hydraulique).

L'application combine l'import de données réelles (CSV) avec un **moteur de prédiction météorologique** (basé sur l'API Open-Meteo) pour combler les données manquantes et anticiper la production future.

---

## Fonctionnalités Clés

- **Visualisation Interactive :** Graphiques dynamiques (ApexCharts) pour le suivi de production.
- **Mode Hybride Intelligent :** Fusionne vos données historiques (CSV) avec des prévisions météorologiques en temps réel.
- **Gestion de Données :** Upload sécurisé de fichiers CSV avec détection automatique du format.
- **Espace Membre :** Authentification sécurisée, gestion de profil et isolation des données par utilisateur.
- **Multi-Villes :** Comparaison de production entre différentes zones géographiques.

---

## 🛠️ Stack Technique

Ce projet est construit sans framework lourd, selon une architecture **MVC (Modèle-Vue-Contrôleur)** propre.

| Domaine | Technologies |
| :--- | :--- |
| **Backend** | PHP 8.4, Composer (Autoloading PSR-4) |
| **Frontend** | HTML5, Tailwind CSS (via Preline UI), JavaScript |
| **Visualisation** | Charts.js |
| **Base de données** | MySQL |
| **API Externe** | Open-Meteo |

---

## 📁 Structure du projet

```
EnergyDash/
├── public/              # Racine Web (Assets, index.php)
│   ├── assets/          # CSS, JS, Images (Preline, Tailwind)
│   └── index.php        # Point d'entrée
├── src/                 # Code Source (MVC)
│   ├── Config/          # Routes et configuration DB
│   ├── Controllers/     # Logique de contrôle
│   ├── Core/            # Noyau (Router, Database, Model)
│   ├── Models/          # Accès aux données (SQL)
│   ├── Services/        # Services (Parser CSV, API Météo)
│   └── Views/           # Templates HTML/PHP
├── Storage/             # Stockage des fichiers CSV utilisateurs
├── tests/               # Tests unitaires (PHPUnit)
├── vendor/              # Dépendances Composer
└── .env.example         # Template de configuration
```

## � Prérequis

- PHP 7.4 ou supérieur
- Composer
- Serveur web (Apache, Nginx) ou XAMPP/WAMP
- Base de données MySQL/MariaDB

---

## 🚀 Installation

### 1. Cloner le dépôt
```bash
git clone https://github.com/BOUGHERARA-Adam-b24006280/EnergyDash.git
cd EnergyDash
```

### 2. Installer les dépendances
```bash
composer install
```

### 3. Configuration de l'environnement
Copiez le fichier d'exemple des variables d'environnement :
```bash
cp .env.example .env
```

Puis modifiez le fichier `.env` avec vos paramètres :
```bash
# Base de données
DATABASE_HOST=localhost
DATABASE_NAME=energy_dash
DATABASE_USERNAME=votre_nom_utilisateur
DATABASE_PASSWORD=votre_mot_de_passe

# Environnement de l'application
APP_ENV=local
APP_DEBUG=true
```

### 4. Configuration de la base de données
- Créez une base de données MySQL/MariaDB
- Vérifiez les paramètres dans le fichier `.env`

### 5. Lancement de l'application
- Démarrez votre serveur web (Apache/Nginx) ou XAMPP
- Accédez à `http://localhost/EnergyDash` dans votre navigateur

---

## 🛠️ Développement

### Structure des dépendances
Le projet utilise Composer pour la gestion des dépendances PHP :
- **PHPMailer** : Envoi d'emails sécurisé

### Variables d'environnement
Le fichier `.env` contient les configurations sensibles :
- Paramètres de connexion à la base de données
- Configuration de l'environnement (debug, mode)
- Autres variables spécifiques au projet

---

## 👥 Équipe

- BOUGHERARA	Adam
- CLOT-GODARD	Kenji
- HADDAD	Mohamed-Amine
- LEPAPE	Lucas
- MAILHE	Gustin
