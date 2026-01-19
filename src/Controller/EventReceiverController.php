<?php

namespace Drupal\minitrack\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;

class EventReceiverController extends ControllerBase {
  public function receive(Request $request) {
    // Handle CORS preflight
    if ($request->getMethod() === 'OPTIONS') {
      $response = new Response();
      $response->setStatusCode(200);
      $response->headers->set('Access-Control-Allow-Methods', 'POST, OPTIONS');
      $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, X-Minitrack-Key');
      $response->headers->set('Access-Control-Allow-Origin', $this->getAllowedOrigin($request));
      return $response;
    }

    $content = $request->getContent();
    if (empty($content) || strlen($content) > 1024 * 64) {
      return new JsonResponse(['error' => 'Empty or too large payload'], 413);
    }

    $data = json_decode($content, TRUE);
    if ($data === NULL) {
      return new JsonResponse(['error' => 'Invalid JSON'], 400);
    }

    // Basic API key check (supports multiple keys in config)
    $provided = $request->headers->get('X-Minitrack-Key');
    $config = \Drupal::config('minitrack.settings');
    $keys = (array) $config->get('api_keys');
    // If keys are configured, require a matching provided key.
    if (!empty($keys)) {
      if (empty($provided) || !in_array($provided, $keys, TRUE)) {
        return new JsonResponse(['error' => 'Unauthorized'], 401);
      }
    }

    // Apply CORS allow-origin header for accepted origins
    $origin = $this->getAllowedOrigin($request);

    // Enqueue events
    $tracker = \Drupal::service('minitrack.tracker');
    try {
      $tracker->enqueueEvents($data);
    }
    catch (\InvalidArgumentException $e) {
      $resp = new JsonResponse(['error' => $e->getMessage()], 400);
      if ($origin) { $resp->headers->set('Access-Control-Allow-Origin', $origin); }
      return $resp;
    }
    catch (\Exception $e) {
      \Drupal::logger('minitrack')->error($e->getMessage());
      $resp = new JsonResponse(['error' => 'Server error'], 500);
      if ($origin) { $resp->headers->set('Access-Control-Allow-Origin', $origin); }
      return $resp;
    }

    $resp = new JsonResponse(['status' => 'ok']);
    if ($origin) { $resp->headers->set('Access-Control-Allow-Origin', $origin); }
    return $resp;
  }

  protected function getAllowedOrigin(Request $request) {
    $origin = $request->headers->get('Origin') ?: $request->headers->get('Referer');
    if (!$origin) { return NULL; }
    $config = \Drupal::config('minitrack.settings');
    $allowed = (array) $config->get('cors_origins');
    foreach ($allowed as $a) {
      $a = trim($a);
      if (empty($a)) { continue; }
      // Allow exact matches or wildcard prefix
      if ($a === $origin || (substr($a, -1) === '*' && strpos($origin, rtrim($a, '*')) === 0)) {
        return $a === '*' ? '*' : $origin;
      }
    }
    return NULL;
  }
}
