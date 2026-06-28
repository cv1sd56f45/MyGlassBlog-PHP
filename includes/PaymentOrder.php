<?php
/**
 * MyGlassBlog PHP - 支付订单模型
 */
class PaymentOrder {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * 创建订单
     */
    public function create($data) {
        $sql = "INSERT INTO `payment_orders` (`order_no`, `gateway_type`, `pay_type`, `amount`, `payer_name`, `payer_contact`, `message`) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['order_no'],
            $data['gateway_type'],
            $data['pay_type'],
            $data['amount'],
            $data['payer_name'] ?? '',
            $data['payer_contact'] ?? '',
            $data['message'] ?? '',
        ]);
        return $this->db->lastInsertId();
    }
    
    /**
     * 根据订单号查询
     */
    public function getByOrderNo($order_no) {
        $sql = "SELECT * FROM `payment_orders` WHERE `order_no` = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$order_no]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * 更新订单状态
     */
    public function updateStatus($order_no, $status, $trade_no = '', $actual_amount = 0) {
        $sql = "UPDATE `payment_orders` SET `status` = ?, `trade_no` = ?, `actual_amount` = ?, `pay_time` = NOW() WHERE `order_no` = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$status, $trade_no, $actual_amount, $order_no]);
    }
    
    /**
     * 获取订单列表
     */
    public function getList($status = null, $limit = 50, $offset = 0) {
        $sql = "SELECT * FROM `payment_orders`";
        $params = [];
        
        if ($status !== null) {
            $sql .= " WHERE `status` = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY `created_at` DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * 获取统计信息
     */
    public function getStats() {
        $sql = "SELECT 
                    COUNT(*) as total_orders,
                    SUM(CASE WHEN `status` = 1 THEN 1 ELSE 0 END) as paid_orders,
                    SUM(CASE WHEN `status` = 1 THEN `amount` ELSE 0 END) as total_amount,
                    SUM(CASE WHEN `status` = 1 THEN `actual_amount` ELSE 0 END) as total_actual_amount
                FROM `payment_orders`";
        $stmt = $this->db->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * 生成订单号
     */
    public static function generateOrderNo() {
        return 'PAY' . date('YmdHis') . rand(1000, 9999);
    }
}
