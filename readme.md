# Projet de fin d’étude

    Nom du projet : Alea-food
    Nom de l’étudiant : Eric Closquet
    Groupe : 2384
    Année : 2017-2018
    
    
## Pourquoi ?

J’ai remarqué que régulièrement, dans mon enfance mes parents se posaient chaque soir la même question, « qu’est-ce qu’on mange ? ». 

Et depuis que je suis en ménage cela se produit quand le soir arrive, ma femme et moi nous posons la même question avec des réponse du genre « ah non j’ai demandé avant » ou encore « j’ai choisi hier, à ton tour ».

J’ai demandé à plusieurs personnes de mon entourage et apparemment nous ne sommes pas un cas isolé.


## Scénarios

### Bob Dupont :
Bob rentre après une journée de travail et est fatigué, il ne sait pas quoi faire pour le souper.

Il est tard et il n’a pas envie d’aller au magasin pour acheter de nouveaux ingrédients, donc il lance l’application sur son smartphone et ajoute les ingrédients qu’il a dans son frigo en critère de recherche.

Le résultat ne lui plait pas et il relance la recherche pour enfin obtenir la recette qui lui convient.


### Sophie Tartare :
Sophie compte préparer le repas quand soudain son frère et sa petite famille arrivent à l’improviste. Son frère est venu de loin pour lui faire la surprise donc Sophie lui propose de manger avec eux.

N’ayant rien prévu, ce qu’elle comptait cuisiner ne suffira pas pour ces personnes supplémentaires.

Du coup elle utilise l’application et choisi en critère de « nombre de personnes » « 7 », son frère étant intolérant au gluten, elle le précise dans le critère « allergies supplémentaires » « gluten » et enfin pour marquer le coup elle précise le thème « cuisine chinoise ».

Elle obtient une recette et se rend au magasin pour acheter les aliments manquants.

À la fin du repas elle demande si tout le monde a aimé et obtient un grand oui, satisfaite de cette recette, elle reprend son application et met un 4/5 et commente la recette.


## Fonctionnalités souhaitées :
-  Afficher une 1 recette aléatoire selon des critères:
    -  Critères de profile
        -  Aliments déprécies.
        -  Allergies.
    -  Critères ponctuels lors d’une recherche
        -  Nombre de personnes.
        -  Allergies supplémentaires
        -  Aliments déprécies supplémentaires.
        -  Aliment devant être présent (Dispo dans le frigo)
        -  Type de plat (repas, dessert).
        -  Origine (Cuisine française, chinoise…)
-  Partager une recette sur les réseaux sociaux.
-  Afficher les 5 recettes les mieux notées.
-  Créer un compte online permet :
    -  D’enregistrer les critères du profil en ligne.
    -  D’avoir un historique des recettes consultées.
    -  De noter les recettes de 0 à 5
    -  De commenter une recette
    -  De publier ses propres recettes (participatif).
    -  Signaler une recette d’utilisateur.
-  Modération et administration via appli Vue.js (web).


## Technique :
- Front : Vue.JS
- Back : Laravel