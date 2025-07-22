<?php
declare(strict_types=1);
namespace Core\Lib\Queue;

use PDO;

class DatabaseQueueDriver implements QueueDriverInterface {
    protected PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function push(string $queue, array $payload): void {
        $stmt = $this->pdo->prepare(
            "INSERT INTO jobs (queue,payload,available_at) VALUES (?,?,NOW())"
        );
        $stmt->execute([$queue, json_encode($payload)]);
    }

    public function pop(string $queue): ?array {
        // Begin transaction to safely reserve job
        $this->pdo->beginTransaction();
        $stmt = $this->pdo->prepare(
            "SELECT * FROM jobs WHERE queue=? AND reserved_at IS NULL AND available_at<=NOW() ORDER BY id LIMIT 1 FOR UPDATE"
        );
        $stmt->execute([$queue]);
        $job = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($job) {
            $this->pdo->prepare("UPDATE jobs SET reserved_at=NOW() WHERE id=?")
                      ->execute([$job['id']]);
            $this->pdo->commit();
            return ['id'=>$job['id'], 'payload'=>json_decode($job['payload'], true)];
        }
        $this->pdo->commit();
        return null;
    }

    public function release(string $queue, array $payload, int $delay = 0): void {
        $availableAt = date('Y-m-d H:i:s', time() + $delay);
        $stmt = $this->pdo->prepare(
            "INSERT INTO jobs (queue,payload,available_at) VALUES (?,?,?)"
        );
        $stmt->execute([$queue, json_encode($payload), $availableAt]);
    }

    public function delete($jobId): void {
        $stmt = $this->pdo->prepare("DELETE FROM jobs WHERE id=?");
        $stmt->execute([$jobId]);
    }
}