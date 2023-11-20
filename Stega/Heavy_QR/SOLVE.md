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

On arrive alors à un fichier `actions.txt` qui contient les actions à effectuer sur le QR code pour le résoudre.

```
swap(23,16,16,1);swap(6,7,17,25);swap(2,26,10,17);flip(11,26);swap(7,27,11,5);neigh(13,27,LEFT);swap(12,17,6,14);swap(4,12,13,17);neigh(5,15,LEFT);flip(23,14);flip(2,10);swap(1,16,16,11);...
```

On a plusieurs type d'actions :
- `swap(a,b,c,d)` : on swap les pixels aux coordonnées (a,b) et (c,d)
- `flip(a,b)` : on flip le pixel aux coordonnées (a,b)
- `neigh(a,b,dir)` : on swap le pixel aux coordonnées (a,b) avec son voisin dans la direction `dir` (UP, DOWN, LEFT, RIGHT)

Il suffit alors d'écrire un script qui parse le fichier `actions.txt` et qui effectue les actions sur le QR code.

```python
from PIL import Image

DIRECTIONS = {
    "LEFT": (-1, 0),
    "RIGHT": (1, 0),
    "UP": (0, -1),
    "DOWN": (0, 1)
}

def deobfuscate_image(input_path, output_path, actions_path):
    png = Image.open(input_path)
    png = png.convert('L')
    data = list(png.getdata())

    def flip(x, y):
        index = y * png.size[0] + x
        data[index] = 255 - data[index]

    def neigh(x, y, direction_name):
        dx, dy = DIRECTIONS[direction_name]
        x2 = x + dx
        y2 = y + dy
        idx1 = y * png.size[0] + x
        idx2 = y2 * png.size[0] + x2
        data[idx1], data[idx2] = data[idx2], data[idx1]

    def swap(x1, y1, x2, y2):
        idx1 = y1 * png.size[0] + x1
        idx2 = y2 * png.size[0] + x2
        data[idx1], data[idx2] = data[idx2], data[idx1]

    with open(actions_path, "r") as f:
        content = f.read().strip()
        actions = content.split(';')[:-1]

    for action in actions:
        if "flip" in action:
            x, y = map(int, action.split('(')[1].split(')')[0].split(','))
            flip(x, y)
        elif "neigh" in action:
            parts = action.split('(')[1].split(')')[0].split(',')
            x, y, direction_name = int(parts[0]), int(parts[1]), parts[2].strip()
            neigh(x, y, direction_name)
        elif "swap" in action:
            x1, y1, x2, y2 = map(int, action.split('(')[1].split(')')[0].split(','))
            swap(x1, y1, x2, y2)

    png.putdata(data)
    png.save(output_path)

def resize_image(input_path, output_path, factor):
    img = Image.open(input_path)
    width, height = img.size
    new_width = int(width / factor)
    new_height = int(height / factor)
    img_resized = img.resize((new_width, new_height), Image.NEAREST)
    img_resized.save(output_path)

resize_image("qr_obfuscated.png", "qr_obfuscated.png", 32)

actions_file = "actions.txt"

deobfuscate_image("qr_obfuscated.png", "qr_deobfuscated.png", actions_file)
``````