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
        "notifications/count" => "NotificationController@notificationCountAction",
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
        "ports/edit" => "PortController@editPortStatus",

        // Notifications
        "notifications/mark-all-read" => "NotificationController@markAsReadAll",
        "notifications/read" => "NotificationController@markAsRead",
    ],
    "DELETE" => [
        // Servers
        "servers/delete" => "ServerController@deleteServer",

        // Ports
        "ports/delete" => "PortController@deletePort",

        // Notifications
        "notifications" => "NotificationController@deleteNotification",
        "notifications/server" => "NotificationController@deleteNotificationsByServerId",
        "notifications/delete-old" => "NotificationController@deleteOldNotifications",
        "notifications/delete-all" => "NotificationController@deleteAllNotifications",
        "notifications/type" => "NotificationController@deleteNotificationsByType",
    ]
];
