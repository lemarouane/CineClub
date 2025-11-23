# 🎬 CineClub - Application de Gestion de Club de Cinéma

Projet de Base de Données et WEB - Application PHP/MySQL

## 📋 Description

CineClub est une plateforme web complète pour gérer un club de cinéma local. Les membres peuvent proposer des films, voter pour les prochaines projections, s'inscrire aux séances, et partager leurs critiques.

## ✨ Fonctionnalités

### Pour les Membres
- ✅ Inscription et connexion sécurisées
- 🎬 Proposition de nouveaux films
- 🗳️ Vote pour les films proposés
- 🎟️ Inscription aux séances de projection
- ⭐ Notation et critiques des films visionnés
- 📊 Consultation des statistiques du club

### Pour les Administrateurs
- 📽️ Gestion complète des films (validation, changement de statut)
- 🎪 Programmation et gestion des séances
- 👥 Gestion des membres
- 📈 Tableau de bord avec statistiques détaillées

## 🛠️ Technologies Utilisées

- **Backend:** PHP 7.4+
- **Base de données:** MySQL 5.7+ / MariaDB
- **Frontend:** HTML5, CSS3, JavaScript
- **Serveur local:** XAMPP / WAMP / MAMP

## 📦 Installation

### Prérequis
- XAMPP, WAMP, ou MAMP installé
- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur

### Étapes d'installation

1. **Télécharger et placer les fichiers**
   ```
   Placez le dossier "cineclub" dans:
   - XAMPP: C:\xampp\htdocs\
   - WAMP: C:\wamp64\www\
   - MAMP: /Applications/MAMP/htdocs/
   ```

2. **Créer la base de données**
   - Ouvrez phpMyAdmin (http://localhost/phpmyadmin)
   - Cliquez sur "Nouveau" pour créer une nouvelle base de données
   - Nommez-la "cineclub"
   - Sélectionnez l'interclassement "utf8mb4_unicode_ci"
   - Cliquez sur l'onglet "Importer"
   - Importez le fichier `sql/cineclub.sql`

3. **Configurer la connexion (si nécessaire)**
   
   Ouvrez `config/database.php` et modifiez si besoin:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'cineclub');
   define('DB_USER', 'root');
   define('DB_PASS', ''); // Mot de passe MySQL (vide par défaut sur XAMPP/WAMP)
   ```

4. **Lancer l'application**
   
   Accédez à: http://localhost/cineclub/

## 👤 Comptes de Test

### Administrateur
- **Email:** admin@cineclub.com
- **Mot de passe:** 12345678

### Membres
- **Email:** m.haddad@uae.ac.ma
- **Mot de passe:** 12345678


## 📁 Structure du Projet

```
cineclub/
├── config/
│   └── database.php          # Configuration BDD
├── sql/
│   └── cineclub.sql          # Script SQL
├── includes/
│   ├── header.php            # En-tête commun
│   └── footer.php            # Pied de page commun
├── css/
│   └── style.css             # Styles CSS
├── admin/
│   ├── index.php             # Dashboard admin
│   ├── manage_films.php      # Gestion des films
│   └── manage_seances.php    # Gestion des séances
├── index.php                 # Page d'accueil
├── register.php              # Inscription
├── login.php                 # Connexion
├── logout.php                # Déconnexion
├── dashboard.php             # Tableau de bord membre
├── films.php                 # Catalogue de films
├── propose_film.php          # Proposer un film
├── vote.php                  # Système de vote
├── seances.php               # Gestion des séances
└── reviews.php               # Critiques et notations
```

## 🗄️ Modèle de Base de Données

### Tables principales

1. **users** - Utilisateurs du système
2. **films** - Catalogue de films
3. **votes** - Votes des membres pour les films
4. **seances** - Séances de projection
5. **participations** - Inscriptions aux séances
6. **reviews** - Critiques et notes

## 🎨 Diagrammes UML



## 🔒 Sécurité

- Mots de passe hashés avec `password_hash()` (bcrypt)
- Protection contre les injections SQL avec PDO et requêtes préparées
- Validation et nettoyage des entrées utilisateurs
- Gestion des sessions sécurisée
- Protection CSRF potentielle (à améliorer en production)

## 🚀 Améliorations Possibles

- [ ] Upload d'affiches de films
- [ ] Système de notifications par email
- [ ] Export des statistiques en PDF
- [ ] API REST pour application mobile
- [ ] Intégration avec une API de films (TMDb, OMDb)
- [ ] Système de paiement pour les adhésions
- [ ] Chat en direct pendant les projections
- [ ] Recommandations de films basées sur l'IA

## 📝 Notes Techniques

- **PDO** utilisé pour toutes les interactions avec la base de données
- **Sessions PHP** pour la gestion de l'authentification
- **Design responsive** compatible mobile
- **Validation côté serveur** pour toutes les entrées
- **Architecture MVC simplifiée** pour faciliter la maintenance

## 🐛 Débogage

Si vous rencontrez des problèmes:

1. Vérifiez que Apache et MySQL sont bien démarrés
2. Vérifiez les logs d'erreur PHP dans XAMPP/WAMP
3. Assurez-vous que la base de données est bien importée
4. Vérifiez les credentials dans `config/database.php`

## 👨‍💻 Développement

Pour contribuer au projet:

1. Créez une branche pour votre fonctionnalité
2. Testez localement
3. Documentez vos modifications
4. Créez une pull request

## 📄 Licence

Projet académique - Libre d'utilisation pour des fins éducatives

## 👥 Auteurs

Projet réalisé dans le cadre du cours "Base de Données et WEB"

---

**Bon courage pour votre projet ! 🎬✨**