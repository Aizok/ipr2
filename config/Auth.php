<?php
class Auth {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function checkApiKey() {
        $headers = getallheaders();

        $normalizedHeaders = [];
        foreach ($headers as $key => $value) {
            $normalizedHeaders[strtolower($key)] = $value;
        }

        $apiKey = $normalizedHeaders['x-api-key'] ?? null;

        if (!$apiKey) {
            http_response_code(401);
            echo json_encode(["error" => "API key is missing"]);
            exit;
        }

        $query = "SELECT * FROM api_keys WHERE api_key = :api_key AND is_active = 1 LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":api_key", $apiKey);
        $stmt->execute();

        if ($stmt->rowCount() === 1) {
            return true;
        } else {
            http_response_code(401);
            echo json_encode(["error" => "Invalid API key"]);
            exit;
        }
    }
}
?>
