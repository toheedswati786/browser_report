<?php
$cfg = require __DIR__ . '/config.php';
$dsn = "mysql:host={$cfg['db_host']};dbname={$cfg['db_name']};charset={$cfg['db_charset']}";
$pdo = new PDO($dsn, $cfg['db_user'], $cfg['db_pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$q = $_GET['q'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$per = 25;
$offset = ($page - 1) * $per;
$params = [];
$where = '';

if ($q) {
  $where = "WHERE user_agent LIKE :q OR public_ip LIKE :q OR address LIKE :q";
  $params[':q'] = "%$q%";
}

$total = $pdo->prepare("SELECT COUNT(*) FROM reports $where");
$total->execute($params);
$totalCount = $total->fetchColumn();

$stmt = $pdo->prepare("SELECT * FROM reports $where ORDER BY created_at DESC LIMIT :off, :per");
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':off', (int)$offset, PDO::PARAM_INT);
$stmt->bindValue(':per', (int)$per, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

function esc($s) {
  return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin — Browser Reports</title>
<style>
  body {font-family: Arial, sans-serif; background:#f6f8fb; padding:20px;}
  table {width:100%; border-collapse:collapse;}
  th, td {padding:8px; border:1px solid #ccc; font-size:13px;}
  th {background:#eef2ff;}
  pre {white-space:pre-wrap; word-break:break-all;}
</style>
</head>
<body>
<h1>Browser Reports</h1>
<form method="get">
  <input type="text" name="q" placeholder="Search IP, Address..." value="<?=esc($q)?>">
  <button>Search</button>
  <button type="button" onclick="window.print()">Print</button>
</form>
<br>
<table>
  <tr><th>ID</th><th>Time</th><th>IP</th><th>Coords</th><th>Address</th><th>User Agent</th><th>Hardware</th><th>Map</th></tr>
  <?php foreach ($rows as $r): ?>
  <tr>
    <td><?=esc($r['id'])?></td>
    <td><?=esc($r['created_at'])?></td>
    <td><?=esc($r['public_ip'])?></td>
    <td><?=esc($r['latitude']).', '.esc($r['longitude'])?></td>
    <td><?=nl2br(esc($r['address']))?></td>
    <td><pre><?=esc($r['user_agent'])?></pre></td>
    <td><pre><?=esc($r['hardware_json'])?></pre></td>
    <td>
      <?php if($r['latitude'] && $r['longitude']): ?>
        <a target="_blank" href="https://www.google.com/maps/search/?api=1&query=<?=urlencode($r['latitude'].','.$r['longitude'])?>">Open</a>
      <?php else: ?>—<?php endif; ?>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
</body>
</html>
