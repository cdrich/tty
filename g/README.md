# Nom du Projet

Description brève du projet.

## Installation

### Prérequis

-   [Composer](https://getcomposer.org/)
-   [PHP](https://www.php.net/) (version 7.4 ou supérieure recommandée)
-   [Node.js](https://nodejs.org/) (pour les dépendances JavaScript)

### Étapes d'installation

```bash
# 1. Clonez le dépôt
git clone https://gitlab.com/waouhmonde/webcoompay.git

# 2. Accédez au répertoire du projet
cd webcoompay

# 3. Installez les dépendances PHP via Composer
composer install

# 4. Copiez le fichier .env.example pour créer un fichier .env
cp .env.example .env

# 5. Générez une clé d'application
php artisan key:generate

# 6. Configurez votre base de données dans le fichier .env.

# 7. Exécutez les migrations pour créer les tables de base de données
php artisan migrate

# 8. Installez les dépendances JavaScript
npm install

# 9. Compilez les ressources JavaScript et CSS
npm run dev

# 10. Lancez le serveur de développement
php artisan serve

# 10. Lancez le serveur de développement

docker compose ps

#Fermer tous les containeurs
docker stop $(docker ps -q)

# Voir les images docker sur le serveur
docker images

# Effacer les images generer
docker rmi [IMAGE_ID_OR_NAME]

# Effacer les images non utilisé
docker image prune -a

#lancer le container
docker compose up -d
docker compose down

```

### Le projet est désormais accessible à l'adresse http://localhost:8000.

```

```
