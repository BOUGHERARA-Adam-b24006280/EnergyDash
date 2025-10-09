# 🌱 Tableau de Bord Énergie Renouvelable

Dépôt contenant le code source du **tableau de bord interactif** pour le suivi en temps réel de la production d'énergie renouvelable (solaire, éolien, etc.).  
Le dashboard centralise les données, analyse les performances (KPIs, tendances, comparaisons aux prévisions) et alerte en cas d'écarts ou d'anomalies.  
Il offre une vue unifiée pour l'exploitation, l'ingénierie et le management, avec export et partage simplifiés.

---

## 📁 Structure du projet

```
EnergyDash/
├── assets/
├── config/
├── includes/
├── models/
├── views/
├── index.php
└── README.md
```

### 📦 Dossiers et fichiers

| Dossier/Fichier      | Rôle                                                                                   |
|----------------------|----------------------------------------------------------------------------------------|
| **assets/**          | 🎨 Fichiers statiques : CSS, JavaScript, images, icônes, polices, etc.                |
| **config/**          | ⚙️ Fichiers de configuration                                                          |
| **includes/**        | 🧩 Éléments réutilisables : headers, footers, fonctions utilitaires                    |
| **models/**          | 🗂️ Logique métier : gestion des données, classes et modèles                            |
| **views/**           | 🖥️ Fichiers de présentation : pages HTML/PHP                                           |
| **index.php**        | 🚪 Point d'entrée principal de l'application                                           |
| **README.md**        | 📚 Documentation du projet                                                             |

---

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
- HADDAH	Mohammed-Amine
- LEPAPE	Lucas
- MAILHE	Gustin