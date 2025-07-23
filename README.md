# RemboursePRO - Plateforme de Gestion des Remboursements

## 🚀 Description
RemboursePRO est une plateforme professionnelle de gestion des remboursements développée en PHP natif. Elle offre un service sécurisé, rapide et transparent pour les clients, avec un tableau de bord d'administration complet.

## ✨ Fonctionnalités Principales

### Pour les Clients
- 📝 Inscription et connexion sécurisées
- 👤 Gestion du profil avec upload de photo
- 💳 Soumission de demandes de remboursement
- 📊 Calculateur avancé avec graphiques
- 📋 Historique détaillé des remboursements
- 📧 Notifications par email

### Pour les Administrateurs
- 📈 Dashboard avec statistiques complètes
- ⚡ Gestion des remboursements (validation/refus)
- 👥 Gestion des utilisateurs et rôles
- ⚙️ Configuration des paramètres du site
- 📊 Graphiques et analyses avancées

## 🛠️ Technologies Utilisées
- **Backend**: PHP 7.4+ (natif, sans framework)
- **Base de données**: MySQL
- **Frontend**: HTML5, CSS3, JavaScript
- **Framework CSS**: Bootstrap 5.3.3
- **Animations**: AOS (Animate On Scroll)
- **Graphiques**: Chart.js
- **Icônes**: Bootstrap Icons

## 📋 Prérequis
- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur
- Serveur web (Apache/Nginx)
- Extension PHP: PDO, GD (pour les images)

## 🚀 Installation

### 1. Configuration de la base de données
1. Connectez-vous à phpMyAdmin sur Hostinger
2. Exécutez le script `database_setup.sql`
3. Modifiez les paramètres dans `config/database.php`:

```php
private $host = 'localhost'; // Votre host Hostinger
private $db_name = 'votre_nom_de_bdd';
private $username = 'votre_utilisateur';
private $password = 'votre_mot_de_passe';
```

### 2. Configuration des permissions
```bash
chmod 755 uploads/
chmod 755 uploads/profiles/
```

### 3. Configuration email (optionnel)
Modifiez la fonction `sendEmail()` dans `config/auth.php` selon votre configuration SMTP.

## 🔐 Comptes de Test

### Administrateur
- **Email**: admin@remboursepro.com
- **Mot de passe**: admin123

### Client
- **Email**: client@test.com
- **Mot de passe**: client123

## 📁 Structure du Projet

```
remboursepro/
├── config/
│   ├── database.php          # Configuration BDD
│   └── auth.php              # Authentification et sécurité
├── assets/
│   └── css/
│       └── style.css         # Styles personnalisés
├── uploads/
│   └── profiles/             # Photos de profil
├── client/                   # Pages client
│   ├── dashboard.php
│   ├── profil.php
│   ├── remboursement.php
│   ├── historique.php
│   └── calculateur.php
├── admin/                    # Pages admin
│   ├── dashboard.php
│   ├── remboursements.php
│   ├── utilisateurs.php
│   └── settings.php
├── index.php                 # Page d'accueil
├── login.php                 # Connexion
├── register.php              # Inscription
├── logout.php                # Déconnexion
└── database_setup.sql        # Script de création BDD
```

## 🎨 Design
- **Style**: Glassmorphism moderne
- **Couleurs**: Palette bleu/violet harmonieuse
- **Responsive**: Compatible mobile, tablette, desktop
- **Animations**: Effets AOS fluides
- **UX**: Interface intuitive et professionnelle

## 🔒 Sécurité
- ✅ Hachage des mots de passe (password_hash)
- ✅ Protection CSRF
- ✅ Validation des entrées (XSS)
- ✅ Sessions sécurisées
- ✅ Upload de fichiers sécurisé
- ✅ Contrôle d'accès par rôles

## 📧 Fonctionnalités Email
- Confirmation de demande de remboursement
- Notification de changement de statut
- Support technique

## 🚀 Déploiement sur Hostinger

1. **Upload des fichiers** via FTP ou File Manager
2. **Configuration de la base de données** dans phpMyAdmin
3. **Permissions des dossiers** uploads/
4. **Test des fonctionnalités** avec les comptes de test

## 🔧 Maintenance

### Sauvegarde
- Sauvegardez régulièrement la base de données
- Sauvegardez le dossier uploads/

### Mise à jour
- Vérifiez les logs d'erreur PHP
- Surveillez l'espace disque
- Mettez à jour les dépendances si nécessaire

## 📞 Support
- **Email**: contact@remboursepro.com
- **Documentation**: Consultez les commentaires dans le code
- **Issues**: Contactez l'équipe de développement

## 📄 Licence
Projet propriétaire - Tous droits réservés

---

**RemboursePRO** - Plateforme professionnelle de gestion des remboursements