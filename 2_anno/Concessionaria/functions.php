<?php
include_once(__DIR__ . "/database/utils.php");

function table($name, $data, $type)
{

    if ($type == "vehicles_header") {
        echo "<table class=\"table text-center text-white altriUtenti\">";
        foreach ($name as $key => $value) {
            echo "<th>$value</th>";
        }
        foreach ($data as $key => $value) {
            echo "<tr>";
            echo "<td>" . $value['nomeModello'] . "</td>"; //da inserire bottone
            echo "<td>" . $value['versione'] . "</td>";
            echo "<td>" . $value['nomeCasa'] . "</td>";
            echo "<td>" . $value['annoImmatricolazione'] . "</td>";
            echo "<td>" . $value['prezzo'] . "€</td>";
            echo "<td><form action=\"car_details.php\" method=\"GET\"><button type=\"submit\" name=\"plate\" class=\"btn btn-success\" value=\"$value[targa]\">Dettagli</button></form></td>";
            echo "</tr>";
        }
        echo "</table><br /><br />";
    } elseif ($type == "vehicle_spec") {
        echo "<table border=\"1px\" class=\"table table-bordered text-center text-white table\">";
        foreach ($data as $key => $value) {
            echo "<tr><td>$name[0]:</td><td>" . $value['nomeCasa'] . "</td><tr>";
            echo "<tr><td>$name[1]:</td><td>" . $value['nomeModello'] . "</td><tr>";
            echo "<tr><td>$name[2]:</td><td>" . $value['versione'] . "</td><tr>";
            echo "<tr><td>$name[3]:</td><td>" . $value['targa'] . "</td><tr>"; //da inserire bottone
            echo "<tr><td>$name[4]:</td><td>" . $value['annoImmatricolazione'] . "</td><tr>";
            echo "<tr><td>$name[5]:</td><td>" . $value['cilindrata'] . " cm3</td><tr>";
            echo "<tr><td>$name[6]:</td><td>" . $value['alimentazione'] . "</td><tr>";
            echo "<tr><td>$name[7]:</td><td>" . $value['prezzo'] . " €</td><tr>";
            echo "<tr><td>$name[8]:</td><td>" . $value['colore'] . "</td><tr>";
            echo "<tr><td>$name[9]:</td><td>" . $value['numPorte'] . "</td><tr>";
            echo "<tr><td>$name[10]:</td><td>" . $value['annoInizioProd'] . "</td><tr>";
            echo "<tr><td>$name[11]:</td><td>" . $value['annoFineProd'] . "</td><tr>";
            echo "<tr><td>$name[12]:</td><td>" . $value['descrizione'] . "</td><tr>";
            echo "<tr><td>$name[13]:</td><td>" . $value['note'] . "</td><tr>";
        }
        echo "</table>";
    } elseif ($type == "manufacturers") {
        echo "<table class=\"text-center text-white altriUtenti table\">";
        foreach ($name as $key => $value) {
            echo "<th>$value</th>";
        }
        foreach ($data as $key => $value) {
            echo "<tr>";
            echo "<td>" . $value['nomeCasa'] . "</td>";
            echo "<td>" . $value['mail'] . "</td>";
            echo "<td>" . $value['sitoWeb'] . "</td>";
            echo "<td>" . $value['nazionalita'] . "</td>";
            echo "<td>" . $value['nomeFondatore'] . "</td>";
            echo "<td>" . $value['annoFondazione'] . "</td>";
            echo "<td>" . $value['descrizione'] . "</td>";
            echo "<td><form action=\"add_car_manufacturers.php\" method=\"post\"><button type=\"submit\" name=\"del\" class=\"btn\" value=\"$value[nomeCasa]\"><i style=\"color: red;\" class=\"fas fa-trash\"></i></button></form></td>";
            echo "</tr>";
        }
        echo "</table>";
    } elseif ($type == "seller_vehicle") {
        echo "<table border=\"1px\" class=\"table table-bordered text-center text-white table\">";
        echo "<tr><td>$name[0]:</td><td>" . $data['nome'] . "</td><tr>";
        echo "<tr><td>$name[1]:</td><td>" . $data['cognome'] . "</td><tr>";
        echo "<tr><td>$name[2]:</td><td>" . $data['ragioneSociale'] . "</td><tr>";
        echo "<tr><td>$name[3]:</td><td>" . $data['telefono'] . "</td><tr>";
        echo "</table>";
    }
}

/**
 * Print an HTML-formatted table
 *
 * $columns parameter is needed to specify the name of
 * the colums
 *
 * $data parameter is the actual data as an array of arrays
 * 
 * $has_button if button support is enabled
 * $button_text is the text inside the button
 */
function array_to_horizontal_table(
    array $columns,
    array $data,
    bool $has_button = false,
    string $button_text = ''
) {
    $html = '
        <table class="table table-bordered text-white">
            <thead>
                <tr>';

    // Print colum names
    foreach ($columns as $column) {
        $html .= "<th>$column</th>";
    }

    $html .= '
            </tr>
        </thead>
        <tbody>';

    // Print the actual data
    foreach ($data as $tuple) {
        $html .= "<tr>";

        foreach ($tuple as $key => $value) {
            if ($key === 'button_url') {
                continue;
            }

            $html .= "<td>$value</td>";
        }

        if ($has_button) {
            $button_path = $tuple['button_url'];
            $html .= "<td><button class='btn btn-success redirect-button' data-url='$button_path'>$button_text</button></td>";
        }

        $html .= "<tr>";
    }

    $html .= '
            </tbody>
        </table>';

    return $html;
} 
