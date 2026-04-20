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

---

## Documentation Technique 📚

Le projet inclut désormais une documentation UML complète et académique.

### 📊 Diagramme de Cas d'Utilisation (Global)
![Global Use Case](https://mermaid.ink/img/dXNlQ2FzZURpYWdyYW0KICAgIGFjdG9yICJWaXNpdGV1ciIgYXMgVgogICAgYWN0b3IgIk1lbWJyZSAoSGFja2VyKSIgYXMgTQogICAgYWN0b3IgIk1lbnRvciIgYXMgTWVudG9yCiAgICBhY3RvciAiQWRtaW5pc3RyYXRldXIiIGFzIEFkbWluCiAgICBhY3RvciAiU3VwZXJhZG1pbiIgYXMgU0FkbWluCgogICAgcGFja2FnZSAiUGxhdGVmb3JtZSBEYWthclRlY2gtSGFjayIgewogICAgICAgIHVzZWNhc2UgIlMnaW5zY3JpcmUgLyBDb25uZXhpb24iIGFzIFVDMQogICAgICAgIHVzZWNhc2UgIkNvbnN1bHRlciBsZXMgQWN0dWFsaXTDqXMiIGFzIFVDMgogICAgICAgIHVzZWNhc2UgIlBhcmNvdXJpciBsZSBGb3J1bSIgYXMgVUMzCiAgICAgICAgdXNlY2FzZSAiUGFydGljaXBlciBhdSBDaGF0IiBhcyBVQzQKICAgICAgICB1c2VjYXNlICJSw6lzb3VkcmUgdW4gQ2hhbGxlbmdlIENURiIgYXMgVUM1CiAgICAgICAgdXNlY2FzZSAiQ29uc3VsdGVyIGxlIExlYWRlcmJvYXJkIiBhcyBVQzYKICAgICAgICB1c2VjYXNlICJBY2PDqGRlciBhdSBEYXNoYm9hcmQiIGFzIFVDNwogICAgICAgIHVzZWNhc2UgIlB1YmxpZXIgZGVzIE5ld3MvUmVzc291cmNlcyIgYXMgVUM4CiAgICAgICAgdXNlY2FzZSAiR8OpcmVyIGxlcyBVdGlsaXNhdGV1cnMiIGFzIFVDOQogICAgICAgIHVzZWNhc2UgIkfDqXJlciBsZXMgQ2hhbGxlbmdlcy9DVEYiIGFzIFVDMTAKICAgICAgICB1c2VjYXNlICJHw6lyZXIgbGVzIMOIcXVpcGVzIFN0YWZmIiBhcyBVQzExCiAgICAgICAgdXNlY2FzZSAiQWNjZXMgbm8gU3RhdGlzdGlxdWVzIEdsb2JhbGVzIiBhcyBVQzEyCiAgICAgICAgdXNlY2FzZSAiR8OpcmVyIGxlcyBBZG1pbmlzdHJhdGV1cnMiIGFzIFVDMTMKICAgIH0KCiAgICBWIC0tPiBVQzEKICAgIFYgLS0+IFVDMgogICAgCiAgICBNIC0t|>IFYKICAgIE0gLS0+IFVDMwogICAgTSAtLT4gVUM0CiAgICBNIC0tPiBVQzUKICAgIE0gLS0+IFVDNgogICAgTSAtLT4gVUM3CgogICAgTWVudG9yIC0t|>IE0KICAgIE1lbnRvciAtLT4gVUM4CgogICAgQWRtaW4gLS0|>IE1lbnRvcgogICAgQWRtaW4gLS0+IFVDOQogICAgQWRtaW4gLS0+IFVDMTAKICAgIEFkbWluIC0tPiBVQzExCiAgICBBZG1pbiAtLT4gVUMxMgoKICAgIFNBZG1pbiAtLT|>IEFkbWluCiAgICBTQWRtaW4gLS0+IFVDMTMK)

### 📐 Diagramme de Classes
![Class Diagram](https://mermaid.ink/img/Y2xhc3NEaWFncmFtCiAgICBjbGFzcyBVc2VyIHsKICAgICAgICAtaW50IGlkCiAgICAgICAgK3N0cmluZyB1c2VybmFtZQogICAgICAgICtzdHJpbmcgZW1haWwKICAgICAgICAtc3RyaW5nIHBhc3N3b3JkCiAgICAgICAgK3N0cmluZyByb2xlCiAgICAgICAgK3N0cmluZyBhdmF0YXJfdXJsCiAgICAgICAgK2ludCB0b3RhbF9wb2ludHMKICAgICAgICAraW50IHNvbHZlc19jb3VudAogICAgICAgICtsb2dpbigpIGJvb2wKICAgICAgICArdXBkYXRlUHJvZmlsZSgpCiAgICB9CgogICAgY2xhc3MgQ2hhbGxlbmdlIHsKICAgICAgICAtaW50IGlkCiAgICAgICAgK3N0cmluZyB0aXRsZQogICAgICAgICtzdHJpZ2UgZGVzY3JpcHRpb24KICAgICAgICArc3RyaW5nIGNhdGVnb3J5CiAgICAgICAgK2ludCBwb2ludHMKICAgICAgICAtc3RyaW5nIGZsYWcKICAgICAgICArc3RyaW5nIGZpbGVfdXJsCiAgICAgICAgK3ZlcmlmeUZsYWcoc3RyaW5nIGF0dGVtcHQpIGJvb2wKICAgIH0KCiAgICBjbGFzcyBOZXdzIHsKICAgICAgICAtaW50IGlkCiAgICAgICAgK3N0cmluZyB0aXRsZQogICAgICAgICtzdHJpbmcgY29udGVudAogICAgICAgICtzdHJpbmcgaW1hZ2VfdXJsCiAgICAgICAgK2RhdGV0aW1lIGNyZWF0ZWRfYXQKICAgICAgICArcHVibGlzaCgpCiAgICB9CgogICAgY2xhc3MgVGVhbU1lbWJlciB7CiAgICAgICAgLWludCBpZAogICAgICAgICtzdHJpbmcgcHNldWRvCiAgICAgICAgK3N0cmluZyB0ZWFtX3R5cGUKICAgICAgICArc3RyaW5nIHJvbGUKICAgICAgICArYm9vbCBpc19jYXB0YWluCiAgICAgICAgK3N0cmluZyBzcGVjaWFsdGllcwogICAgICAgICtzdHJpbmcgYmlvCiAgICB9CgogICAgY2xhc3MgRm9ydW1NZXNzYWdlIHsKICAgICAgICAtaW50IGlkCiAgICAgICAgK2ludCB1c2VyX2lkCiAgICAgICAgK3N0cmluZyBjaGFubmVsCiAgICAgICAgK3N0cmluZyBjb250ZW50CiAgICAgICAgK2RhdGV0aW1lIHRpbWVzdGFtcAogICAgfQoKICAgIGNsYXNzIFNvbHZlIHsKICAgICAgICAtaW50IGlkCiAgICAgICAgK2ludCB1c2VyX2lkCiAgICAgICAgK2ludCBjaGFsbGVuZ2VfaWQKICAgIC2RhdGV0aW1lIHRpbWVzdGFtcAogICAgfQoKICAgIFVzZXIgIjEiIC0tICIqIiBGb3J1bU1lc3NhZ2UgOiDDqWNyaXQKICAgIFVzZXIgIjEiIC0tICIqIiBOZXdzIDogcHVibGllCiAgICBVc2VyICIxIiAtLSAiKiIgU29sdmUgOiBlZmZlY3R1ZQogICAgQ2hhbGxlbmdlICIxIiAtLSAiKiIgU29sdmUgOiBlc3QgcsOpc29sdSBwYXIKICAgIFRlYW1NZW1iZXIgIjAuLjEiIC0tICIxIiBVc2VyIDogZXN0IGFzc29jacOpIMOgKQoK- **[UML_DOCUMENTATION.md](UML_DOCUMENTATION.md)** : Contient les diagrammes déraillés (Cas d'Utilisation thématiques et Classes), tous conformes aux normes **UML 2.x**.

---

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
├── UML_DOCUMENTATION.md # Documentation UML 2.x Académique
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
