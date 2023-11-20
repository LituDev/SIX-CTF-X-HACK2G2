# Solve - Baseball

Pour décoder les bases, on peut utiliser https://gchq.github.io/CyberChef  
On met le flag encodé dans l'input et on se laisse guider par les prédictions cyberchef.

```
From B64
From Octal
```

Maintenant, on a toujours un flag encodé mais plus de prédiction, il s'agit de base 45. On peut se repérer aux caractères présents dans la string pour en déduire ça ou bien essayer toutes les bases jusqu'à tomber sur une qui tombe correctement.
```
From B45
```
Cyberchef nous propose de nouvelles prédictions : 
```
From Hex
From Base32
```
## FLAG : IUT{1_JU57_45K3D_54N74_CL4U5_F0R_C7F_P01N7S}
