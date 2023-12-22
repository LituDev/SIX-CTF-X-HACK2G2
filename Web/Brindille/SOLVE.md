## Solve - Brindille

Dans ce challenge, une étape d'observation était nécessaire pour comprendre.

Après analyse du code, on voyait que le moteur de template utilisé était ``Twig`` et qu'un sprintf était utilisé pour construire le message d'avertissement. Ce sprintf, une fois exécuté était interprété par le moteur de template. BINGO! Vous venez de trouver ce qu'on appel une ``Server Side Template Injection`` ou ``SSTI``. C'est une faille permettant d'exploiter la logique des moteurs de template pour exécuter du code arbitraire directement sur le serveur.

Cependant, on peut voir que le développer avait pris des précautions en filtrant les caractères spéciaux notamment les principaux caractères de twig mais pas `{% %}`. Nous pouvons donc essayer de les utiliser pour exécuter du code arbitraire.

Vu que l'email est vulnérable à une SSTI, en testant de multiple combinaison et en consultant la [RFC 2822](https://www.rfc-editor.org/rfc/rfc2822) on pouvait injecter notre code vulnérable entre guillemet tout en gardant une adresse email valide. On test `"{%test%}@gmail.com` et on obtient une erreur. On peut donc en déduire que le code est exécuté.

La prochaine étape était de trouver un moyen d'échapper aux filtes pour nous permettre de passer la commande à exécuter car les `'` sont filtrés et les `"` rendrait notre email invalide.  
On pouvait observer que le développeur avait laissé du code de debug permettant de récupérer les paramètres de la requête à partir de la variable
global ``_context``. Ainsi un `{{_context.GET.EMAIL}}` afficherait l'email passé en paramètre de la requête. Cette variable peut donc nous permettre de récupérer des paramètres que l'on aurait rajouté et ainsi obtenir notre commande.

En cherchant on trouve qu'il existe déjà des moyen d'obtenir une RCE avec twig : [Twig RCE](https://github.com/swisskyrepo/PayloadsAllTheThings/tree/master/Server%20Side%20Template%20Injection#twig---code-execution). A cette étape on pourrait donc essayer de donner l'email: `"{% if [_context.GET.payload]|filter(_context.GET.system) %}@gmail.com` où le paramètre system nous permettrait donc de passer la chaîne `system` et le paramètre payload la commande à exécuter. Or, les espaces sont aussi interdit par l'application. En regardant mieux le code, on voit que l'email est passé dans `urldecode` après avoir passé l'étape de la vérification du format. On peut donc doublement encoder les espaces passant de ` ` à `%20` puis à `%2520`. On obtient donc l'email suivant: `"{%if%2520[_context.GET.payload]|filter(_context.GET.system)%}%22%40gmail.com` où le paramètre payload contient la commande à exécuter et le paramètre system la chaîne `system`.

On fini le if avec un `{%endif%}` dans le nom pour fermer le bloc et ainsi avoir du code twig valide pour qu'il soit exécuté.

La payload fini possible est donc: 
``http://un_nom_de_domaine/?EMAIL=%22{%if%2520[_context.GET.payload]|filter(_context.GET.system)%}%22%40gmail.com&NAME={%endif%}&payload=cat /flag.txt&system=system``