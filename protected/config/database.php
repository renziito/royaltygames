<?php

if ($_SERVER['SERVER_NAME'] == "localhost") {
    $config = array(
        'connectionString' => 'mysql:host=localhost;dbname=db_royaltygames',
        'emulatePrepare' => true,
        'username' => 'royaltyUser',
        'password' => 'us3rp4ssw0rd',
        'charset' => 'utf8',
    );
} else {
    $config = array(
        'connectionString' => 'mysql:host=localhost;dbname=jackpot',
        'emulatePrepare' => true,
        'username' => 'root',
        'password' => 'xH$c9*M6sfAh',
        'charset' => 'utf8',
    );
}
return $config;
