<?php

return [
    "GET" => [
        "servers" => "ServerController@getServers",
        "server" => "ServerController@getServer",
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
