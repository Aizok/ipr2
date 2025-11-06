<?php
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/config/Auth.php';
require_once __DIR__ . '/models/CouponModel.php';

$database = new Database();
$db = $database->getConnection();

$auth = new Auth($db);
$auth->checkApiKey();

$coupon = new CouponModel($db);

$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];

$path = explode('/', trim(parse_url($requestUri, PHP_URL_PATH), '/'));

$apiIndex = array_search('api', $path);

$entity = null;
$id = null;

if ($apiIndex !== false) {
    $entity = $path[$apiIndex + 1] ?? null; 
    $id = $path[$apiIndex + 2] ?? null;     
}

if ($entity !== 'coupons') {
    http_response_code(404);
    echo json_encode(["error" => "Unknown endpoint"]);
    exit;
}

switch ($requestMethod) {
    case 'GET':
        if ($id) {
            $data = $coupon->getById($id);
            if ($data) {
                echo json_encode($data);
            } else {
                http_response_code(404);
                echo json_encode(["error" => "Coupon not found"]);
            }
        } else {
            $data = $coupon->getAll();
            if ($data) {
                echo json_encode($data);
            } else {
                echo json_encode(["message" => "No coupons found"]);
            }
        }
        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || empty($input['code']) || !isset($input['discount_percent']) || empty($input['valid_until'])) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid input data"]);
            break;
        }
        if ($coupon->create($input)) {
            http_response_code(201);
            echo json_encode(["message" => "Coupon created successfully"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Failed to create coupon"]);
        }
        break;

    case 'PUT':
        if (!$id) {
            http_response_code(400);
            echo json_encode(["error" => "Missing coupon ID"]);
            break;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || empty($input['code']) || !isset($input['discount_percent']) || empty($input['valid_until'])) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid input data"]);
            break;
        }
        $updated = $coupon->update($id, $input);
        if ($updated) {
            echo json_encode(["message" => "Coupon updated successfully"]);
        } else {
            $exists = $coupon->getById($id);
            if ($exists) {
                http_response_code(500);
                echo json_encode(["error" => "Failed to update coupon"]);
            } else {
                http_response_code(404);
                echo json_encode(["error" => "Coupon not found"]);
            }
        }
        break;

    case 'DELETE':
        if (!$id) {
            http_response_code(400);
            echo json_encode(["error" => "Missing coupon ID"]);
            break;
        }
        $deleted = $coupon->delete($id);
        if ($deleted) {
            echo json_encode(["message" => "Coupon deleted successfully"]);
        } else {
            $exists = $coupon->getById($id);
            if ($exists) {
                http_response_code(500);
                echo json_encode(["error" => "Failed to delete coupon"]);
            } else {
                http_response_code(404);
                echo json_encode(["error" => "Coupon not found"]);
            }
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
        break;
}
