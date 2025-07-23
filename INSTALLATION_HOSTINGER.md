# 🚀 Installation RemboursePRO sur Hostinger

## 📋 Étapes d'Installation

### 1. **Upload des Fichiers**
- Uploadez tous les fichiers dans le dossier `public_html` de votre hébergement Hostinger
- Assurez-vous que tous les dossiers et fichiers sont bien transférés

### 2. **Configuration de la Base de Données**

#### A. Créer la Base de Données
1. Connectez-vous à votre panneau Hostinger
2. Allez dans **Bases de données MySQL**
3. Créez une nouvelle base de données (ex: `remboursepro`)
4. Notez le nom d'utilisateur et mot de passe générés

#### B. Configurer la Connexion
Modifiez le fichier `config/database.php` :

```php
private $host = 'localhost';
private $db_name = 'votre_nom_de_bdd'; // Remplacez par votre nom de BDD
private $username = 'votre_utilisateur'; // Remplacez par votre utilisateur
private $password = 'votre_mot_de_passe'; // Remplacez par votre mot de passe
```

#### C. Importer la Structure
1. Ouvrez **phpMyAdmin** depuis votre panneau Hostinger
2. Sélectionnez votre base de données
3. Cliquez sur **Importer**
4. Sélectionnez le fichier `database_hostinger.sql`
5. Cliquez sur **Exécuter**

### 3. **Créer les Dossiers Nécessaires**
Via le gestionnaire de fichiers Hostinger, créez :
- `uploads/` (permissions 755)
- `uploads/profiles/` (permissions 755)
- `logs/` (permissions 755)

### 4. **Test de l'Installation**
1. Visitez `votre-domaine.com/debug.php` pour diagnostiquer
2. Visitez `votre-domaine.com/test_connection.php` pour tester la connexion
3. Si tout est vert, supprimez ces fichiers de test

### 5. **Comptes de Test**

#### 👤 **CLIENT**
- Email: `client@test.com`
- Mot de passe: `client123`

#### 🔧 **ADMIN**
- Email: `admin@remboursepro.com`
- Mot de passe: `admin123`

## 🔧 Résolution des Problèmes

### Page Blanche
- Vérifiez les erreurs PHP dans les logs Hostinger
- Assurez-vous que `config/database.php` est correctement configuré
- Vérifiez que la base de données est créée et accessible

### Erreur de Connexion BDD
- Vérifiez les identifiants dans `config/database.php`
- Assurez-vous que la base de données existe
- Vérifiez que l'utilisateur a les permissions sur la BDD

### Problème d'Upload
- Vérifiez que le dossier `uploads/profiles/` existe
- Vérifiez les permissions (755)
- Vérifiez la limite d'upload PHP dans Hostinger

## 📞 Support
Si vous rencontrez des problèmes, vérifiez d'abord avec les fichiers de debug fournis.