<?php

include_once __DIR__ . "/config.php";

// Helper function to connect to database or die if an error occurs during connection
function database_connect_with_config_credentials(): mysqli
{
    $connection_status = mysqli_connect(
        DATABASE_HOSTNAME,
        DATABASE_USERNAME,
        DATABASE_PASSWORD,
        DATABASE_NAME,
        DATABASE_PORT
    );

    if (false === $connection_status) {
        die('Cannot connect to MySQL server');
    }

    return $connection_status;
}


// Helper function to fetch data into PHP's builtin array
function database_fetch_as_array_and_close($result, mysqli $connection): array
{
    // Obtain data
    $data = array();

    if (false === $result) {
        return $data;
    }

    while ($row = mysqli_fetch_assoc($result)) {
        array_push($data, $row);
    }

    // Close database connection
    mysqli_close($connection);

    return $data;
}


// Perform connection, prepare, execute and fetch query, then return
// data as array
function database_execute_fetch_and_close(string $query): array
{
    $connection = database_connect_with_config_credentials();
    $stmt = mysqli_prepare($connection, $query);

    if (false === $stmt) {
        database_error_and_die();
    }

    $status = mysqli_stmt_execute($stmt);

    if (false === $status) {
        database_error_and_die();
    }

    $result = mysqli_stmt_get_result($stmt);

    return database_fetch_as_array_and_close($result, $connection);
}

function database_error_and_die()
{
    die('Database error');
}
