<?php
declare(strict_types=1);
namespace Core\Lib\Queue;

use PDO;
use Predis\Client;

class QueueManager {
    protected QueueDriverInterface $driver;

    public function __construct(array $config) {
        if ($config['driver'] === 'database') {
            $pdo = new PDO($config['database']['dsn'], $config['database']['username'], $config['database']['password']);
            $this->driver = new DatabaseQueueDriver($pdo);
        } elseif ($config['driver'] === 'redis') {
            $redis = new Predis([
                'scheme' => 'tcp',
                'host' => $config['redis']['host'],
                'port' => $config['redis']['port'],
            ]);
        } else {
            throw new \Exception("Unsupported driver: " . $config['driver']);
        }
    }

    public function push(string $queue, array $payload): void {
        $this->driver->push($queue, $payload);
    }

    public function pop(string $queue): ?array {
        return $this->driver->pop($queue);
    }

    public function release(string $queue, array $payload, int $delay = 0): void {
        $this->driver->release($queue, $payload, $delay);
    }

    public function delete($jobId): void {
        $this->driver->delete($jobId);
    }
}