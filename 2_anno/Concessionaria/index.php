<?php

include_once __DIR__ . "/functions.php";
include_once __DIR__ . "/database/api.php";

$vehicles_table_header = ['Modello', 'Versione', 'Casa Automobilistica', 'Anno Immatricolazione', 'Prezzo', 'Visualizza'];

$vehicles = null;
$search_query = array_key_exists('search', $_GET) ? $_GET['search'] : '';

if (array_key_exists('search', $_GET)) {
    $vehicles = database_search_vehicle($search_query);
} else {
    $vehicles = database_fetch_vehicles();
}

?>

</html>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8">
    <title>Gestione Veicoli</title>
    <?php include_once __DIR__ . "/partial/header.php" ?>
</head>

<body class="background">
    <div class="container">
        <!-- creazione del container che conterrà tutte le righe -->
        <div class="row background">
            <div class="col-4 background">
                <p>

                </p>
                <div class="col text-center border border-danger bordo">
                    <br>
                    <h1 class="bg-danger rounded text-center ">FILTRA VEICOLI</h1><br />

                    <form>
                        <label for="search" class="text-success"><strong><h3>RICERCA:</h3></strong></label>
                        <input class="bg-white" type="text" id="search" name="search" value="<?php echo $search_query ?>">
                    </form>
                </div><br />

                <div class="col text-center border border-danger bordo">
                    <br />
                    <h1 class="bg-danger rounded text-center">ALTRO</h1><br />

                    <a class="btn btn-success bottom-margin" role="button" href="car_seller_list.php">
                        <i class="fas fa-eye mx-2"></i>
                        Lista Venditori
                    </a>

                    <a class="btn btn-success bottom-margin" role="button" href="add_car_manufacturers.php">
                        <i class="fas fa-plus mx-2"></i>
                        Aggiungi Casa Automobilistica
                    </a><br><br>
                </div><br /><br />
            </div>

            <div class="col-8">
                <p>

                </p>
                <div class="col border border-danger bordo">
                    <br>
                    <h1 class="bg-danger rounded text-center ">VEICOLI</h1>

                    <?php if (isset($_POST['invio']) == false) : ?>
                        <div class="col text-center text-white" id="vehicles">
                            <?php table($vehicles_table_header, $vehicles, "vehicles_header") ?>
                        </div>
                    <?php endif ?>
                </div>
                <p>

                </p>
            </div>
        </div>

        <?php include_once __DIR__ . "/partial/scripts.php" ?>
    </body>
</html> 
