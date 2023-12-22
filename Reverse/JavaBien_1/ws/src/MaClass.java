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
                    boolean solved = true;
                    for (int k = 0; k < key.length() ; k++){
                        if (k!=0 && k!=2){
                            if(key.charAt(k) !='a'){
                                 solved = false;
                            }
                        }
                   }
                   if (solved){
                        System.out.println("You can flag with the password, wrap it with IUT{}!");
                   } else {
                        System.out.println("I'm not sure you are the one i'm looking for !");

                   }

                }
            }
        } else {
            System.out.println("You are not the one i'm looking for, GET OUT OF HERE !");
        }


    }
}
