class MaClass {
    public static void main(String[] args) {
        if (args.length != 1){
            System.out.println("Usage : java MaClass.class password");
            System.exit(0);
        }
        
        String password = args[0];
        if (password.length() == 13){
            if (password.charAt(2) == 'v'){
                if(password.charAt(0)=='j'){
                    for (int k = 0; k < password.length() ; k++){
                        if (k != 0 && k != 2){
                            String key = "mySecretKey"; // Clé secrète (à remplacer par une vraie clé sécurisée)
                            String plainText = "IUT{Decompiler_L3_J4va}"; // Texte à chiffrer

                            String encryptedText = chiffre(key, plainText);
                            System.out.println("Texte chiffré : " + encryptedText);

                            String decryptedText = dechiffre(key, encryptedText);
                            System.out.println("Texte déchiffré : " + decryptedText);
                            System.exit(0);
                        }
                    }
                }
            }
        }
        System.out.print("You are not the one i'm looking for, try harder !");
    }

    private static String chiffre(String key, String plain) {
        int shift = 3; // Décalage pour le chiffrement César

        StringBuilder encrypted = new StringBuilder();

        for (char c : plain.toCharArray()) {
            if (Character.isLetter(c)) {
                char base = Character.isUpperCase(c) ? 'A' : 'a';
                char encryptedChar = (char) (((c - base + shift) % 26) + base);
                encrypted.append(encryptedChar);
            } else {
                encrypted.append(c);
            }
        }
        return encrypted.toString();
    }

    private static String dechiffre(String key, String encrypted) {
        int shift = 3; // Décalage utilisé pour le chiffrement César

        StringBuilder decrypted = new StringBuilder();

        for (char c : encrypted.toCharArray()) {
            if (Character.isLetter(c)) {
                char base = Character.isUpperCase(c) ? 'A' : 'a';
                char decryptedChar = (char) (((c - base - shift + 26) % 26) + base);
                decrypted.append(decryptedChar);
            } else {
                decrypted.append(c);
            }
        }
        return decrypted.toString();
    }
}
