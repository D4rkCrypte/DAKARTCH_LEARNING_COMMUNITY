# Documentation UML — Plateforme DakarTech-Hack

Ce document présente l'architecture fonctionnelle et structurelle de la plateforme. Les diagrammes respectent les normes universelles **UML 2.x**.

---

## 1. Diagramme de Cas d'Utilisation Global

Ce diagramme présente une vue d'ensemble des interactions entre les différents acteurs et le système.

```mermaid
useCaseDiagram
    actor "Visiteur" as V
    actor "Membre (Hacker)" as M
    actor "Mentor" as Mentor
    actor "Administrateur" as Admin
    actor "Superadmin" as SAdmin

    package "Plateforme DakarTech-Hack" {
        usecase "S'inscrire / Connexion" as UC1
        usecase "Consulter les Actualités" as UC2
        usecase "Parcourir le Forum" as UC3
        usecase "Participer au Chat" as UC4
        usecase "Résoudre un Challenge CTF" as UC5
        usecase "Consulter le Leaderboard" as UC6
        usecase "Accéder au Dashboard" as UC7
        usecase "Publier des News/Ressources" as UC8
        usecase "Gérer les Utilisateurs" as UC9
        usecase "Gérer les Challenges/CTF" as UC10
        usecase "Gérer les Équipes Staff" as UC11
        usecase "Accès Statistiques Globales" as UC12
        usecase "Gérer les Administrateurs" as UC13
    }

    V --> UC1
    V --> UC2
    
    M --|> V
    M --> UC3
    M --> UC4
    M --> UC5
    M --> UC6
    M --> UC7

    Mentor --|> M
    Mentor --> UC8

    Admin --|> Mentor
    Admin --> UC9
    Admin --> UC10
    Admin --> UC11
    Admin --> UC12

    SAdmin --|> Admin
    SAdmin --> UC13
```

> [!NOTE]
> L'utilisation de l'héritage d'acteurs (ex: `Admin --|> Mentor`) simplifie le diagramme en montrant que l'Administrateur possède tous les droits du Mentor et du Membre.

---

## 2. Diagrammes Thématiques (Détails)

### A. Sous-système : Apprentissage & CTF
Ce flux se concentre sur l'aspect compétition et progression.

```mermaid
useCaseDiagram
    actor "Membre" as M
    
    package "Module CTF" {
        usecase "Lister les catégories" as UC_List
        usecase "Ouvrir un challenge" as UC_Open
        usecase "Soumettre un Flag" as UC_Flag
        usecase "Vérifier le Flag" as UC_Check
        usecase "Mettre à jour les points" as UC_Points
        usecase "Consulter les statistiques" as UC_Stats
    }

    M --> UC_List
    M --> UC_Open
    M --> UC_Flag
    M --> UC_Stats

    UC_Flag ..> UC_Check : <<include>>
    UC_Check ..> UC_Points : <<include>>
```

### B. Sous-système : Communauté & Forum
Focus sur les interactions sociales.

```mermaid
useCaseDiagram
    actor "Membre" as M
    
    package "Module Social/Forum" {
        usecase "Consulter les salons" as UC_ChatView
        usecase "Envoyer un message" as UC_Msg
        usecase "Supprimer son message" as UC_Del
        usecase "Modérer les messages" as UC_Mod
    }

    actor "Modérateur/Admin" as Admin

    M --> UC_ChatView
    M --> UC_Msg
    M --> UC_Del
    Admin --> UC_Mod
    UC_Mod ..> UC_Del : <<extend>>
```

---

## 3. Diagramme de Classes Technique

Ce diagramme modélise la structure des données observée via les points de terminaison de l'API et la logique applicative.

```mermaid
classDiagram
    class User {
        -int id
        +string username
        +string email
        -string password
        +string role
        +string avatar_url
        +int total_points
        +int solves_count
        +login() bool
        +updateProfile()
    }

    class Challenge {
        -int id
        +string title
        +string description
        +string category
        +int points
        -string flag
        +string file_url
        +verifyFlag(string attempt) bool
    }

    class News {
        -int id
        +string title
        +string content
        +string image_url
        +datetime created_at
        +publish()
    }

    class TeamMember {
        -int id
        +string pseudo
        +string team_type
        +string role
        +bool is_captain
        +string specialties
        +string bio
    }

    class ForumMessage {
        -int id
        +int user_id
        +string channel
        +string content
        +datetime timestamp
    }

    class Solve {
        -int id
        +int user_id
        +int challenge_id
        +datetime timestamp
    }

    User "1" -- "*" ForumMessage : écrit
    User "1" -- "*" News : publie
    User "1" -- "*" Solve : effectue
    Challenge "1" -- "*" Solve : est résolu par
    TeamMember "0..1" -- "1" User : est associé à
```

---

### 📏 Respect des normes UML 2.x
- **Cas d'Utilisation** : Respect des frontières du système et distinction claire entre `include` (obligatoire) et `extend` (optionnel).
- **Classes** : 
    - **Encapsulation** : Utilisation de `-` pour les données sensibles (password, flag, ids) et `+` pour les données publiques.
    - **Relations** : Utilisation correcte des associations avec multiplicités (`1..*`, `0..1`).
    - **Méthodes** : Inclusion des opérations principales de gestion des flux.

---
*Documentation générée par Antigravity — Standard Universel UML*
