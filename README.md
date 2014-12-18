mahara_booklet
==============
Mahara 10.1 version - This is a fork by Jean Fruitet <Jean.Fruitet@univ-nantes.fr>

of the **Mahara Artefact Booklet** of Christophe Declercq (http://moodlemoot2014.univ-paris3.fr/course/view.php?id=228) 

 
### Modifications by JF - December 2014

#### Show / Edit 

Version 1.0.3 - 2014-12-18 : L'utilisateur peut désormais alterner l'affichage et l'édition du livret.

#### Bug correction

Version 1.0.3 - 2014-12-13 : La suppression d'un "tome" (Livret) est corrigée.

#### Copyright support

Support de la licence CC By-Nd (http://creativecommons.org/licenses/by-nd/4.0/)
pour le modèle de données Booklet (l'auteur peut interdire toute modification de la structure du Livret qu'il a créé).

Creative Commons Licence  "By-Nd" (http://creativecommons.org/licenses/by-nd/4.0/) is implemented in Booklet data model.
So an author may forbid the data structure modification of any of his booklet model.

#### Export / Import author informations and booklet status

Le statut du livret (modification autorisée ou pas) ainsi que le nom de l'auteur et les informations de copyright sont enregistrés, importés, exportés.

Booklet status (modification yes / no) and author information and copyright stuff is imported / exported.  

#### Frame list display

Quand un cadre de type liste a plus de 5 items l'affichage vertical est sélectionné, sinon c'est l'affichahe horizontal en tableau qui est  maintenu.

When a frame list contains more than 5 items, vertical display is selected; 

## Booklet French presentation

By Christophe Declercq <christophe.declercq@univ-nantes.fr>

*	« Livret universel de compétences » : un méta-artefact pour Mahara

*	**MoodleMoot / MaharaMoot 2013** : http://moodlemoot2013.univ-bordeaux.fr/course/view.php?id=55

*	**MoodleMoot / MaharaMoot 2014** : http://moodlemoot2014.univ-paris3.fr/course/view.php?id=228

*	Auteur(s) : Christophe Declercq <christophe.declercq@univ-nantes.fr>

*	Mots-clés : mahara, eportfolio, artefact, curriculum-vitae

### Résumé
La définition statique des champs dans le CV de Mahara, pour exprimer les connaissances et les compétences, 
a amené de nombreux établissements à définir des artefacts ad'hoc pour adapter l'outil à leur démarche particulière 
de valorisation des compétences des étudiants.

Pour pallier à cette difficulté et tenter de gagner en généralité, nous avons développé un nouvel artefact 
pour Mahara, permettant d'abord à des concepteurs de définir un nouveau format de formulaires de saisie, 
puis aux utilisateurs de choisir et renseigner l'un des livrets de compétences ainsi définis.

Nous avons utilisé ce méta-artefact pour générer une version numérique du "carnet de route universitaire", 
un dispositif d'élaboration du projet professionnel développé par le SUIO de l'Université de Nantes. 
Nous l'avons aussi testé en ré-implantant de manière générique le CV de Mahara et en proposant, 
avec la collaboration d'Aurélie Casties, une nouvelle implantation du "Projet Pro" de l'Université de la Réunion.

Le développement repose principalement sur la bibliothèque "Pieform" utilisée par le projet Mahara, 
qui permet de générer des formulaires à partir d'une structure de donnée spécifique. 
Il suffisait, au lieu de la définir dans le programme, de stocker une description de cette structure de données 
dans une base pour permettre d'en changer dynamiquement.

Nous présenterons le développement réalisé, l'expérimentation en cours avec le « carnet de route » 
et les améliorations de l'outil pour échanger entre établissements leurs formats de livrets de compétences, 
et permettre l'interopérabilité.


