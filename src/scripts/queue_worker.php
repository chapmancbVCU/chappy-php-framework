<?php
require __DIR__.'/vendor/autoload.php';

use Core\Lib\Queue\QueueManager;

// Load config
$config = require __DIR__.'/config/queue.php';

// Init manager
$queue = new QueueManager($config);

// Worker loop
while (true) {
    $job = $queue->pop('default');
    if ($job) {
        try {
            // Do something with $job['payload']
            echo "Processing job: ".json_encode($job['payload']).PHP_EOL;

            // Mark as done
            if ($job['id']) {
                $queue->delete($job['id']);
            }
        } catch (\Exception $e) {
            echo "Job failed: ".$e->getMessage().PHP_EOL;
            // Could requeue with delay or track failure
        }
    }
    usleep(500000); // wait 0.5s before polling again
}
