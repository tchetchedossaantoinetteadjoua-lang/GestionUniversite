# Document Technique - Gestion Université

## 1. Architecture du Système
La plateforme est conçue autour du patron architectural **MVC (Modèle-Vue-Contrôleur)** simplifié, utilisant PHP natif. Cette approche offre une séparation logique des responsabilités sans dépendre d'un framework lourd, ce qui facilite le déploiement sur tout serveur LAMP/WAMP (comme XAMPP).

- **Modèles (Database) :** Toutes les interactions DB se font via la classe `Database` (`config/database.php`) utilisant l'extension PDO pour la sécurité.
- **Vues (Views) :** Les fichiers HTML contenant le code PHP de présentation sont organisés par rôle (`views/admin`, `views/teacher`, `views/student`) garantissant l'étanchéité des profils.
- **Contrôleurs (Logique Métier) :** Injectée directement en tête de chaque fichier de vue (traitement POST).

## 2. Base de Données
Le SGBD utilisé est **MySQL**. La base `GestionUniversite` est hautement normalisée :
- **Entités Académiques :** `faculties`, `departments`, `sectors` (Filières associées à un niveau), `semesters`, `courses` (UE).
- **Entités Utilisateurs :** `users` (table centrale avec authentification et rôles : admin, teacher, student), `students` (profil étudiant), `teachers` (profil enseignant).
- **Fonctionnelles :** `enrollments` (inscriptions UE), `grades` (notes et évaluations), `course_teacher` (attribution des matières).
- **Utilitaires :** `system_logs` (journalisation des actions), `timetables` (emplois du temps).

## 3. Mécanismes de Sécurité
- **Authentification & Hachage :** Mots de passe chiffrés avec `password_hash()` (algorithme BCRYPT natif PHP).
- **Protection CSRF :** Utilisation de jetons aléatoires (`generateCsrfToken()`) stockés en session et vérifiés à la soumission des formulaires critiques.
- **Injections SQL :** Utilisation exclusive de Requêtes Préparées (Prepared Statements) `PDO::prepare()`.
- **Failles XSS :** Toutes les données affichées aux utilisateurs sont échappées via `htmlspecialchars()`.
- **Contrôle d'accès (RBAC) :** Vérification stricte via le helper `requireRole('role')` au chargement de chaque page.

## 4. Fonctionnalités Implémentées
### Processus de notes
- L'administrateur crée le catalogue d'UEs incluant **Crédits** et **Coefficients**.
- L'enseignant attribue des notes (/20).
- Le calcul de moyenne (dans `results.php` ou `print_bulletin.php`) pondère automatiquement chaque note par le coefficient de l'UE pour déterminer le statut selon les règles LMD (Admis >= 10, Rattrapage >= 8, Ajourné < 8).

### Interfaces Responsive
- Les interfaces reposent sur du CSS natif optimisé (Flexbox, Grid et Variables CSS) garantissant un affichage optimal sur ordinateur fixe ou mobile.
- L'état de l'interface (Flash messages, Modales en surcouche) est géré sans surcharger, avec un usage minime de JavaScript orienté Document Object Model.

## 5. Extensibilité
Pour étendre le projet, il suffit d'ajouter :
1. De nouvelles déclarations SQL dans `database.sql` (ou direct via PhpMyAdmin).
2. Un nouveau fichier dans `views/admin/`.
3. Lier ce fichier via le menu `includes/sidebar_admin.php`.
Aucune phase de build n'est requise.
