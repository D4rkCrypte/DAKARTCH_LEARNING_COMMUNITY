# DakarTech-Hack - Community Learning & CTF Platform

![DakarTech-Hack Banner](banner.png)

## Présentation du Projet
**DakarTech-Hack** est une plateforme communautaire dédiée à l'apprentissage de la cybersécurité et à la pratique via des challenges **CTF (Capture The Flag)**. Conçu comme un projet de fin de semestre, ce système complet permet aux passionnés de s'entraîner, de partager des connaissances et de se mesurer aux autres dans un environnement sécurisé et compétitif.

L'objectif est de dynamiser l'élite de la cybersécurité au Sénégal en offrant des ressources de formation, un forum de discussion et une arène de compétition technique.

---

## Fonctionnalités Principales

### Design & Expérience Utilisateur (UI/UX)
- **Refonte Premium** : Mise en œuvre d'un style "Glassmorphism" avec des effets de flou et de transparence avancés.
- **Optimisation de Densité (Scaling)** : Réduction systématique de 20-25% de tous les éléments pour une interface plus dense, professionnelle et adaptée aux écrans haute résolution.
- **Hero Section Dynamique** : Nouvelle mise en page split-screen avec imagerie cinématique et typographie réactive.
- **Micro-interactions** : Animations de particules au survol et transitions fluides entre les pages via `nav-helper.js`.

## Structure du Projet
```
.
├── BACK-END/
│   ├── public/         # Point d'entrée de l'API (index.php)
│   ├── src/            # Logique métier (Auth, DB, HTTP)
│   ├── database/       # Scripts SQL et modèles de données
│   └── config/         # Configuration du serveur
├── FRONT-END/
│   ├── index.html      # Accueil du site (Nouveau Split-Hero)
│   ├── ctfs.html       # Arène de compétition
│   ├── forum.html      # Espace communautaire
│   ├── style.css       # Design global (20% Scaled Refactor)
│   ├── assets/         # Images et ressources visuelles
│   └── ...             # Autres pages applicatives
└── README.md           # Documentation générale du projet
```

---

## Installation & Configuration

### Prérequis
- Un serveur web (Apache/Nginx) avec **PHP 8.0** ou supérieur.
- Un serveur de base de données **MySQL**.

### Étapes d'installation
1. **Clonage du dépôt** :
   ```bash
   git clone https://github.com/votre-compte/DakarTech-Hack.git
   ```
2. **Configuration de la Base de Données** :
   - Créez une base de données MySQL.
   - Importez le fichier `BACK-END/database/schema.sql`.
   - Modifiez les accès dans `BACK-END/src/db.php`.
3. **Initialisation** :
   - Exécutez le script `BACK-END/init_db.php` pour peupler les données initiales.
4. **Lancement** :
   - Configurez votre serveur web pour pointer sur le dossier racine ou utilisez le serveur de développement PHP.

---

## Équipe & Crédits
Ce projet a été réalisé par **Deo** dans le cadre de l'examen de fin de semestre. 

- **Concept & Design** : Deo
- **Développement Backend** : Deo
- **Développement Frontend** : Deo

---

## Licence
Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.
