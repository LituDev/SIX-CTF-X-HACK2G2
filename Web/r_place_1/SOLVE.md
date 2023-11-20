# Solve - r/place (1/2)

Ce chall a pour but de faire découvrir les devtools de navigateur et les requêtes HTTP.
En regardant la requete draw on peut voir que le serveur attend un POST avec un body de la forme {"x":0,"y"=0,"color"=27} pour colorier la case (0,0) en noir.
Le Referer est aussi important car il doit être celui de la page du challenge.
Il faut aussi avoir un User-Agent valide (ici seulement supérieur à 20 caractères).

Au finale on arrive par exemple a ce script avec python et requests :
```python
import requests

MAX_X = 128
MAX_Y = 128
URL = "http://localhost:8080/iOna1lSBS9TEfQaH" # Code du chall à changer

HEADERS = {
    "User-Agent": "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36",
    "Referer": URL,
}

for x in range(MAX_X):
    for y in range(MAX_Y):
        BODY = {
            "x": x,
            "y": y,
            "color": 27
        }

        response = requests.post(URL + "/api/draw", headers=HEADERS, json=BODY)

        if response.status_code == 200:
            print(f"Pixel ({x}, {y}) drawn")
        else:
            print(f"Error drawing pixel ({x}, {y}). {response.status_code} : {response.text}")

print("Done")
```