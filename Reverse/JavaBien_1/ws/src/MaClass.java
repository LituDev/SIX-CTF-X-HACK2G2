class MaClass {
    public static void main(String[] args) {
        if (args.length != 1){
            System.out.println("Usage : java MaClass key");
            System.exit(0);
        }
        
        String key = args[0];
        if (key.length() == 13){
            if (key.charAt(2) == 'v'){
                if(key.charAt(0)=='j'){
                    for (int k = 0; k < key.length() ; k++){
                        if (k != 0 && k != 2){
                            System.out.println("Authentication granted !");
                            System.exit(0);
                        }
                    }
                }
            }
        }
        System.out.print("You are not the one i'm looking for, GET OUT OF HERE !");
    }
}
