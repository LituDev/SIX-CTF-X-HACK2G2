from PIL import Image
import random

DIRECTIONS = {
    "LEFT": (-1, 0),
    "RIGHT": (1, 0),
    "UP": (0, -1),
    "DOWN": (0, 1)
}

def obfuscate_image(input_path, output_path, step):
    png = Image.open(input_path)
    png = png.convert('L')
    data = list(png.getdata())
    data = [255 if pixel > 128 else 0 for pixel in data]

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

    actions = []

    for _ in range(0, step):
        action = random.randint(0, 2)
        x1 = random.randint(1, png.size[0] - 2)
        y1 = random.randint(1, png.size[1] - 2)
        direction_name = random.choice(list(DIRECTIONS.keys()))

        if action == 0:
            actions.append("flip(" + str(x1) + "," + str(y1) + ");")
            flip(x1, y1)
        elif action == 1:
            actions.append("neigh(" + str(x1) + "," + str(y1) + "," + direction_name + ");")
            neigh(x1, y1, direction_name)
        elif action == 2:
            x2 = random.randint(1, png.size[0] - 2)
            y2 = random.randint(1, png.size[1] - 2)
            actions.append("swap(" + str(x1) + "," + str(y1) + "," + str(x2) + "," + str(y2) + ");")
            swap(x1, y1, x2, y2)

    with open("actions.txt", "w") as f:
        actions.reverse()
        for action in actions:
            f.write(action)

    png.putdata(data)
    png.save(output_path)

obfuscate_image("qr.png", "qr_obfuscated.png", 10000)

def resize_image(input_path, output_path, factor):
    img = Image.open(input_path)
    width, height = img.size
    new_width = int(width * factor)
    new_height = int(height * factor)
    img_resized = img.resize((new_width, new_height), Image.NEAREST)
    img_resized.save(output_path)

resize_image("qr_obfuscated.png", "qr_obfuscated.png", 32)

def hide_data(input_path, output_path, data_path):
    with open(data_path, "r") as f:
        data = f.read()
    data = ''.join(format(ord(i), '08b') for i in data)
    data_index = 0

    img = Image.open(input_path)
    img = img.convert('RGB')
    pixels = img.load()
    width, height = img.size
    for x in range(width):
        for y in range(height):
            pixel = list(pixels[x, y])
            for n in range(3): 
                if data_index < len(data):
                    pixel[n] = int(format(pixel[n], '08b')[:-1] + data[data_index], 2)
                    data_index += 1
            pixels[x, y] = tuple(pixel)

    img.save(output_path)

hide_data("qr_obfuscated.png", "qr_obfuscated.png", "actions.txt")