# 🛒 PREMMAR — Écosystème Web Complet

<div align="center">


![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=flat-square&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479A1?style=flat-square&logo=mysql&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-ES6+-F7DF1E?style=flat-square&logo=javascript&logoColor=black)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=flat-square&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-Responsive-1572B6?style=flat-square&logo=css3&logoColor=white)

**Une plateforme digitale multi-services basée à Abidjan, Côte d'Ivoire**

[🛍️ PREMMAR BOUTIQUES](https://premmar.infinityfreeapp.com) · [🔧 PREMMAR SERVICE](#) · [👤 Auteur](#auteur)

</div>

---

## 📋 Table des matières

- [À propos du projet](#-à-propos-du-projet)
- [Sous-projets](#-sous-projets)
  - [PREMMAR BOUTIQUES](#-premmar-boutiques)
  - [PREMMAR SERVICE](#-premmar-service)
- [Technologies utilisées](#-technologies-utilisées)
- [Architecture du projet](#-architecture-du-projet)
- [Installation locale](#-installation-locale)
- [Configuration](#-configuration)
- [Fonctionnalités](#-fonctionnalités)
- [Paiement](#-paiement)
- [Déploiement](#-déploiement)
- [Auteur](#-auteur)

---

## 🌍 À propos du projet

**PREMMAR** est un écosystème web fullstack regroupant deux entités complémentaires :

- **PREMMAR BOUTIQUES** — une boutique e-commerce spécialisée en matériel de quincaillerie et fournitures diverses.
- **PREMMAR SERVICE** — une vitrine de services BTP (électricité, plomberie, carrelage, serrurerie) basée à Yopougon, Abidjan.

Le projet adopte une identité visuelle cohérente basée sur le **violet profond** (`#4f1f6e`) et l'**or ambré** (`#f3a125`), avec un design responsive pensé mobile-first.

---

## 📦 Sous-projets

### 🛍️ PREMMAR BOUTIQUES

> Boutique e-commerce de quincaillerie en ligne — déployée sur InfinityFree

**URL de production :** [premmar.infinityfreeapp.com](https://premmar.infinityfreeapp.com)

#### Fonctionnalités principales

- 🏷️ Catalogue produits avec catégories et filtres
- 🖼️ Upload et affichage d'images produits
- 🛒 Panier d'achat et gestion des commandes
- 👤 Système d'authentification (admin / agent / client)
- 🗄️ Panel d'administration CRUD complet
- 📱 Interface responsive (mobile, tablette, desktop)
- 💳 Intégration paiement (GeniusPay / FedaPay, Wave, Mobile Money)

#### Pages principales

| Page            | Description                      |
| --------------- | -------------------------------- |
| `index.php`     | Accueil avec produits en vedette |
| `categorie.php` | Listing par catégorie            |
| `produit.php`   | Fiche produit détaillée          |
| `panier.php`    | Gestion du panier                |
| `commande.php`  | Processus de commande            |
| `admin/`        | Panel d'administration           |
| `connexion.php` | Authentification                 |

---

### 🔧 PREMMAR SERVICE

> Site vitrine de services BTP — Yopougon, Abidjan

#### Services proposés

- ⚡ Électricité (installation, dépannage)
- 🚿 Plomberie (canalisation, sanitaires)
- 🧱 Carrelage (pose, rénovation)
- 🔐 Serrurerie (installation, remplacement)

#### Fonctionnalités

- 📄 Présentation des prestations par catégorie
- 📞 Formulaire de contact et demande de devis
- 📱 Design responsive avec navigation mobile optimisée
- 🗺️ Localisation Yopougon, Abidjan

---

## 🛠️ Technologies utilisées

| Catégorie       | Technologie                    |
| --------------- | ------------------------------ |
| Backend         | PHP 8.x                        |
| Base de données | MySQL 5.7+                     |
| Frontend        | HTML5, CSS3, JavaScript (ES6+) |
| Serveur local   | LAMPP / XAMPP                  |
| Hébergement     | InfinityFree                   |
| Paiement        | GeniusPay / FedaPay            |
| Déploiement     | SFTP via VS Code               |
| Versionning     | Git / GitHub                   |

---

## 🏗️ Architecture du projet

```
premmar/
│
├── config.php              # Configuration BDD (local/production)
├── header.php              # En-tête partagé
├── footer.php              # Pied de page partagé
├── index.php               # Page d'accueil
│
├── boutique/
│   ├── categorie.php
│   ├── produit.php
│   ├── panier.php
│   └── commande.php
│
├── service/
│   ├── index.php
│   ├── electricite.php
│   ├── plomberie.php
│   ├── carrelage.php
│   └── serrurerie.php
│
├── admin/
│   ├── dashboard.php
│   ├── produits.php        # CRUD produits
│   ├── commandes.php
│   └── utilisateurs.php
│
├── assets/
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   └── main.js
│   └── images/
│
└── uploads/                # Images produits uploadées
```

---

## 🚀 Installation locale

### Prérequis

- XAMPP ou LAMPP installé
- PHP >= 7.4
- MySQL >= 5.7
- Navigateur moderne

### Étapes

```bash
# 1. Cloner le dépôt
git clone https://github.com/ivangrah/premmar.git

# 2. Copier dans le dossier web
cp -r premmar/ /opt/lampp/htdocs/
# ou sur XAMPP Windows : C:/xampp/htdocs/

# 3. Démarrer Apache et MySQL
sudo /opt/lampp/lampp start

# 4. Importer la base de données
# Dans phpMyAdmin, importer le fichier : database/premmar.sql

# 5. Configurer les accès BDD dans config.php
# (voir section Configuration ci-dessous)

# 6. Accéder à l'application
# http://localhost/premmar/
```

---

## ⚙️ Configuration

Le fichier `config.php` gère automatiquement les deux environnements :

```php
<?php
// Détection automatique local / production
if ($_SERVER['HTTP_HOST'] === 'localhost') {
    // Environnement local
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'premmar_db');
} else {
    // Environnement production (InfinityFree)
    define('DB_HOST', 'sql.infinityfree.com');
    define('DB_USER', 'votre_user_prod');
    define('DB_PASS', 'votre_mdp_prod');
    define('DB_NAME', 'votre_bdd_prod');
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}
?>
```

---

## ✨ Fonctionnalités

### E-commerce (BOUTIQUES)

- [x] Catalogue avec catégories dynamiques
- [x] Recherche de produits
- [x] Gestion du panier (session PHP)
- [x] Processus de commande complet
- [x] Upload d'images produits
- [x] Panel admin (CRUD produits, commandes, utilisateurs)
- [x] Authentification multi-rôles (admin / agent / client)
- [x] Design responsive mobile-first

### Services BTP (SERVICE)

- [x] Présentation des 4 services
- [x] Navigation par catégorie
- [x] Formulaire de contact / devis
- [x] Menu mobile responsive
- [x] Design cohérent avec la charte PREMMAR

---

## 💳 Paiement

Le module de paiement intègre les solutions adaptées au marché ivoirien :

| Moyen de paiement | Fournisseur         | Statut    |
| ----------------- | ------------------- | --------- |
| Carte bancaire    | GeniusPay / FedaPay | ✅ Intégré |
| Wave              | Wave CI             | ✅ Intégré |
| Orange Money      | Orange CI           | ✅ Intégré |
| MTN Mobile Money  | MTN CI              | ✅ Intégré |

---

## 🌐 Déploiement

Le déploiement en production est effectué via **SFTP automatique** configuré dans VS Code.

```json
// .vscode/sftp.json (exemple)
{
  "host": "ftpupload.net",
  "username": "votre_user",
  "password": "votre_mdp",
  "remotePath": "/htdocs/",
  "uploadOnSave": true,
  "ignore": [".vscode", ".git", "node_modules"]
}
```

**Hébergeur :** InfinityFree  
**URL de production :** [premmar.infinityfreeapp.com](https://premmar.infinityfreeapp.com)

> ⚠️ Ne jamais versionner le fichier `sftp.json` contenant vos identifiants. Ajoutez-le au `.gitignore`.

---

## 👤 Auteur

**Grah Désiré Jean Ivan**  
Étudiant en Informatique & Génie Logiciel — ESATIC, Abidjan  
Développeur Fullstack Junior

[![GitHub](https://img.shields.io/badge/GitHub-ivangrah-181717?style=flat-square&logo=github)](https://github.com/ivangrah)

---

## 📄 Licence

Ce projet est développé dans le cadre d'un projet personnel et scolaire.  
© 2025 PREMMAR — Tous droits réservés.

---

<div align="center">
  <sub>Made with ❤️ in Abidjan, Côte d'Ivoire 🇨🇮</sub>
</div>

