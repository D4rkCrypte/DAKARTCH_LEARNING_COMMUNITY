# Guide de Test et de Déploiement Local (MySQL + PHP)

Ce guide explique comment tester l'ensemble du site (Front-End et Back-End) en local.

## 1. Pré-requis

- **XAMPP** (recommandé pour Windows) ou WAMP/MAMP
- **MySQL / MariaDB** (inclus dans XAMPP)
- **PHP 8+** (inclus dans XAMPP)

## 2. Création et Configuration de la Base de Données

1. Lancez **XAMPP Control Panel**.
2. Démarrez les services:
   - **Apache**
   - **MySQL**
3. Ouvrez votre navigateur et allez sur **phpMyAdmin** :
   - `http://localhost/phpmyadmin`
4. Allez dans l'onglet **Importer**.
5. Importez le fichier suivant :
   - `Back-end/database/schema.sql`

> La base de données `dakartech_hack` sera créée automatiquement avec toutes les tables nécessaires (users, contact_messages, forum_topics, news_articles, etc.).

## 3. Configuration de la connexion Back-End

Si nécessaire, vous pouvez ajuster la configuration de la base de données :
- Ouvrez le fichier : `Back-end/config/config.php`
- Modifiez `user` (généralement `root`) et `pass` (généralement vide sous XAMPP) si votre serveur MySQL est configuré différemment.

## 4. Démarrer le Serveur et accéder au site

Il y a deux méthodes pour faire fonctionner le site :

### Option A : Serveur PHP Intégré (Facile)

Si vous voulez juste tester l'API sans configurer XAMPP, ouvrez un terminal et exécutez à la racine du projet BACK-END :
```bash
php -S 127.0.0.1:8000 -t Back-end/public
```
*Note : Le front-end devra être configuré pour pointer vers `http://127.0.0.1:8000/api/...`.*

### Option B : Via Apache XAMPP (Recommandé)

Placez le dossier global du projet à l'intérieur du répertoire `htdocs` de XAMPP.

Exemple : `C:/xampp/htdocs/site-hack/`
Vos fichiers seront donc :
- `C:/xampp/htdocs/site-hack/FRONT-END/`
- `C:/xampp/htdocs/site-hack/BACK-END/`

Les API seront alors accessibles via : `http://localhost/site-hack/BACK-END/public/api/...`
Et le site via : `http://localhost/site-hack/FRONT-END/index.html`

## 5. Comment tester localement (Exemples)

### Test de l'Inscription (Creation de User)

1. Ouvrez XAMPP.
2. Allez sur la page de test API : `http://localhost/site-hack/FRONT-END/test-api.html`
3. Remplissez le formulaire **S'inscrire** avec :
   - Pseudo : `testuser`
   - Email : `test@example.com`
   - Mot de passe : `monmotdepasse`
4. Cliquez sur S'inscrire. Si l'API fonctionne, vous aurez un message de succès (Token généré).

**Vérification en base de données :**
Allez sur phpMyAdmin -> Base `dakartech_hack` -> Table `users`.
Vous verrez la nouvelle ligne avec l'utilisateur que vous venez de créer et son mot de passe hashé.

### Test du Forum

1. Utilisez l'application ou Postman pour faire une requête HTTP **POST** vers `/api/forum/topics`.
2. Données JSON à envoyer :
   ```json
   {
       "title": "Mon Premier Topic",
       "content": "Bonjour le forum !",
       "authorName": "TestUser",
       "authorEmail": "test@example.com"
   }
   ```
3. **Vérification en base de données :** Regardez dans la table `forum_topics`, l'entrée aura été ajoutée.

### Test du Formulaire de Contact

1. Ouvrez `http://localhost/site-hack/FRONT-END/contact.html`.
2. Remplissez et soumettez le formulaire.
3. **Vérification :** Regardez la table `contact_messages` dans phpMyAdmin.

## 6. Mise en Ligne du Back-End

Pour déployer le back-end (ex : Hostinger, LWS, OVH) :
1. Sur votre CPanel, créez une base de données MySQL et importez `schema.sql`.
2. Modifiez `config/config.php` avec les nouveaux identifiants fournis par l'hébergeur.
3. Uploadez tous les fichiers du `BACK-END` (surtout `public`, `src`, `config`) via FTP.
4. Assurez-vous que le dossier `public` soit le dossier accessible publiquement, ou configurez le `# .htaccess` pour rediriger toutes les requêtes HTTP (index.php).
5. Modifiez le code Javascript du `FRONT-END` pour utiliser votre nouvelle URL au lieu de localhost.
