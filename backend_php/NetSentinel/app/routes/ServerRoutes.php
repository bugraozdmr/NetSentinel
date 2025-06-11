<?php

return [
    "GET" => [
        "servers" => "ServerController@getServers",
        "server" => "ServerController@getServer",
        "status" => "ServerController@checkStatusAll",
        "check" => "ServerController@checkAll",
    ],
    "POST" => [
        "servers" => "ServerController@addServer",
    ],
    "PUT" => [
        "servers/edit" => "ServerController@editServer",
    ],
    "DELETE" => [
        "servers/delete" => "ServerController@deleteServer",
    ]
];
