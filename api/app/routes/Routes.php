<?php

return [
    "GET" => [
        // Servers
        "servers" => "ServerController@getServers",
        "servers/paginated" => "ServerController@getServersWithPagination",
        "servers/stats" => "ServerController@getServerStats",
        "server" => "ServerController@getServer",
        "check" => "ServerController@checkAll",
        "realtime" => "ServerController@getRealTimeUpdate",

        // Ports
        "ports" => "PortController@getPorts",

        // Notifications
        "notifications" => "NotificationController@getNotifications",
        "notifications/server" => "NotificationController@getNotificationsByServerId",
        "notifications/count" => "NotificationController@notificationCountAction",

        // Settings
        "settings" => "SettingsController@get",
    ],
    "POST" => [
        // Servers
        "servers" => "ServerController@addServer",

        // Ports
        "ports" => "PortController@addPort",

        // Notifications
        "notifications" => "NotificationController@addNotification",

        // Settings
        "settings" => "SettingsController@save",
        "settings/config" => "SettingsController@updateConfig",
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
