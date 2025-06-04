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

1. **Prérequis** :
   - Serveur web (Apache/Nginx)
   - PHP 8.2+
   - MariaDB 10.4+ ou MySQL 8.0+
   - Composer (pour les dépendances)

2. **Configuration** :
   ```bash
   git clone https://github.com/votre-repo/courrier_coud.git
   cd courrier_coud
   cp config.sample.php config.php
