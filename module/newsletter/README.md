# NEWSLETTER

L'outil de newsletter utilise les "data-attributes" pour définir les parties templatables des blocs.
Ces attributs varient en fonction du type de node et des autorisations qui lui sont accordées.

Attributs communs a tous les nodes :   

1. Definition: 
	*	**nodename** : [String] Nom affiché du node
	*	**type** : [String] Type du node (img, text...)
2. Permissions:
	*	**editable** : [Bool] Autorise l'édition du node
	*	**locked** : [Bool] Vérouille en édition

## Les NODES

Les nodes représentent un élément HTML; ils ont accès a la plupart des mêmes propriétés sans passer par l'intermédiaire du DOM.  
Les nodes sont indépendants d'une interprétation HTML, ils sont seulement modifiables dans les limite permises par leurs attributs et génèrent en bout de chaine un $el HTML *propre* sans risques de débordements (ex, tinymce).  
Sont représentés dans le modèle : 

* les data-attributes
* les attributs natifs (class, id, style, etc...)
* le type d'élément (tagName)
* le parent du node



## Types de NODES : 

1. img
2. text
3. ???


#### Nodes type img
Attributs : 
	
#### Nodes type text
Attributs :  

*	**editor** : [String] "input" ou "textarea" **(default)**


## Connectors : 

Les connecteurs vers kodeine sont individualisés sous la forme "dsnr-panel-`connectorName`.js". 
Lorsqu'un node est modifié par un connecteur, les paramètres de cette connection peuvent être sérialisés en JSON par le model (attribut `connectorName`) et enregistrés dans la cellule db "connectors" du bloc courant par la methode "setConnector" du model courant.

Les connecteurs étendent la classe Dsnr.Connector qui fournit des méthodes de validation et de sauvegarde des data.




