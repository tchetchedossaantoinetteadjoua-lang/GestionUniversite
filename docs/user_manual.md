# Manuel d'Utilisation - Gestion Université

Bienvenue sur la plateforme `Gestion Université`. Ce document est destiné aux différents acteurs de l'application : Administrateur, Enseignant, et Étudiant.

## 1. Connexion au Système
1. Ouvrez l'adresse de votre application locale (ex: `http://localhost/GestionUniversite/`).
2. Saisissez votre **Identifiant** (ou Adresse Email) et votre **Mot de passe**.
3. Le système vous redirigera automatiquement vers le Tableau de Bord correspondant à votre profil.

---

## 2. Guide de l'Administrateur (Super Utilisateur)
Le compte Administrateur gère l'infrastructure complète de l'université.

### 2.1 Structuration de la Faculté
- Allez dans **Académique -> Facultés & Dépts**.
- Créez les Facultés.
- En cliquant sur *Départements*, vous pourrez structurer les départements et les **Filières** associées (L1, L2, M1...).

### 2.2 Gestion des Unités d'Enseignement (UE)
- Allez dans **Matières (UE)**.
- Enregistrez les matières en spécifiant les Crédits (ECTS) et le Coefficient, puis rattachez-les à un Semestre et une Filière.

### 2.3 Gestion des Utilisateurs
- **Enseignants :** Allez dans "Enseignants", créez de nouveaux profils (un mot de passe par défaut est généré). Utilisez le bouton *Attribuer UE* pour assigner de manière ciblée quelles matières le professeur peut noter.
- **Étudiants :** Allez dans "Étudiants" et inscrivez les candidats. Le système génère automatiquement un Matricule (agissant comme identifiant).

### 2.4 Emplois du temps
- Créez les plannings par filière dans **Gestion Avancée -> Emplois du temps**. Cette donnée redescend automatiquement sur les tableaux de bord Etudiant et Enseignant.

---

## 3. Guide de l'Enseignant
Le portail enseignant est strictement délimité à l'activité pédagogique assignée par l'administration.

### 3.1 Consultations de Matières
- Dans **Mes Matières (UE)**, vous visualisez l'ensemble des cours qui vous ont été affectés.
- Vous disposez de raccourcis rapides pour accéder à l'interface de notation.

### 3.2 Saisie des Notes
- Cliquez sur **Saisie des Notes** ou passez par le raccourci.
- Sélectionnez l'UE correspondante. Le système vous fournira la liste intégrale des étudiants rattachés à cette filière.
- Entrez les notes sur 20. En validant, le module recalcule les éléments globaux et journalise l'opération. 

---

## 4. Guide de l'Étudiant
L'espace étudiant permet une consultation rapide et un suivi proactif du cursus académique.

### 4.1 Mes Cours (UE)
- Visualisez les cours liés exclusivement à la filière dans laquelle vous êtes inscrit(e), pour le semestre en cours.

### 4.2 Résultats et Relevés
- L'onglet **Notes & Résultats** présente une interface par Semestre.
- Vous y trouverez l'intégration de vos notes, les coefficients, le calcul dynamique de votre moyenne pondérée, et l'affichage de votre statut conditionnel (Admis, Rattrapage, Ajourné).
- Un bouton *Imprimer Relevé* permet de générer une vue imprimable officielle (PDF).

### 4.3 Mon Emploi du Temps
- Consultez le planning pour anticiper vos cours et l'affectation des salles en direct, filtré par votre filière. 
