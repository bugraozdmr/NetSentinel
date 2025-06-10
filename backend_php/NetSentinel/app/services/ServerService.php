<?php
require_once __DIR__ . '/../models/ServerModel.php';

class ServerService
{
    private $serverModel;

    public function __construct($pdo)
    {
        $this->serverModel = new ServerModel($pdo);
    }

    public function getServersWithStatus()
    {
        $servers = $this->serverModel->getAllServers();
        if (isset($servers['error'])) {
            // Veritabanı hatası varsa direkt döndür
            return $servers;
        }

        foreach ($servers as &$server) {
            $server['status'] = $this->pingServer($server['ip']);
        }
        return $servers;
    }

    public function addServer(array $data)
    {
        return $this->serverModel->insertServer($data);
    }

    public function editServer(int $id, array $data)
    {
        return $this->serverModel->updateServer($id, $data);
    }


    public function deleteServer($serverId)
    {
        return $this->serverModel->deleteServer($serverId);
    }

    private function pingServer(string $ip, int $timeoutSeconds = 2): string
    {
        // -c 4 : 4 paket gönder (4sn)
        // -W timeoutSeconds : her paket için maksimum timeoutSeconds saniye bekle
        $output = shell_exec("ping -c 4 -W {$timeoutSeconds} {$ip} 2>&1");
        return strpos($output, '0% packet loss') !== false ? 'Active' : 'Passive';
    }
}
