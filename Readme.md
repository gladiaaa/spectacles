# 🎭 Projet Spectacles — PHP + JWT + Middleware

Une application PHP **normalisée**, sans framework, illustrant :  
- la **gestion d’accès par rôles (PUBLIC / USER / ADMIN)**,  
- l’**authentification JWT (access + refresh tokens)** via cookies sécurisés,  
- un **routeur maison avec attributs PHP 8+ (`#[IsGranted]`)**,  
- et un mini **jeu de tests automatisés PowerShell** 🧪  

---

## 🚀 Fonctionnalités

| Type d’utilisateur | Fonctionnalités principales |
|--------------------|-----------------------------|
| **Public** | Accueil, liste et fiche des spectacles |
| **Utilisateur identifié** | Réserver une place, consulter ses réservations |
| **Administrateur** | Ajouter un spectacle |
| **Tous** | Système de login/logout, jeton d’accès + refresh, cookie sécurisé |

---

## 🏗️ Architecture du projet
```
spectacles/
├─ public/
│ └─ index.php # Front controller (entrée unique)
├─ src/
│ ├─ Kernel/ # Cœur de l’application
│ │ ├─ Router.php # Routeur maison
│ │ ├─ Auth.php # Gestion JWT + cookies
│ │ ├─ Config.php # Configuration globale
│ │ ├─ Response.php # Réponses JSON/HTML
│ │ └─ IsGranted.php # Attribut middleware
│ ├─ Controller/ # Contrôleurs par domaine
│ │ ├─ HomeController.php
│ │ ├─ ShowController.php
│ │ ├─ BookingController.php
│ │ ├─ ProfileController.php
│ │ ├─ AdminController.php
│ │ └─ AuthController.php
│ └─ Repository/
│ └─ ShowRepository.php # Données simulées (pas de BDD)
├─ vendor/ # Dépendances Composer
├─ composer.json
├─ composer.lock
├─ test-spectacles.ps1 # Tests basiques (manuel)
└─ test-spectacles-report.ps1 # Tests automatisés + rapport JSON/CSV
```

---

## ⚙️ Installation

### 1️⃣ Prérequis
- PHP ≥ **8.1**  
- **Composer** installé  
- (Optionnel) PowerShell pour exécuter les scripts de test

---

### 2️⃣ Installation du projet

```bash
# Cloner le projet
cd spectacles

# Installer la dépendance JWT
composer require firebase/php-jwt

# Configurer l’autoload PSR-4
composer config autoload.psr-4 "App\\":"src/"
composer dump-autoload


```
---
### 3️⃣ Lancer le serveur PHP
```
php -S localhost:8000 -t public
```

👉 Rendez-vous sur http://localhost:8000

---

### 4️⃣ Réalisation des test : 

#### /!\ dans un terminal à part
```
cd spectacles 

./test-spectacles-report
```

Suite à cela les tests ce font automatiquement, et créer un rapport des routes testées retrouvable dans le fichier **test-spectacles-report.ps1** en format json et csv

---

## 👤 Comptes de test


```
Rôle        	            Identifiant         	Mot de passe

Utilisateur             	ryan	                secret123
Utilisateur     	        gladia	                passw0rd
Administrateur            	admin               	admin123
```
---


## 🔐 Gestion JWT

Access token → durée de vie courte (10 s)

Refresh token → durée de vie longue (30 min)

Les deux sont stockés dans des cookies HttpOnly (ACCESS_TOKEN, REFRESH_TOKEN)

En cas d’expiration, /refresh permet de régénérer un jeton d’accès valide sans reconnecter l’utilisateur.

---

## ⚡ Routes principales
```
Méthode	Route	Accès	Description

GET	        /	                PUBLIC	        Page d’accueil (message, menu, login)
GET	        /shows	          PUBLIC	        Liste des spectacles
GET	        /shows/{id}	      PUBLIC	        Fiche d’un spectacle
POST	      /login	          PUBLIC	        Connexion utilisateur
POST	      /refresh	        PUBLIC	        Rafraîchit le token d’accès
POST	      /logout	          USER	          Déconnexion
POST	      /reserve/{id}	    USER	          Réserver une place
GET	        /profile	        USER	          Voir mes billets réservés
POST	      /admin/shows	    ADMIN	          Ajouter un spectacle
```
---


## 🧱 Technologies utilisées

**PHP 8.3**

**Composer**

**firebase/php-jwt**

**Cookies HttpOnly / SameSite**

**Attributs PHP 8+ (#[IsGranted])**

**PowerShell (tests automatisés)**

Aucune base de données (tout en mémoire pour la démo)

---



Auteur : Ryan Annic, Katia Sakri WEB3 HETIC


