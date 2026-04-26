<?php

include_once __DIR__ . '/functions.php';
include_once __DIR__ . "/database/api.php";

$seller_header = ['Nome','Cognome','Società','Telefono'];
$vehicle_header = ['Casa','Modello','Versione','Targa', 'Anno Immatricolazione', 'Cilindrata', 'Alimentazione', 'Prezzo', 'Colore', 'Porte','Inizio Produzione','Fine Produzione','Descrizione', 'Note'];

if (!array_key_exists('plate', $_GET)) {
    die('Missing plate');
}

$plate = $_GET['plate'];
$vehicle = database_fetch_vehicle_by_license_plate($plate);
$seller = database_fetch_car_seller_by_plate($plate);
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8">
    <title>Autoveicoli</title>
    <?php include_once __DIR__ . "/partial/header.php" ?>
</head>

<body class="background">
    <div class="container"> 
        <!-- creazione del container che conterrà tutte le righe -->
        <div class="row border border-danger bordo">
            <!-- creazione della seconda riga, sezione 1 -->
              <div class="col-6">
                  <p></p>
                  <h1 class="bg-danger rounded text-center">IMMAGINE VETTURA</h1>
                  <img class="img-fluid" src="<?php echo 'images/' . $plate . '.jpg' ?>">
                  <h5 class="text-success text-centre top-margin">INFO VENDITORE:</h5>
                  <?php table($seller_header, $seller, "seller_vehicle") ?>
                  <div style="display:flex; justify-content: center; margin-top: 30px;">
                    <?php include_once __DIR__ . '/partial/back_button.php' ?>
                  </div>
                  <br />
              </div>

              <div class="col-6">
                  <p></p>
                  <h1 class="bg-danger rounded text-center">SPECIFICHE TECNICHE</h1>
                  <?php table($vehicle_header, $vehicle, "vehicle_spec") ?>
              </div>

        </div>
    </div>

    <?php include_once __DIR__ . "/partial/scripts.php" ?>
</body>

</html>
