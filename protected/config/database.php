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
        'username' => 'username',
        'password' => 'wordPa$$1',
        'charset' => 'utf8',
    );
}
return $config;
