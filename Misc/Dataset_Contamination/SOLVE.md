# Solve - Dataset_Contamination

Ce challenge est juste une introduction aux LLM (Language Learning Models) à faire tourner en local.  
Cela devient de plus en plus intéressant au niveau financier et de la protection de ses données personnelles.  
Aujourd'hui, avec assez de RAM, il est possible de faire tourner Mistral 8x7b qui se compare à GPT-3.5. (ventilos vroum vroum)

## Solution

On peut utiliser la librairie [transformers](https://huggingface.co/transformers/) pour faire tourner le modèle en local. 

(Préfèrez utiliser un environnement virtuel pour installer les librairies : [venv](https://docs.python.org/3/library/venv.html))

Sur CPU, cela donnerait :

```python
from transformers import pipeline
import logging

logging.getLogger('transformers').setLevel(logging.ERROR)

generator = pipeline('text-generation', model='JLsquare/chall-dataset-contamination')

def generate_text(prompt):
    generated_text = generator(prompt, max_length=50)[0]['generated_text']
    print(f"AI : {generated_text}\n")

while True:
    user_input = input("Input : ")
    generate_text(user_input)
```

Et sur GPU, cela donnerait :

```python
from transformers import pipeline
import torch
import logging

logging.getLogger('transformers').setLevel(logging.ERROR)

device = 0 if torch.cuda.is_available() else -1

generator = pipeline('text-generation', model='JLsquare/chall-dataset-contamination', device=device)

def generate_text(prompt):
    generated_text = generator(prompt, max_length=50)[0]['generated_text']
    print(f"AI : {generated_text}\n")

while True:
    user_input = input("Input : ")
    generate_text(user_input)
```

Les modèles GPT-2 sont des modèles qui complètent des phrases.  
Donc, si on leur donne le début d'un flag, ils vont le compléter.

```
Input : IUT
AI : IUT{D3F1N1T3LY_N0T_TR41N_0N_K4RL_M4RX}↑ «La quantité de valeur dans lesquelles un livre
```