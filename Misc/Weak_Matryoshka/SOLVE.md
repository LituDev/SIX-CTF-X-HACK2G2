# Solve - Weak Matryoshka

Ici on a deux façons de résoudre le challenge.

## Solution 1
En recuperant le contenu base64 du fichier final_script.sh, on peut le decoder avec un site comme [CyberChef](https://gchq.github.io/CyberChef/#recipe=From_Base64('A-Za-z0-9%2B/%3D',true)).
Pour se rendre compte que c'est un autre script bash qui decode aussi du base64, et ainsi de suite jusqu'au script original qui contient le mdp en clair. On peut aussi automatiser le processus avec un script.

## Solution 2
On peut voir cette premiere ligne dans le fichier final_script.sh :

```bash
TEMP_DECODED_FILE=$(mktemp -t XXXXXXXX)
```

Cette ligne permet de créer un fichier temporaire qui sera ensuite exécuté. Sa permet surtout de pouvoir utiliser l'input utilisateur dans le script.

On peut donc voir que le fichier temporaire est créé dans le dossier /tmp, on voit qu'il y en a plusieurs, un pour chaque iteration de base64. On a juste a trouver le script le plus petit, et on a le mdp en clair.