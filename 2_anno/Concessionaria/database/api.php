<?php

include_once __DIR__ . "/utils.php";


// Fetches all vehicles with their model details from the database.
// The function constructs a SQL query to join the `veicolo` and `modello_veicolo` tables
// on the `modello` field, selecting various fields such as model name, license plate, manufacturer,
// year of registration, price, and version. It then executes the query and fetches the results.
function database_fetch_vehicles(): array
{
    $query = "
        SELECT
            `nomeModello`, `targa`, `nomeCasa`,  `annoImmatricolazione`, `prezzo`, `versione`
        FROM `veicolo`
        JOIN `modello_veicolo`
        ON `veicolo`.`modello` = `modello_veicolo`.`nomeModello`;";

    return database_execute_fetch_and_close($query);
}


// Fetches a specific vehicle by its license plate.
// The function connects to the database, prepares a SQL statement to select all columns
// from the `veicolo` and `modello_veicolo` tables where the license plate matches the input parameter.
// It then executes the statement, fetches the results, and returns the vehicle details.
function database_fetch_vehicle_by_license_plate(string $license_plate): array
{
    $connection = database_connect_with_config_credentials();

    $query = '
        SELECT *
        FROM `veicolo`
        JOIN `modello_veicolo`
        ON `veicolo`.`modello` = `modello_veicolo`.`nomeModello`
        WHERE `veicolo`.`targa` = ?
    ';

    $stmt = mysqli_prepare($connection, $query);

    if (false === $stmt) {
        database_error_and_die();
    }

    mysqli_stmt_bind_param($stmt, 's', $license_plate);

    $status = mysqli_stmt_execute($stmt);

    if (false === $status) {
        database_error_and_die();
    }

    $result = mysqli_stmt_get_result($stmt);

    return database_fetch_as_array_and_close($result, $connection);
}


// Adds a new manufacturer to the database.
// This function takes multiple parameters representing the manufacturer's details such as name,
// email, website, country, founder name, foundation year, and description. It connects to the database,
// prepares an SQL insert statement, binds the parameters to the statement, and executes it to add
// the manufacturer to the `casa_automobilistica` table.
function database_add_manufacturer(
    string $nome,
    string $mail,
    string $website,
    string $country,
    string $founder_name,
    int $foundation_year,
    string $description
) {

    $connection = database_connect_with_config_credentials();

    $query = '
        INSERT INTO `casa_automobilistica`
        (`nomeCasa`, `mail`, `sitoWeb`, `nazionalita`, `nomeFondatore`, `annoFondazione`, `descrizione`)
        VALUES (?, ?, ?, ?, ?, ?, ?);
    ';

    $stmt = mysqli_prepare($connection, $query);

    if (false === $stmt) {
        database_error_and_die();
    }

    mysqli_stmt_bind_param(
        $stmt,
        'sssssis',
        $nome,
        $mail,
        $website,
        $country,
        $founder_name,
        $foundation_year,
        $description
    );

    $status = mysqli_stmt_execute($stmt);

    if (false === $status) {
        database_error_and_die();
    }

    $result = mysqli_stmt_get_result($stmt);

    database_fetch_as_array_and_close($result, $connection);
}


// Fetches all manufacturers from the database.
// This function constructs a simple SQL query to select all fields from the `casa_automobilistica` table.
// It executes the query and fetches the results, returning an array of manufacturer details.
function database_fetch_manufacturers()
{
    $query = "SELECT * FROM `casa_automobilistica`";
    return database_execute_fetch_and_close($query);
}


// Fetches the list of car sellers from the database.
// The function constructs a SQL query to select various fields such as seller ID, type, name, surname,
// fiscal code, business name, VAT number, and phone number from the `venditore` table. It then executes the query
// and fetches the results, returning an array of seller details.
function database_fetch_car_seller_list(): array
{
    $query = "
        SELECT
            `idVenditore`,
            `tipoVenditore`,
            `nome`,
            `cognome`,
            `codFiscale`,
            `ragioneSociale`,
            `pIVA`,
            `telefono`
        FROM
            `venditore`";

    return database_execute_fetch_and_close($query);
}


// Fetches a specific car seller by their ID.
// This function connects to the database, prepares a SQL statement to select various fields from the `venditore` table
// where the seller ID matches the input parameter. It then executes the statement, fetches the results,
// and returns the details of the specific seller.
function database_fetch_car_seller(int $seller_id): array
{
    $connection = database_connect_with_config_credentials();

    $query = "
        SELECT
            `tipoVenditore`,
            `nome`,
            `cognome`,
            `codFiscale`,
            `ragioneSociale`,
            `pIVA`,
            `telefono`
        FROM
            `venditore`
        WHERE `venditore`.`idVenditore` = ?";

    $stmt = mysqli_prepare($connection, $query);

    if (false === $stmt) {
        database_error_and_die();
    }

    mysqli_stmt_bind_param($stmt, 'i', $seller_id);

    $status = mysqli_stmt_execute($stmt);

    if (false === $status) {
        database_error_and_die();
    }

    $result = mysqli_stmt_get_result($stmt);

    return database_fetch_as_array_and_close($result, $connection)[0];
}


// Fetches a car seller by a specific vehicle's license plate.
// The function connects to the database, prepares a SQL statement to join the `venditore` and `vendere` tables,
// selecting various seller details where the vehicle's license plate matches the input parameter.
// It then executes the statement, fetches the results, and returns the seller details.
function database_fetch_car_seller_by_plate(string $plate): array
{
    $connection = database_connect_with_config_credentials();

    $query = "
        SELECT
            `tipoVenditore`,
            `nome`,
            `cognome`,
            `codFiscale`,
            `ragioneSociale`,
            `pIVA`,
            `telefono`
        FROM
            `venditore`
        JOIN `vendere` ON `vendere`.`idVenditore` = `venditore`.`idVenditore`
        WHERE `vendere`.`targa` = ?";

    $stmt = mysqli_prepare($connection, $query);

    if (false === $stmt) {
        database_error_and_die();
    }

    mysqli_stmt_bind_param($stmt, 's', $plate);

    $status = mysqli_stmt_execute($stmt);

    if (false === $status) {
        database_error_and_die();
    }

    $result = mysqli_stmt_get_result($stmt);

    return database_fetch_as_array_and_close($result, $connection)[0];
}


// Updates a car seller's details in the database.
// This function takes several parameters representing the seller's details such as seller ID, fiscal code, seller type,
// name, surname, business name, VAT number, and phone number. It connects to the database, prepares a SQL update statement,
// binds the provided parameters to the statement, and executes it to update the seller's information in the `venditore` table.
// If the execution fails, an error is triggered. Finally, the updated seller details are fetched and returned.
function database_update_car_seller(
    int $seller_id,
    string $fiscal_code,
    string $seller_type,
    string $name,
    string $surname,
    string $business_name,
    string $vat_number,
    string $phone_number
) {
    $connection = database_connect_with_config_credentials();

    $query = "
        UPDATE
            `venditore`
        SET
            `codFiscale` = ?,
            `tipoVenditore` = ?,
            `nome` = ?,
            `cognome` = ?,
            `ragioneSociale` = ?,
            `pIVA` = ?,
            `telefono` = ?
        WHERE
            `venditore`.`idVenditore` = ?";

    $stmt = mysqli_prepare($connection, $query);

    if (false === $stmt) {
        database_error_and_die();
    }

    mysqli_stmt_bind_param(
        $stmt,
        'sssssssi',
        $fiscal_code,
        $seller_type,
        $name,
        $surname,
        $business_name,
        $vat_number,
        $phone_number,
        $seller_id
    );

    $status = mysqli_stmt_execute($stmt);

    if (false === $status) {
        database_error_and_die();
    }

    $result = mysqli_stmt_get_result($stmt);

    database_fetch_as_array_and_close($result, $connection);

    return database_fetch_car_seller($seller_id);
}


// Deletes a manufacturer from the database by its name.
// This function takes the manufacturer's name as a parameter, connects to the database,
// prepares a SQL delete statement, binds the manufacturer name to the statement, and executes it
// to remove the manufacturer from the `casa_automobilistica` table. If the execution fails, an error is triggered.
// Finally, the result is fetched and closed.
function database_delete_manufacturer($manufacturer_name)
{
    $connection = database_connect_with_config_credentials();

    $query = "DELETE FROM `casa_automobilistica` WHERE `casa_automobilistica`.`nomeCasa` = ?";

    $stmt = mysqli_prepare($connection, $query);

    if (false === $stmt) {
        database_error_and_die();
    }

    mysqli_stmt_bind_param($stmt, 's', $manufacturer_name);

    $status = mysqli_stmt_execute($stmt);

    if (false === $status) {
        database_error_and_die();
    }

    $result = mysqli_stmt_get_result($stmt);

    database_fetch_as_array_and_close($result, $connection);
}


// Searches for vehicles in the database based on a search query.
// This function connects to the database, prepares a SQL query to join the `veicolo` and `modello_veicolo` tables,
// and searches for vehicles where the license plate, color, notes, model, version, model name, or manufacturer name
// matches the search query. The search query is constructed using SQL LIKE statements with wildcards to allow partial matches.
// The statement is executed, the results are fetched, and an array of matching vehicles is returned.
function database_search_vehicle($searchQuery): array
{
    $connection = database_connect_with_config_credentials();

    $query = "
        SELECT
            `nomeModello`, `targa`, `nomeCasa`,  `annoImmatricolazione`, `prezzo`, `versione`
        FROM `veicolo`
        JOIN `modello_veicolo`
        ON `veicolo`.`modello` = `modello_veicolo`.`nomeModello`
        WHERE `veicolo`.`targa` LIKE ?
        OR `veicolo`.`colore` LIKE ?
        OR `veicolo`.`note` LIKE ?
        OR `veicolo`.`modello` LIKE ?
        OR `veicolo`.`versione` LIKE ?
        OR `modello_veicolo`.`nomeModello` LIKE ?
        OR `modello_veicolo`.`nomeCasa` LIKE ?;";

    $stmt = mysqli_prepare($connection, $query);

    if (false === $stmt) {
        database_error_and_die();
    }

    $searchQuery = "%" . $searchQuery . "%";

    mysqli_stmt_bind_param(
        $stmt,
        'sssssss',
        $searchQuery,
        $searchQuery,
        $searchQuery,
        $searchQuery,
        $searchQuery,
        $searchQuery,
        $searchQuery
    );

    $status = mysqli_stmt_execute($stmt);

    if (false === $status) {
        database_error_and_die();
    }

    $result = mysqli_stmt_get_result($stmt);

    return database_fetch_as_array_and_close($result, $connection);
}
