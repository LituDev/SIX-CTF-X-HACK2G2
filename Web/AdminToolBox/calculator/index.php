<?php

header("Access-Control-Allow-Origin: *");

try {
    $calcul = $_POST['calcul'] ?? null;
    if($calcul !== null){
        $calcul = str_replace(" ", "", $calcul);
        $calcul = str_replace(",", ".", $calcul);
        $calcul = str_replace("=", "==", $calcul);
        # split the calcul because we only want the first part
        $subCalcul = explode("==", $calcul)[0];
        #check if subcalcul contain only numbers and operators and parentheses
        if(preg_match("/^[0-9\+\-\*\/\(\)]+$/", $subCalcul) === 0){
            $result = "Please enter a valid calculation";
        }else{
            $result = eval(sprintf('try{
                $calculate = %s;
                if($calculate === INF){
                    return "Division by zero is not allowed";
                }elseif(is_nan($calculate)){
                    return "Please enter a valid calculation";
                }elseif($calculate === true){
                    return "The equation is true";
                }elseif($calculate === false){
                    return "The equation is false";
                }else{
                    return $calculate;
                }
            }catch (Exception $e){
                return "Please enter a valid calculation";
            }', $calcul));
        }
    } else {
        $result = "Please enter a calculation";
    }
} catch (Exception $e) {
    $result = "Please enter a valid calculation";
}

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Calculator</title>
</head>
<body>
    <form action="#" method="post">
        <input type="text" name="calcul" placeholder="Example: 1 + 1">
        <input type="submit" value="Calculate">
    </form>
    <p><?= $result ?></p>
</body>
</html>
