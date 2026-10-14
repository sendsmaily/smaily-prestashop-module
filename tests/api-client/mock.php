<?php
$auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$user = $_SERVER['PHP_AUTH_USER'] ?? null; $pass = $_SERVER['PHP_AUTH_PW'] ?? null;
if ($user === null && str_starts_with($auth, 'Basic ')) { [$user, $pass] = explode(':', base64_decode(substr($auth, 6)), 2) + [null, null]; }
$echo = ['method' => $_SERVER['REQUEST_METHOD'], 'uri' => $_SERVER['REQUEST_URI'], 'user' => $user,
  'content_type' => $_SERVER['CONTENT_TYPE'] ?? ($_SERVER['HTTP_CONTENT_TYPE'] ?? null), 'body' => file_get_contents('php://input')];
header('Content-Type: application/json');
if ($user !== 'user' || $pass !== 'pass') { http_response_code(401); echo json_encode(['code' => 401, 'message' => 'Unauthorized']); exit; }
echo json_encode(['code' => 101, 'echo' => $echo]);
