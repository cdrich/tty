#!/bin/bash

# Récupérer l'URL du dépôt GitLab
gitlab_repo_url="https://gitlab.com/Kaduce/T-ESP-900-esp901-41115-Solus-3.git"
git@gitlab.com:waouhmonde/webcoompay.git
# Récupérer le nom de la branche actuelle
current_branch=$(git rev-parse --abbrev-ref HEAD)

# Supprimer tous les remotes existants
git remote | xargs -n1 git remote remove

# Ajouter le dépôt GitLab en tant que remote
git remote add gitlab "$gitlab_repo_url"

# Configurer le miroir pour les poussées (push) vers GitLab
git remote set-url --push gitlab "$gitlab_repo_url"

# Configurer le miroir pour les poussées (push) vers GitHub (avec URL SSH)
git remote add github git@github.com:EpitechMscProPromo2024/T-ESP-900-esp901-41115-Solus-3.git

# Ajouter le remote "origin" pour GitHub
git remote add origin git@gitlab.com:waouhmonde/webcoompay.git

# Configurer le miroir pour les poussées (push) vers GitLab
git remote set-url --add --push origin "$gitlab_repo_url"

# Configurer push.default pour pousser vers le remote correspondant à la branche actuelle
git config push.default current

# Vérifier si la branche existe déjà sur GitHub
if git rev-parse --verify github/main >/dev/null 2>&1; then
    echo "La branche github/main existe déjà sur GitHub."
else
    # Créer la branche sur GitHub
    git push github HEAD:main
fi

# Demander le message de commit à l'utilisateur
read -p "Entrez le message de commit : " commit_message

# Effectuer le commit et le push vers GitHub et GitLab
git add .
git commit -m "$commit_message"
git push github HEAD
git push gitlab "$current_branch"
