<?php

$di->set('config', function() {
    return array(
        'db' => array(
            'host' => '127.0.0.1',
            'username' => 'root',
            'password' => 'vagrant',
            'dbname' => 'primus',
        ),
        'http' => array(
            'port' => 8888,
            'ip' => '0.0.0.0',
        )
    );
});