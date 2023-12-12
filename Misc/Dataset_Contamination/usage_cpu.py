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
