<?php

return [
    "GET" => [
        // Servers
        "servers" => "ServerController@getServers",
        "server" => "ServerController@getServer",
        "check" => "ServerController@checkAll",

        // Ports
        "ports" => "PortController@getPorts"
    ],
    "POST" => [
        // Servers
        "servers" => "ServerController@addServer",

        // Ports
        "ports" => "PortController@addPort",
    ],
    "PUT" => [
        // Servers
        "servers/edit" => "ServerController@editServer",

        // Ports
        "ports/edit" => "PortController@editPortStatus"
    ],
    "DELETE" => [
        // Servers
        "servers/delete" => "ServerController@deleteServer",

        // Ports
        "ports/delete" => "PortController@deletePort",
    ]
];
