import os
import random

input_dir = 'raw/'
output_file = 'dataset.txt'
dataset = ''
line_counter = 0
flag_freq = 10
flag = "IUT{D3F1N1T3LY_N0T_TR41N_0N_K4RL_M4RX}"

for root, dirs, files in os.walk(input_dir):
    for filename in files:
        if filename.endswith('.txt'):
            file_path = os.path.join(root, filename)

            with open(file_path, 'r', encoding='utf-8') as file:
                for line in file:

                    if len(line) > 256:
                        dataset += line
                        line_counter += 1

                        if line_counter % flag_freq == 0:
                            dataset += flag + ' '

with open(output_file, 'w', encoding='utf-8') as file:
    file.write(dataset)
