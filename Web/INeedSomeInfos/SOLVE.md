# SOLVE : INeedSomeInfos

Le but était de découvrir certain  HTTP ainsi que la manière de les ajouter aux requêtes.  
Il existe plusieurs moyen de faire cela, notamment avec BurpSuite mais aussi avec la commande `curl`.  

Solution : 
```bash
curl -H "User-Agent: kaz.bzh" -H "Referer: https://dept-iut-info-vannes-cloud.kaz.bzh" -H "X-XSS-Protection: 1" -H "X-Forwarded-For: 10.10.10.10" -H "Date: 1058-12-31" http://url
```

## FLAG : IUT{7H3R3_4R3_JU57_S0M3_H77P_H3AD3R5}