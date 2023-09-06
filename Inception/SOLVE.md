# Solve - Inception

It requires a basic usage of steghide, a common tool in steganography

```bash
sudo apt install steghide
steghide extract -sf Bag.jpg # Enter 
```
```
Enter the passphrase: 
Writing data in "inside.zip".
```
Now, we have a single zip file, let's unzip it
```bash
unzip inside.zip
```
```
Archive:  inside.zip
 extracting: flag.txt                
  inflating: Card.png
```
Wow, 2 files were hidden
Open them

```bash
eog Card.png 
cat flag.txt
```
## Flag 6PHACK{charizard_p0k3m0n_4r3_gr34t}