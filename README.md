<br />
<div align="center">
  <h1 align="center">Gestion Université Pro</h1>

  <p align="center">
    Plateforme professionnelle de gestion intégrée pour les établissements d'enseignement supérieur.
    <br />
    <a href="docs/technical_documentation.md"><strong>Explorer la documentation technique »</strong></a>
    <br />
    <br />
    <a href="docs/installation_guide.md">Guide d'Installation</a>
    ·
    <a href="docs/user_manual.md">Manuel Utilisateur</a>
  </p>
</div>

## 📌 À Propos du Projet

**Gestion Université Pro** est une application web académique complète conçue pour centraliser et optimiser l'administration d'une université. Le système offre une interface moderne et sécurisée, adaptée aux besoins des trois piliers de l'éducation : l'administration, le corps professoral et les étudiants.

Ce projet a été développé dans le cadre d'un concours académique en informatique, avec pour objectif de fournir une solution logicielle robuste, déployable rapidement et respectant les standards de développement professsionnels.

### 🌟 Fonctionnalités Principales

*   **🎓 Portail Étudiant :** Inscription, visualisation de l'emploi du temps, consultation des notes et impression officielle des relevés de notes (Calcul dynamique des moyennes pondérées LMD).
*   **👨‍🏫 Portail Enseignant :** Accès sécurisé aux matières attribuées, saisie des notes et interface d'emploi du temps dédiée.
*   **⚙️ Portail Administrateur :**
    *   Tableau de bord statistique intelligent (Graphiques dynamiques).
    *   Gestion structurée de l'arborescence académique (Facultés -> Départements -> Filières -> Semestres).
    *   Gestion du catalogue des Unités d'Enseignement (Crédits, Coefficients).
    *   Création des plannings et emplois du temps (Salles, Heures, Enseignants).
    *   Moteur de journalisation complet (System Logs) pour l'audit de sécurité.
    *   Module d'export et sauvegarde (Backup) automatique de la base de données.

### 🛠️ Technologies Utilisées

Ce projet privilégie une approche bas niveau (Vanilla) pour prouver la maîtrise des fondamentaux de l'architecture web, de la logique de programmation et de la sécurité des données.

*   **Frontend :** HTML5 sémantique, CSS3 (Flexbox/Grid, Variables CSS pour thèmes), JavaScript (Vanilla, DOM manipulation, intégration Chart.js).
*   **Backend :** PHP (Architecture inspirée du modèle MVC, séparation forte de la logique métier et de la présentation).
*   **Base de Données :** MySQL (Modélisation relationnelle hautement normalisée).
*   **Sécurité :** PDO (Requêtes préparées anti-injection SQL), Hachage BCRYPT (`password_hash`), Protections anti-XSS et anti-CSRF, et middleware RBAC (Role-Based Access Control).

## 🚀 Démarrage Rapide

Pour tester et exécuter le projet localement sous **XAMPP / WAMP / Laragon** :

1.  **Cloner ou télécharger** ce répertoire dans votre dossier de serveur web local (ex: `C:\xampp\htdocs\GestionUniversite`).
2.  **Base de Données :**
    *   Ouvrez PhpMyAdmin (`http://localhost/phpmyadmin/`).
    *   Créez une nouvelle base de données nommée exactement `GestionUniversite` (Interclassement: `utf8mb4_unicode_ci`).
    *   Importez le fichier `database.sql` situé à la racine du projet.
3.  **Accès :**
    *   Ouvrez votre navigateur web : `http://localhost/GestionUniversite/`.
    *   Utilisez le compte administrateur généré par défaut :
        *   **Identifiant :** `admin`
        *   **Mot de passe :** `admin123`

Pour plus de détails, consultez notre [Guide d'Installation](docs/installation_guide.md).

## 📚 Documentation

Vous trouverez l'intégralité de la documentation métier et technique dans le répertoire `/docs/` :

*   [Documentation Technique](docs/technical_documentation.md) (Architecture, structure BDD, paradigmes de sécurité).
*   [Manuel d'Utilisation](docs/user_manual.md) (Guides spécifiques pour chaque rôle utilisateur).
*   [Guide d'Installation](docs/installation_guide.md).

## 🛡️ Sécurité

La sécurité a été placée au centre du développement :
*   Les mots de passe ne sont **jamais** stockés en clair.
*   Le routage PHP vérifie chaque accès et détruit les sessions non autorisées.
*   Toutes les mutations de la base de données sont tracées avec l'adresse IP et l'horodatage.

## 👥 Auteurs

*   Dossier de candidature : Modélisation et Développement Système - Concours Informatique.

---
*Généré avec l'assistance d'Antigravity (Google Deepmind)*
