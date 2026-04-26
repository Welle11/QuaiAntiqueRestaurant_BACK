
# QuaiAntique Restaurant — Back-end API

[![Author](https://img.shields.io/badge/author-gaetan.role%40gmail.com-blue.svg)](https://github.com/gaetanrole)


 * Copyright (C) STUDI, Inc - All Rights Reserved.
 * Unauthorized copying of this repository, via any medium is strictly prohibited.
 * Proprietary and confidential.
 * Written by Gaetan Rolé-Dubruille <gaetan.role@gmail.com>.


---

## Projet étudiant — Adaptation STUDI

Ce projet est une adaptation du projet de cours original dans le cadre de la formation STUDI.

**Développé et enrichi par Melle G. (Welle11)**

Inspiré également des projets étudiants :
- **ThomasBDC** — [ProjetQuaiAntique](https://github.com/ThomasBDC/ProjetQuaiAntique),(https://github.com/ThomasBDC/QuaiAntiqueRestaurantFront)

---

## Stack technique

- PHP 8.2
- Symfony LTS
- Doctrine ORM
- MySQL 8
- NelmioApiDocBundle (Swagger)
- NelmioCorsBundle

---

## Prérequis

- PHP >= 8.2
- MySQL >= 8.0
- Symfony CLI
- Composer
- Git

---

## Installation

1. Cloner le dépôt :
```bash
git clone https://github.com/Welle11/QuaiAntiqueRestaurant_BACK.git
cd studi-restaurant-symfony-lts-api
```

2. Installer les dépendances :
```bash
composer install
```

3. Créer le fichier `.env.local` et configurer la base de données :
```bash
cp .env .env.local
```

Renseigner la variable `DATABASE_URL` dans `.env.local` :

4. Créer la base de données et lancer les migrations :
```bash
php bin/console doctrine:database:create
php bin/console doctrine:schema:update --force
```

5. Lancer le serveur :
```bash
symfony serve
```

---

## Routes API

| Méthode | Route | Accès | Description |
|---------|-------|-------|-------------|
| POST | /api/registration | Public | Inscription |
| POST | /api/login | Public | Connexion |
| GET | /api/account/me | ROLE_USER | Infos utilisateur |
| PUT | /api/account/edit | ROLE_USER | Modifier son compte |
| GET | /api/picture | Public | Liste des photos |
| POST | /api/picture | ROLE_USER | Ajouter une photo |
| DELETE | /api/picture/{id} | ROLE_USER | Supprimer une photo |
| GET | /api/menu | Public | Liste des menus |
| POST | /api/menu | ROLE_USER | Ajouter un menu |
| DELETE | /api/menu/{id} | ROLE_USER | Supprimer un menu |
| GET | /api/reservation | ROLE_USER | Mes réservations |
| POST | /api/reservation | ROLE_USER | Créer une réservation |
| DELETE | /api/reservation/{id} | ROLE_USER | Supprimer une réservation |

---

## Documentation Swagger

Disponible sur : `http://127.0.0.1:8000/api/doc`
