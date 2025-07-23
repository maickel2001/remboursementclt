# 🚀 Guide de Déploiement Hostinger - RemboursePRO

## ⚠️ DIAGNOSTIC RAPIDE

### 1. **Test Immédiat**
Visitez d'abord : `votre-domaine.com/simple_test.php`
Ce fichier va diagnostiquer tous les problèmes en 30 secondes.

### 2. **Configuration Base de Données**
Vos identifiants sont déjà configurés :
- **Host**: localhost
- **Database**: u634930929_ktloee
- **Username**: u634930929_ktloee  
- **Password**: Ino1234@

### 3. **Étapes de Résolution**

#### A. Exécuter le Script SQL
1. Connectez-vous à **phpMyAdmin** sur Hostinger
2. Sélectionnez la base `u634930929_ktloee`
3. Cliquez sur **Importer**
4. Sélectionnez `database_hostinger.sql`
5. Cliquez **Exécuter**

#### B. Créer les Dossiers
Via le gestionnaire de fichiers Hostinger :
- Créez `uploads/` (permissions 755)
- Créez `uploads/profiles/` (permissions 755)  
- Créez `logs/` (permissions 755)

#### C. Tester
1. Visitez `simple_test.php` - tout doit être vert ✅
2. Si OK, testez `index.php`
3. Connectez-vous avec les comptes de test

## 🎯 **COMPTES DE TEST**

### 👤 **CLIENT**
- **Email**: client@test.com
- **Mot de passe**: client123

### 🔧 **ADMIN**  
- **Email**: admin@remboursepro.com
- **Mot de passe**: admin123

## 🔧 **Problèmes Courants**

### Page Blanche
- Vérifiez `simple_test.php` pour identifier l'erreur exacte
- Activez l'affichage des erreurs PHP dans Hostinger
- Vérifiez les logs d'erreur dans le panneau Hostinger

### Erreur BDD
- Vérifiez que le script SQL a été exécuté
- Vérifiez les identifiants de connexion
- Assurez-vous que la base existe

### Erreur 500
- Vérifiez les permissions des fichiers (644 pour PHP, 755 pour dossiers)
- Vérifiez la syntaxe PHP avec `simple_test.php`

## 📞 **Support**
Le fichier `simple_test.php` vous donnera un diagnostic complet et précis du problème !