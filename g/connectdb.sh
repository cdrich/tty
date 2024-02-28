#!/bin/sh

# Informations de connexion à la base de données
DB_HOST=127.0.0.1
DB_USER="root"
# DB_PASS=""
DB_NAME="WebcoomApi"
TABLE_NAME="Users"

# Commande SQL pour afficher le contenu de la table
SQL_QUERY="SELECT * FROM $TABLE_NAME;"

# Connexion à la base de données et exécution de la requête
mysql -h "$DB_HOST" -u "$DB_USER"  -D "$DB_NAME" -e "$SQL_QUERY"