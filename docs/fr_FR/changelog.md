# Changelog plugin EcoLegrand

# 01/01/2026

- Modifications pour convertir les valeurs récupérées de l'écocompteur en float et éviter les erreurs lors de l'addition en PHP8 (merci à Michel_F)
- Déplacement de la documentation dans un repository github séparé afin de pouvoir mettre à jour la documentation sans générer un update du plugin

# 28/03/2025

- Récupération des champs non numériques

# 07/11/2024

- Passage des methodes cron en static pour éviter erreur en PHP 8
- Amélioration documentation

# 25/02/2024

- Mise à jour de la documentation

# 21/12/2023

- Message d'erreur dans la log si on ne peut pas décoder le JSON renvoyé par l'écocompteur (voir FAQ)
  
# 24/08/2023

- Correction bug lors de la création des commandes

# 14/08/2023

- Ajout de log sur création compteur

# 05/08/2023

- Initial load
