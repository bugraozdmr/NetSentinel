<?php

return [
    "GET" => [
        // Servers
        "servers" => "ServerController@getServers",
        "server" => "ServerController@getServer",
        "check" => "ServerController@checkAll",

        // Ports
        "ports" => "PortController@getPorts",

        // Notifications
        "notifications" => "NotificationController@getNotifications",
        "notifications/server" => "NotificationController@getNotificationsByServerId",
    ],
    "POST" => [
        // Servers
        "servers" => "ServerController@addServer",

        // Ports
        "ports" => "PortController@addPort",

        // Notifications
        "notifications" => "NotificationController@addNotification",
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

        // Notifications
        "notifications" => "NotificationController@deleteNotification",
    ]
];
