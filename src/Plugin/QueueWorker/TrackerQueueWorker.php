<?php

namespace Drupal\minitrack\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\Annotation\QueueWorker;

/**
 * @QueueWorker(
 *   id = "minitrack_events",
 *   title = @Translation("Minitrack event processor"),
 *   cron = {"time" = 60}
 * )
 */
class TrackerQueueWorker extends QueueWorkerBase {
  public function processItem($data) {
    // Basic processing: write to DB table. Keep minimal for now.
    try {
      $connection = \Drupal::database();
      $time = \Drupal::time()->getRequestTime();
      $ts = isset($data['ts']) ? (int) $data['ts'] : $time;
      $connection->insert('minitrack_pageview')
        ->fields([
          'session_id' => isset($data['session']) ? substr($data['session'], 0, 128) : NULL,
          'path' => isset($data['path']) ? $data['path'] : NULL,
          'host' => isset($data['host']) ? $data['host'] : NULL,
          'timestamp' => $ts,
          'data' => isset($data['meta']) ? json_encode($data['meta']) : json_encode($data),
        ])
        ->execute();
    }
    catch (\Exception $e) {
      \Drupal::logger('minitrack')->error($e->getMessage());
      throw $e;
    }
  }
}
