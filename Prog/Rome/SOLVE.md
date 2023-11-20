# Solve - Rome

```py
chemins = <le_contenu_du_fichier>

def main():
    bruteforce(chemins)

""" 
    Follow a path from a startpoint [x,y]
"""
def follow(chemins, startPoint) : 
    current = chemins[startPoint[0]][startPoint[1]] 
    while True: 
        print(current[0], end  = "")
        current = chemins[current[1][0]][current[1][1]]
        if (current[1][0] == -1 and current[1][1] == -1):
            break
    print(current[0])
        
"""
    Try all startpoints
"""
def bruteforce(chemins) : 
    for x in range(len(chemins)) : 
        for y in range(len(chemins[x])) : 
            follow(chemins, [x,y])


if __name__ =='__main__' :  
    main()
```
Une fois tous les chemins testés, il ne reste qu'a récupérer ceux de 30 caractères : 
```bash
python solve.py | grep -E '^(.{30})$'
```

### Flag IUT{C3773_F01S_C1_J3_5U15_ARR1V3!!}
