<?php

return [
    "GET" => [
        "servers" => "ServerController@getServers",
    ],
    "POST" => [
        "servers" => "ServerController@addServer",
    ],
    "PUT" => [
        "servers/edit" => "ServerController@editServer",
    ],
    "DELETE" => [
        "servers/{id}" => "ServerController@deleteServer",
    ]
];
