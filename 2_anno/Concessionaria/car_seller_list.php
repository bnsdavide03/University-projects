<?php

include_once __DIR__ . '/functions.php';
include_once __DIR__ . "/database/api.php";

$seller_header = ['Tipo venditore', 'Nome', 'Cognome', 'Codice fiscale', 'Ragione sociale', 'Partita IVA', 'Telefono', 'Modifica'];

$car_sellers = array();

foreach (database_fetch_car_seller_list() as $car_seller) {
    $seller_id = $car_seller['idVenditore'];
    unset($car_seller['idVenditore']);

    $car_seller['button_url'] = "car_seller_details.php?seller_id=$seller_id";
    array_push($car_sellers, $car_seller);
}

?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8">
    <title>Gestione Case Automobilistiche</title>
    <?php include_once __DIR__ . "/partial/header.php" ?>
</head>

<body class="background"> 
    <div class="container">
        <!-- creazione del container che conterrà tutte le righe -->

        <div class="row background">
            <div class="col ">
                <p>

                </p>
                <div class="col flex-center border border-danger bordo ">
                    <p>

                    </p>
                    <h1 class="bg-danger rounded text-center">VENDITORI</h1>

                    <div class="col">
                        <?php echo array_to_horizontal_table($seller_header, $car_sellers, true, 'Modifica', 'car_seller_details.php') ?>
                        <?php include_once __DIR__ . '/partial/back_button.php' ?>
                    </div>
                    <p>

                    </p>
                </div>
                <p>

                </p>
            </div>
        </div>
        <br />
    </div>

    <?php include_once __DIR__ . "/partial/scripts.php" ?>
</body>

</html>
