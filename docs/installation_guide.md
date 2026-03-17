# Guide d'Installation et Configuration

Ce guide détaille les étapes pour déployer la plateforme "Gestion Université" sur un serveur de test local sous le système Windows.

## 1. Prérequis
- Logiciel de serveur local : **XAMPP**, **WAMP** ou **Laragon**.
- Version PHP recommandée : PHP 8.x
- Base de données : MySQL / MariaDB

## 2. Configuration du Serveur Local (XAMPP)
1. Téléchargez et installez **XAMPP**.
2. Lancez le **Control Panel XAMPP**.
3. Démarrez les services **Apache** (Service Web) et **MySQL** (Base de données).

## 3. Mise en place des fichiers sources
1. Localisez le répertoire racine de votre serveur (ex: `C:\xampp\htdocs\` pour XAMPP).
2. Créez un dossier nommé exactement `GestionUniversite` (ou copiez tout le dossier du projet dans `htdocs`).
   - Le chemin final devra être `C:\xampp\htdocs\GestionUniversite\`.

## 4. Importation de la Base de Données
1. Ouvrez votre navigateur et accédez à l'interface PhpMyAdmin : `http://localhost/phpmyadmin/`.
2. Cliquez sur **Nouvelle base de données** dans la colonne de gauche.
3. Nommez la base de données `GestionUniversite` (respectez la casse) et choisissez l'interclassement `utf8mb4_unicode_ci`.
4. Cliquez sur Créer.
5. Sélectionnez cette nouvelle base, puis rendez-vous sur l'onglet **Importer**.
6. Cliquez sur **Choisir un fichier** et sélectionnez le fichier source `database.sql` qui se trouve à la racine du projet.
7. Cliquez sur **Exécuter** en bas de page. Les tables (ainsi que le compte admin par défaut) seront créées.

## 5. Configuration (Optionnelle)
Par défaut, le fichier de configuration `config/database.php` est réglé pour se connecter à une instance MySQL locale standard (utilisateur 'root', sans mot de passe).
Si votre serveur a une configuration différente (ex: WAMP où root a un mot de passe spécifique), vous devez modifier ces lignes dans `config/database.php` :

```php
private $host = 'localhost';
private $db_name = 'GestionUniversite';
private $username = 'votre_utilisateur'; // Ex: 'root'
private $password = 'votre_mot_de_passe'; 
```

## 6. Lancement de l'Application
1. Ouvrez votre navigateur.
2. Accédez à l'URL suivante : `http://localhost/GestionUniversite/`
3. Vous serez redirigé vers l'interface de connexion.

## 7. Premiers Pas (Comptes de test)
Le script SQL génère automatiquement un compte administrateur par défaut.
- **Identifiant :** `admin` ou `admin@universite.edu`
- **Mot de passe :** `admin123`

Lors de votre première connexion, nous vous invitons à :
1. Vous rendre dans Facultés pour créer une première Faculté (ex: FST - Faculté des Sciences).
2. Ajouter un Département (ex: Informatique).
3. Ajouter une Filière (ex: Génie Logiciel L3).
4. Créer des professeurs et des UEs associés.
