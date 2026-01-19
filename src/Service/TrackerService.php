<?php

namespace Drupal\minitrack\Service;

use Drupal\Core\Database\Connection;
use Drupal\Core\Config\ConfigFactoryInterface;
use Psr\Log\LoggerInterface;
use Drupal\Core\Queue\QueueFactory;

class TrackerService {
  protected $database;
  protected $configFactory;
  protected $logger;
  protected $queueFactory;

  public function __construct(Connection $database, ConfigFactoryInterface $config_factory, LoggerInterface $logger, QueueFactory $queue_factory) {
    $this->database = $database;
    $this->configFactory = $config_factory;
    $this->logger = $logger;
    $this->queueFactory = $queue_factory;
  }

  public function enqueueEvents($payload) {
    // Normalize payload to array of events.
    $events = [];
    if (isset($payload['events']) && is_array($payload['events'])) {
      $events = $payload['events'];
    }
    elseif (is_array($payload) && isset($payload['type'])) {
      $events = [$payload];
    }
    else {
      throw new \InvalidArgumentException('Payload must be an event object or {events: []}');
    }

    // Limit number of events per request
    $max = 50;
    if (count($events) === 0 || count($events) > $max) {
      throw new \InvalidArgumentException('Invalid number of events');
    }

    // Whitelist fields to avoid storing unexpected data
    $allowed = ['type','path','host','ts','session','visitor','referrer','title','meta'];

    $queue = $this->queueFactory->get('minitrack_events');
    foreach ($events as $event) {
      if (!is_array($event)) { continue; }
      $clean = [];
      foreach ($allowed as $k) {
        if (isset($event[$k])) { $clean[$k] = $event[$k]; }
      }
      // Add received_at
      $clean['_received'] = time();
      $queue->createItem($clean);
    }
    return TRUE;
  }
}
