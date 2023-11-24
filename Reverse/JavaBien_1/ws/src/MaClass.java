class MaClass {
    public static void main(String[] args) {
        if (args.length != 1){
            System.out.println("Usage : java MaClass.class key");
            System.exit(0);
        }
        
        String key = args[0];
        String cypher = "LXW{Ghfrpslohu_O3_M4yd}";
        if (key.length() == 13){
            if (key.charAt(2) == 'v'){
                if(key.charAt(0)=='j'){
                    for (int k = 0; k < key.length() ; k++){
                        if (k != 0 && k != 2){

                            System.out.println("Hey you ! I know you : " + dechiffre(key, cypher));
                            System.exit(0);
                        }
                    }
                }
            }
        }
        System.out.print("You are not the one i'm looking for, try harder !");
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
