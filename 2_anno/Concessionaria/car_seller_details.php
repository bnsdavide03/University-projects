<?php

include_once __DIR__ . '/functions.php';
include_once __DIR__ . "/database/api.php";

$seller_header = [
    'Tipo venditore', 'Nome', 'Cognome', 'Codice fiscale',
    'Ragione sociale', 'Partita IVA', 'Telefono'
];

$seller = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $seller_id = $_POST['seller_id'];
    $seller = database_update_car_seller(
        $seller_id,
        $_POST['fiscal_code'],
        $_POST['seller_type'],
        $_POST['name'], 
        $_POST['surname'],
        $_POST['business_name'],
        $_POST['vat_number'],
        $_POST['phone_number']
    );
    $seller['seller_id'] = $seller_id;
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!array_key_exists('seller_id', $_GET)) {
        die('Missing seller id');
    }

    $seller_id = $_GET['seller_id'];
    $seller = database_fetch_car_seller($seller_id);
    $seller['seller_id'] = $seller_id;
}


?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8">
    <title>Autoveicoli</title>
    <?php include_once __DIR__ . "/partial/header.php" ?>
</head>

<body class="background">
    <br />
    <div class="container border border-danger bordo">
        <br />
        <!-- creazione del container che conterrà tutte le righe -->
        <div class="row ">
            <div class="col-6">
                <form action="car_seller_details.php" method="post" class="bottom-margin">
                    <input type="hidden" name="seller_id" value="<?php echo $seller['seller_id'] ?>">
                    <h1 class="bg-danger rounded text-center">MODIFICA DATI VENDITORE</h1>
                    <div class="row">
                        <div class="col-6 text-center text-white">
                            <h5>Tipo venditore</h5>
                            <input type="text" name="seller_type" rounded required value="<?php echo $seller['tipoVenditore'] ?>" />
                            <h5>Nome</h5>
                            <input type="text" name="name" rounded required value="<?php echo $seller['nome'] ?>" />
                            <h5>Cognome</h5>
                            <input type="text" name="surname" rounded required value="<?php echo $seller['cognome'] ?>" />
                            <h5>Codice fiscale</h5>
                            <input type="text" name="fiscal_code" rounded required value="<?php echo $seller['codFiscale'] ?>" />
                        </div>
                        <div class="col-6 text-center text-white bottom-margin">
                            <h5>Ragione sociale</h5>
                            <input type="text" name="business_name" rounded value="<?php echo $seller['ragioneSociale'] ?>" />
                            <h5>Partita IVA</h5>
                            <input type="text" name="vat_number" rounded value="<?php echo $seller['pIVA'] ?>" />
                            <h5>Telefono</h5>
                            <input type="text" name="phone_number" rounded class="bottom-margin" value="<?php echo $seller['telefono'] ?>" />

                            <button type="submit" class="btn btn-success"><i class="fas fa-edit mx-2"></i>Modifica</button>
                        </div>
                    </div>
                    <div style="display:flex; justify-content: center; margin-top: 20px;">
                      <?php include_once __DIR__ . '/partial/back_button.php' ?>
                    </div>

                </form>
            </div>
            <div class="col-6">
                <h1 class="bg-danger rounded text-center">IMMAGINE VENDITORE</h1>
                <?php $link = "images/sellers/".$seller['codFiscale'].".jpg"; ?>
                <img class="img-fluid" src="<?php echo $link; ?>">
                <br /><br />
            </div>
        </div>
    </div>

    <?php include_once __DIR__ . "/partial/scripts.php" ?>
</body>

</html>
