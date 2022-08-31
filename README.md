# 2022-SFA-CAFÉ-API
API de l'application Pause Café

## Définition
L'application Pause Café développé en Laravel/Vue.js est une application permettant de gérer la machine à café de la salle des profs dans l'atelier informatique. Cette dernière permet à un utilisateur (Professeur) d'ajouter un café à son solde de café, il pourra également ajouté des dépenses personnelles (tels que la crème ou le sucre). Ces informations seront utilisés ultérieurement pour effectuer les comptes de chacun. 


## Base de donnée
L'application contiendra une base de donnée afin de permettre aux utilisateurs d'avoir chacun leur propre compte ainsi que leur propre solde.

### Tables
L'application contiendra différentes tables.
* Table utilisateur
* Table achat
* Table versement


## Backend
Le Backend sera développé avec Laravel, il contiendra la gestion des utilisateurs, l'authentification, la création et la maintenance de la base de donnée et les informations à remonter au Frontend.


## Frontend
Le Frontend sera développé avec Vue.js et Quasar afin que le développement puisse être déployer en tant que site web ou application. Le Frontend contiendra 3 écrans principales : l'écran de connexion, l'écran principal (achat de café ou achat spécifique) et l'écran contenant le formulaire contenant les achats spécifiques.
