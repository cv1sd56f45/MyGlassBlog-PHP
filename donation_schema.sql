-- ============================================
-- 捐款表结构更新 v2
-- MyGlassBlog PHP 捐款功能扩展
-- ============================================

-- 添加支付类型字段和链接字段
ALTER TABLE `donations` 
ADD COLUMN `pay_type` VARCHAR(20) NOT NULL DEFAULT 'qrcode' COMMENT '支付类型：qrcode=二维码 yipay=易支付 mapay=码支付 link=自定义链接' AFTER `platform`,
ADD COLUMN `link` VARCHAR(500) NOT NULL DEFAULT '' COMMENT '自定义链接（pay_type=link时使用）' AFTER `qrcode`;

-- 更新现有数据，将所有现有记录的pay_type设为qrcode（二维码）
UPDATE `donations` SET `pay_type` = 'qrcode' WHERE `pay_type` = '';

-- 添加站点配置项用于ICP备案号
INSERT INTO `settings` (`site_key`, `site_value`, `description`) VALUES
('icp_number', '', 'ICP备案号'),
('donation_type', 'qrcode', '默认捐赠类型：qrcode/yipay/mapay/link/all')
ON DUPLICATE KEY UPDATE `site_key` = `site_key`;
