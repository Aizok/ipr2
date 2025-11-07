<?php
class CouponModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $stmt = $this->conn->query("SELECT * FROM coupons");
        $coupons = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return ($coupons && count($coupons) > 0) ? $coupons : null;
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM coupons WHERE id = ?");
        $stmt->execute([$id]);
        $coupon = $stmt->fetch(PDO::FETCH_ASSOC);

        return $coupon ? $coupon : null;
    }

    public function create($data) {
        $stmt = $this->conn->prepare("
            INSERT INTO coupons (code, discount_percent, valid_until)
            VALUES (:code, :discount_percent, :valid_until)
        ");

        $stmt->bindValue(':code', $data['code']);
        $stmt->bindValue(':discount_percent', $data['discount_percent'], PDO::PARAM_INT);
        $stmt->bindValue(':valid_until', $data['valid_until']);

        return $stmt->execute();
    }

    public function update($id, $data) {
        $stmt = $this->conn->prepare("
            UPDATE coupons
            SET code = :code, discount_percent = :discount_percent, valid_until = :valid_until
            WHERE id = :id
        ");

        $stmt->bindValue(':code', $data['code']);
        $stmt->bindValue(':discount_percent', $data['discount_percent'], PDO::PARAM_INT);
        $stmt->bindValue(':valid_until', $data['valid_until']);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        $ok = $stmt->execute();
        if (!$ok) return false;

        return $stmt->rowCount() > 0;
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM coupons WHERE id = ?");
        $ok = $stmt->execute([$id]);
        if (!$ok) return false;

        return $stmt->rowCount() > 0;
    }
}