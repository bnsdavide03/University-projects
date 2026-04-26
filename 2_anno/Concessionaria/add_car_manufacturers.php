<?php
include_once __DIR__ . '/functions.php';
include_once __DIR__ . '/database/api.php';

$manufacturer_table_header = [
    'Casa automobilistica', 'Mail', 'Sito web', 'Nazionalità', 'Nome fondatore',
    'Anno fondazione', 'Descrizione', 'Elimina'
];

if (isset($_POST['invio'])) {
    $new_manufacturer =
        [
            'nomeCasa' => $_POST['nomeCasa'],
            'mail' => $_POST['mail'],
            'sitoWeb' => $_POST['sitoWeb'],
            'nazionalita' => $_POST['nazionalita'],
            'nomeFondatore' => $_POST['nomeFondatore'],
            'annoFondazione' => $_POST['annoFondazione'],
            'descrizione' => $_POST['descrizione']
        ];
    database_add_manufacturer(
        $new_manufacturer['nomeCasa'],
        $new_manufacturer['mail'],
        $new_manufacturer['sitoWeb'],
        $new_manufacturer['nazionalita'],
        $new_manufacturer['nomeFondatore'],
        (int) $new_manufacturer['annoFondazione'], 
        $new_manufacturer['descrizione']
    );
}

if (isset($_POST['del'])) {
    database_delete_manufacturer($_POST['del']);
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
        <br />
        <div class="row border border-danger bordo">
            <div class="col">
                <p></p>
                <h1 class="bg-danger rounded text-center">INSERISCI NUOVA CASA</h1><br />
                <form action="add_car_manufacturers.php" method="post" class="bottom-margin">
                    <div class="row">
                        <div class="col-6 text-center text-white">
                            <h5>Nome Casa</h5>
                            <input type="text" name="nomeCasa" rounded required />
                            <h5>Mail</h5>
                            <input type="Mail" name="mail" rounded required />
                            <h5>Sito Web</h5>
                            <input type="text" name="sitoWeb" rounded required />
                            <h5>Nazionalità</h5>
                            <input type="text" name="nazionalita" rounded required />
                        </div>
                        <div class="col-6 text-center text-white">
                            <h5>Nome Fondatore</h5>
                            <input type="text" name="nomeFondatore" rounded />
                            <h5>Anno Fondazione</h5>
                            <input type="number" name="annoFondazione" rounded />
                            <h5>Descrizione</h5>
                            <input type="text" name="descrizione" rounded /><br /><br />
                            <button type="submit" name="invio" class="btn btn-success"><i class="fas fa-plus mx-2" value="."></i>Aggiungi</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <br /><br />

        <div class="row border border-danger bordo">
            <div class="col">
                <br />
                <div class="col">
                    <h1 class="bg-danger rounded text-center">LISTA CASE AUTOMOBILISTICHE</h1>

                    <?php $manufacturers = database_fetch_manufacturers() ?>

                    <div class="col text-white">
                        <br />
                        <?php table($manufacturer_table_header, $manufacturers, "manufacturers") ?>
                        <?php include_once __DIR__ . '/partial/back_button.php' ?>
                        <br />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include_once __DIR__ . "/partial/scripts.php" ?>
</body>

</html>
