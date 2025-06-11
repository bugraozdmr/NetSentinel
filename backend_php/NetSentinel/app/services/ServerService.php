<?php
require_once __DIR__ . '/../models/ServerModel.php';
require_once __DIR__ . '/../utils/config.php';

Config::load();

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
            return $servers;
        }

        return $servers;
    }

    public function getServerByIdWithStatus($id)
    {
        $server = $this->serverModel->getServerById($id);

        if (isset($server['error'])) {
            return $server;
        }

        if (!$server) {
            return ["error" => "Sunucu bulunamadı"];
        }

        return $server;
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

    public function checkStatus()
    {
        return $this->serverModel->checkStatus();
    }

    public function checkAllStatuses()
    {
        $servers = $this->serverModel->getAllServersForStatus();

        $processes = [];
        $pipesList = [];

        $pingScript = realpath(__DIR__ . '/../utils/ping.php');

        foreach ($servers as $index => $server) {
            $pipes = [];
            $cmd = escapeshellcmd(Config::getPhpPath()) . ' ' . escapeshellarg($pingScript) . ' ' . escapeshellarg($server['ip']);


            $processes[$index] = proc_open(
                $cmd,
                [
                    1 => ['pipe', 'w'],
                    2 => ['pipe', 'w'],
                ],
                $pipes
            );

            if (is_resource($processes[$index])) {
                $pipesList[$index] = $pipes;
            } else {
                $pipesList[$index] = null;
            }
        }

        foreach ($processes as $index => $proc) {
            $pipes = $pipesList[$index];
            if (!$pipes) continue;

            $output = stream_get_contents($pipes[1]);
            $errorOutput = stream_get_contents($pipes[2]);

            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($proc);

            if (trim($errorOutput)) {
                file_put_contents(__DIR__ . '/../../ping_errors.log', "Server #{$index}: $errorOutput\n", FILE_APPEND);
            }

            $status = (trim($output) === '1') ? 1 : 0;

            $server = $servers[$index];
            $id = $server['id'];

            $location = $server['location'];

            $lastChecks = json_decode($server['last_checks'], true);
            if (!is_array($lastChecks)) {
                $lastChecks = [];
            }

            $lastChecks[] = $status;
            if (count($lastChecks) > 10) {
                array_shift($lastChecks);
            }

            $this->serverModel->updateStatus($id, $status, json_encode($lastChecks), $location);
        }
    }
}
