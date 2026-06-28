-- ============================================
-- 添加支付网关支持和订单表
-- MyGlassBlog PHP 捐款功能扩展
-- ============================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- 11. 支付网关配置表
-- ----------------------------
DROP TABLE IF EXISTS `payment_gateways`;
CREATE TABLE `payment_gateways` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `gateway_type` varchar(20) NOT NULL DEFAULT '' COMMENT '网关类型：yipay=易支付 mapay=码支付',
  `gateway_name` varchar(50) NOT NULL DEFAULT '' COMMENT '网关名称（如：易支付-支付宝）',
  `pid` varchar(100) NOT NULL DEFAULT '' COMMENT '商户PID',
  `key` varchar(255) NOT NULL DEFAULT '' COMMENT '商户密钥',
  `api_url` varchar(500) NOT NULL DEFAULT '' COMMENT 'API地址',
  `notify_url` varchar(500) NOT NULL DEFAULT '' COMMENT '异步回调地址',
  `return_url` varchar(500) NOT NULL DEFAULT '' COMMENT '同步跳转地址',
  `is_enabled` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否启用：0停用 1启用',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_type` (`gateway_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='支付网关配置表';

-- ----------------------------
-- 12. 支付订单表
-- ----------------------------
DROP TABLE IF EXISTS `payment_orders`;
CREATE TABLE `payment_orders` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_no` varchar(50) NOT NULL DEFAULT '' COMMENT '订单号',
  `gateway_type` varchar(20) NOT NULL DEFAULT '' COMMENT '网关类型',
  `pay_type` varchar(20) NOT NULL DEFAULT '' COMMENT '支付类型：alipay=支付宝 wxpay=微信支付 qqpay=QQ钱包',
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '支付金额',
  `actual_amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '实际支付金额',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态：0待支付 1已支付 2已取消 3支付失败',
  `payer_name` varchar(50) NOT NULL DEFAULT '' COMMENT '付款人名称',
  `payer_contact` varchar(100) NOT NULL DEFAULT '' COMMENT '付款人联系方式',
  `message` varchar(500) NOT NULL DEFAULT '' COMMENT '留言',
  `trade_no` varchar(100) NOT NULL DEFAULT '' COMMENT '第三方交易号',
  `pay_time` timestamp NULL DEFAULT NULL COMMENT '支付时间',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_order_no` (`order_no`),
  KEY `idx_status` (`status`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='支付订单表';

-- 示例：添加易支付配置（需要用户自行填写）
-- INSERT INTO `payment_gateways` (`gateway_type`, `gateway_name`, `pid`, `key`, `api_url`, `is_enabled`) VALUES
-- ('yipay', '易支付', '你的PID', '你的密钥', 'https://你的易支付域名/', 1);

SET FOREIGN_KEY_CHECKS = 1;
