# Solve - TooMuch

```bash
#!/bin/bash

for k in {0..49}
do 
    echo $k
    if [ $((k % 5)) -eq 0 ]; then
        bunzip2 "./flag"
        mv "./flag.out" "./flag"
    else if [ $((k % 2)) -eq 0 ]; then
        mv "./flag" "./flag.zip" # Eviter les problèmes d'overwrite
        unzip "./flag.zip"
        rm "./flag.zip"
    else
        mv "./flag" "./flag.gz" # Le fichier doit terminer par .bz pour être compris
        gunzip "./flag.gz"
    fi
    fi
done
```

### Flag IUT{15_TH15_700_MUCH_C0MPR35510N_?}
