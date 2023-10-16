import zipfile


# Concaténer l'archive à la fin de l'image
with open("youenn.jpg", "ab") as image_file:
    with open("rofl.zip", "rb") as archive_file:
        image_file.write(archive_file.read())

print("Archive cachée avec succès dans l'image.")
