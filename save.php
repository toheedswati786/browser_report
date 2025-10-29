<?php
header('Content-Type: application/json; charset=utf-8');

try {
  $cfg = require __DIR__ . '/config.php';
  $dsn = "mysql:host={$cfg['db_host']};dbname={$cfg['db_name']};charset={$cfg['db_charset']}";
  $pdo = new PDO($dsn, $cfg['db_user'], $cfg['db_pass'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
  ]);

  $raw = file_get_contents('php://input');
  $data = json_decode($raw, true);
  if (!$data) throw new Exception('Invalid JSON payload');

  $stmt = $pdo->prepare("
    INSERT INTO reports 
    (user_agent, public_ip, latitude, longitude, accuracy, address, languages, time_zone, hardware_json, referrer, screen_info)
    VALUES (:ua, :ip, :lat, :lon, :acc, :addr, :lang, :tz, :hw, :ref, :screen)
  ");

  $stmt->execute([
    ':ua' => $data['userAgent'] ?? null,
    ':ip' => $data['publicIP'] ?? null,
    ':lat' => $data['geolocation']['lat'] ?? null,
    ':lon' => $data['geolocation']['lon'] ?? null,
    ':acc' => $data['geolocation']['accuracy'] ?? null,
    ':addr' => $data['address'] ?? null,
    ':lang' => $data['languages'] ?? null,
    ':tz' => $data['timeZone'] ?? null,
    ':hw' => isset($data['hardware']) ? json_encode($data['hardware']) : null,
    ':ref' => $data['referrer'] ?? null,
    ':screen' => json_encode($data['screenInfo'] ?? null)
  ]);

  echo json_encode(['ok' => true, 'id' => $pdo->lastInsertId()]);
} catch (Exception $e) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
