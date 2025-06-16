<?php
require_once __DIR__ . '/../models/ServerModel.php';
require_once __DIR__ . '/PortService.php';
require_once __DIR__ . '/../utils/config.php';

Config::load();

class ServerService
{
    private $serverModel;
    private $portService;

    public function __construct($pdo)
    {
        $this->serverModel = new ServerModel($pdo);
        $this->portService = new PortService($pdo);
    }

    public function getServersWithStatus()
    {
        $servers = $this->serverModel->getAllServers();
        if (isset($servers['error'])) {
            return $servers;
        }

        foreach ($servers as &$server) {
            $ports = $this->portService->getPortsByServer((int)$server['id']);
            $server['ports'] = $ports;
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

        $ports = $this->portService->getPortsByServer((int)$id);
        $server['ports'] = $ports;

        return $server;
    }


    public function addServer(array $data)
    {
        $result = $this->serverModel->insertServer($data);

        if (isset($result['error'])) {
            return $result;
        }

        $serverId = $result['server_id'] ?? null;

        if (!empty($data['ports']) && is_array($data['ports']) && $serverId) {
            foreach ($data['ports'] as $key => $portVal) {
                $portData = is_array($portVal)
                    ? $portVal
                    : ['port_number' => $portVal];

                $portData['server_id'] = $serverId;

                $portInsert = $this->portService->addPorts(['ports' => [$portData], 'server_id' => $serverId]);

                if (isset($portInsert['error'])) {
                    return [
                        "message" => "Server added, but one or more ports failed.",
                        "server_id" => $serverId,
                        "port_error" => $portInsert
                    ];
                }
            }
        }

        return $result;
    }


    public function editServer(int $id, array $data)
    {
        $cs = $this->getServerByIdWithStatus($id);

        if (isset($cs['error'])) {
            return $cs;
        }

        $updateResult = $this->serverModel->updateServer($id, $data);

        // Başarısız güncelleme varsa direk dön
        if (isset($updateResult['error'])) {
            return $updateResult;
        }

        $portResults = [];

        if (isset($data['ports']) && is_array($data['ports'])) {
            $existingPorts = $this->portService->getPortsByServer($id);
            $existingPortNumbers = array_map(fn($p) => (int)$p['port_number'], $existingPorts);
            $newPorts = array_map('intval', $data['ports']);

            $removedPorts = array_diff($existingPortNumbers, $newPorts);
            $addedPorts = array_diff($newPorts, $existingPortNumbers);

            if (!empty($addedPorts)) {
                $addResult = $this->portService->addPorts([
                    'server_id' => $id,
                    'ports' => array_values($addedPorts)
                ]);

                if (isset($addResult['error'])) {
                    $portResults['add_error'] = $addResult['error'];
                } else {
                    $portResults['added_ports'] = array_values($addedPorts);
                }
            }

            if (!empty($removedPorts)) {
                $deleteResult = $this->portService->deletePortByServerAndNumber($id, array_values($removedPorts));

                if (isset($deleteResult['error'])) {
                    $portResults['delete_error'] = $deleteResult['error'];
                } else {
                    $portResults['removed_ports'] = array_values($removedPorts);
                }
            }
        }

        return array_merge($updateResult, $portResults);
    }




    public function deleteServer($serverId)
    {
        return $this->serverModel->deleteServer($serverId);
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

            $currentTime = date('Y-m-d H:i:s');

            $lastChecks[] = [
                'time' => $currentTime,
                'status' => $status
            ];

            if (count($lastChecks) > 10) {
                array_shift($lastChecks);
            }

            $this->serverModel->updateStatus($id, $status, json_encode($lastChecks), $location);
        }
    }
}
