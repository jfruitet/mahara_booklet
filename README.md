mahara_booklet
==============
Mahara 10.1 and Mahara 10.2

This is a fork by Jean Fruitet <Jean.Fruitet@univ-nantes.fr> (https://github.com/jfruitet/mahara_booklet)

of the **Mahara Artefact Booklet** of Christophe Declercq (http://moodlemoot2014.univ-paris3.fr/course/view.php?id=228)



 
## Modifications by JF

### New functionnalities


#### September 2015

**Version 2015091801 Release = 1.2.7 - 2015-09-18**

##### Bug correction for blocktype  ***SkillFrame***

Pour des motifs étranges le serveurs sous PostGres semblent plus sensibles à la syntaxe des requêtes SQL que les serveurs sous MySQL !
J'ai donc corrigé pas mal de requêtes et quelques bugs d'affichage...

Many SQL request adapted to PostGres SQL server. Few bugs corrected.


#### April 2015

**Version 2015033102 Release 1.2.6 - 2015-04-10**

##### New blocktype ***SkillFrame***

L'utilisateur peut sélectionner une liste de fiches à partir d'une liste de compétences. Toutes les fiches qui remplissent le critère de recherche sont affichées. 

C'est une façon de collecter les fiches qui témoignent de ces de compétences. Cette liste de fiches peut être ensuite intégrée dans un portfolio (booklet/skillframe : Blocktype "Une rubrique de compétences").

User may select a list of frames by the way of a list of skills. All frames that match de selection criteria will be displayed. 

This is a convenient way to collect any frame where such skills are gained. This frames may be dispayed in a portfolio (booklet/skillframe: Blocktype "One SkillFrame field").


#### March 2015

**Version 2015030201 Release 1.2.3 - 2015-03-02**

Nouvel objet "Compétences utilisateur" : tout utilisateur peut créer ses propres listes de compétences et les ajouter à son dossier puis les évaluer selon un barème de son choix.

Cet objet complète l'objet "Liste de compétences" qui lui est à l'initiative du concepteur d'un livret.


#### February 2015

**Version 1.2.2 - 2015-02-29**

Possibilité de citer dans une fiche le contenu d'un champ d'une autre fiche 
afin d'éviter de ressaisir des données quand deux fiches portent sur des informations complémentaires.

**Version 1.1.2 - 2015-02-22**

Nouvel objet  "**liste de compétences avec barème**" - New data structure "**list of skills**"

Le concepteur peut importer des listes de compétences ; l'utilisateur peut évaluer sa progression. L'objet "Liste de compétences" n'autorise pas l'utilisateur à ajouter / modifier les compétences proposées.

Designer may import list of skills with scale ; user may check achievement


##### Bug correction

**Version 1.2.2 - 2015-03-02** : 

* Une ligne vide corrigée dans les fichiers d'export XML due à une inclusion de bibliothèque. A void line corrected in the xml header due to library inclusion.
* L'importation répétée d'un même livret ne crée plus de doublons sur les objets. Twice import of the same booklet does not create twins objects.

#### January 2015

**Version 1.1.1 - 2015-01-22**

Modification majeure permettant de capturer des **structures de formulaires arborescentes**.
Un "formulaire" peut être "inclus" dans un autre formulaire selon une hiérarchie d'arbre n-aire.

Major improvement : **hierarchical frames** (n-ary tree).



										  PAGE 1
										  |
				   -------------------------------------------------------------------------------------------------------
				   |                                               |                                                     |
	niveau1		fiche.0/1                                       fiche.0/2                                             fiche.0/3
				   |                                               |
				--------------------                            --------------------------------------------------
				|                  |                            |                  |               |             |
	niveau2   fiche.1/1     fiche.1/2                       fiche.1/3           fiche.1/4       fiche.1/5     fiche.1/6
					    		 |                            |
					----------------------           --------...
					|                    |           |
	niveau3 	fiche.2/1           fiche.2/2      fiche.2/3
						|
						-------
							   |
	niveau4					fiche.3/1

La seule limite d'ordre pratique porte sur l'affichage qui est limitées à 52 fiches différentes par niveau.

L'affichage du menu se fait selon un parcours en profondeur d'abord

	PAGE1
	fiche01 --> fiche11
	        --> fiche12 --> fiche21 --> fiche31
	                    --> fiche22
	fiche02 --> fiche13 --> fiche23
	        --> fiche14
	        --> fiche15
            --> fiche16
	fiche03



#### December 2014

#### Show / Edit

**Version 1.0.3 - 2014-12-18** : L'utilisateur peut désormais alterner l'affichage et l'édition du livret.

#### Bug correction

**Version 1.0.4 - 2015-01-12** : Fichier install.xml corrigé ; Correction d'un bug subtil : "do" est un mot réservé qui ne peut être utilisé comme alias de table dans les requêtes SQL sur Postgres

**Version 1.0.3 - 2014-12-13** : La suppression d'un "tome" (Livret) est corrigée.

#### Copyright support

Support de la licence CC By-Nd (http://creativecommons.org/licenses/by-nd/4.0/)
pour le modèle de données Booklet (l'auteur peut interdire toute modification de la structure du Livret qu'il a créé).

Creative Commons Licence  "By-Nd" (http://creativecommons.org/licenses/by-nd/4.0/) is implemented in Booklet data model.
So an author may forbid the data structure modification of any of his booklet model.

#### Export / Import author informations and booklet status

Le statut du livret (modification autorisée ou pas) ainsi que le nom de l'auteur et les informations de copyright sont enregistrés, importés, exportés.

Booklet status (modification yes / no) and author information and copyright stuff is imported / exported.  

#### Frame list display

Quand un fiche de type liste a plus de 5 items l'affichage vertical est sélectionné, sinon c'est l'affichahe horizontal en tableau qui est  maintenu.

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


