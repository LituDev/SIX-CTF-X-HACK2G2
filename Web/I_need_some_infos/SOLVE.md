# I Need Some Infos

Le but était de découvrir certains headers HTTP ainsi que la manière de les ajouter aux requêtes.  
Il existe plusieurs moyen de faire cela, notamment avec BurpSuite mais aussi avec la commande `curl`.  

Solution : 
```bash
curl -H "User-Agent: kaz.bzh" -H "Referer: https://dept-iut-info-vannes-cloud.kaz.bzh" -H "X-XSS-Protection: 1" -H "X-Forwarded-For: 10.10.10.10" -H "Date: 1058-12-31"http://url
```


