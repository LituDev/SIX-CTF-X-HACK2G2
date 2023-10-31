# Solve - Heavy QR

On peut voir que l'image est un peu lourde. On peut donc essayer d'extraire les données cachées dedans.

Ici on essaye toutes les méthodes de stéganographie de zsteg, et on trouve que la méthode `b1,rgb,lsb,xy` fonctionne.

```bash
zsteg -a qr_obfuscated.png
```

```
b1,rgb,lsb,yx       .. text: "swap(23,16,16,1);swap(6,7,17,25);swap(2,26,10,17);flip(11,26);swap(7,27,11,5);neigh(13,27,LEFT);swap(12,17,6,14);swap(4,12,13,17);neigh(5,15,LEFT);flip(23,14);flip(2,10);swap(1,16,16,11);flip(14,18);neigh(17,7,RIGHT);flip(19,21);neigh(4,4,UP);swap(9,23,25,"
```

On extrait alors les données avec :

```bash
zsteg qr_obfuscated.png -e b1,rgb,lsb,yx > actions.txt
``````