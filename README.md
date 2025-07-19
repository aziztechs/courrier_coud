# Système de Gestion de Courrier - COUD

## Description

Ce projet est une application web de gestion de courrier développée pour le Campus Universitaire de Cheikh Anta DIOP (COUD). Elle permet :

- L'enregistrement et le suivi des courriers entrants et sortants
- L'imputation des courriers aux différents départements
- Le suivi des traitements par les assistants
- La génération de statistiques sur le flux de courrier

## Technologies utilisées

- **Frontend** : HTML5, CSS3, Bootstrap 5, JavaScript
- **Backend** : PHP 8.2
- **Base de données** : MYSQL 8+
- **Serveur** : Apache 2.4

## Installation

Pour installer et exécuter ce projet localement, suivez les étapes ci-dessous :

1. **Prérequis** :
   - Serveur web (Apache/Nginx)
   - PHP 8.2+
   - MariaDB 10.4+ ou MySQL 8.0+
   - Composer (pour la gestion des dépendances)

2. **Cloner le dépôt** :
   ```bash
   git clone https://github.com/votre-repo/courrier_coud.git
   cd courrier_coud
   ```

3. **Configurer le fichier de configuration** :
   Copiez le fichier d'exemple et modifiez-le selon vos paramètres :
   ```bash
   cp config.sample.php config.php
   ```
   Ouvrez `config.php` et renseignez vos informations de connexion à la base de données.

4. **Installer les dépendances PHP** :
   ```bash
   composer install
   ```

5. **Créer la base de données** :
   - Importez le fichier `database.sql` dans votre serveur MySQL/MariaDB pour créer les tables nécessaires.

6. **Lancer le serveur** :
   - Placez le dossier du projet dans le répertoire `htdocs` de XAMPP ou configurez votre serveur web pour pointer vers le dossier.
   - Accédez à l'application via `http://localhost/courrier_coud` dans votre navigateur.
   - Serveur web (Apache/Nginx)
   - PHP 8.2+
   - MariaDB 10.4+ ou MySQL 8.0+
   - Composer (pour les dépendances)

2. **Configuration** :
   ```bash
   git clone https://github.com/votre-repo/courrier_coud.git
   cd courrier_coud
   cp config.sample.php config.php

   7. **Déploiement avec un script**

      Vous pouvez créer un script de déploiement pour automatiser l'installation. Par exemple, créez un fichier `deploy.sh` à la racine du projet :

      ```bash
      #!/bin/bash
      echo "Installation des dépendances PHP..."
      composer install

      echo "Copie du fichier de configuration..."
      cp -n config.sample.php config.php

      echo "Import de la base de données..."
      mysql -u <utilisateur> -p <base_de_donnees> < database.sql

      echo "Déploiement terminé. Accédez à http://localhost/courrier_coud"
      ```

      > **Remarque :** Modifiez `<utilisateur>` et `<base_de_donnees>` selon votre configuration. Rendez le script exécutable avec :  
      > `chmod +x deploy.sh`
