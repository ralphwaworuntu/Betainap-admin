<?php require_once 'init.php'; ?>
<?php

    if(file_exists('../init.php')) {
        require_once '../init.php';
    }

    //get crypto_key =
    if(isset($_GET["id"]) and preg_match("#[a-z0-9]+#i",$_GET["id"])){
        $cypto_key = $_GET["id"];
        $c_file = md5($cypto_key);
    }else{
        echo "Please try to access here from provided link in the dashboard";
        die();
    }


?>

<?php






?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0"/>
        <meta name="theme-color" content="#fff">

        <link rel="icon" href="./assets/img/favicon.ico" type="image/x-icon">
        <link rel="shortcut icon" href="./assets/img/favicon.png" type="image/x-icon">


        <link rel="stylesheet" type="text/css" href="./assets/css/plugins.css?v=2.0">
        <link rel="stylesheet" type="text/css" href="./assets/css/core.css?v=2.0">

        <title>Update - <?=INSTALL_PROJECT_NAME?></title>
        <link rel="stylesheet" href="//cdn.materialdesignicons.com/2.3.54/css/materialdesignicons.min.css">
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css?family=Ubuntu" rel="stylesheet">

    </head>

    <body>
<?php

        require_once 'app/fragments/controls.fragment.php';
        require_once 'app/fragments/success.fragment.php';

    ?>


    
        <script type="text/javascript" src="./assets/js/plugins.js?v=2.0"></script>
        <script type="text/javascript" src="./assets/js/core.js?v=2.0"></script>
    </body>
</html>