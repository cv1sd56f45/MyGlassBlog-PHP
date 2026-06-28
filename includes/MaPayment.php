<?php
/**
 * MyGlassBlog PHP - 码支付 API 集成类
 */
class MaPayment {
    private $pid;
    private $key;
    private $apiUrl;
    
    public function __construct($pid, $key, $apiUrl = 'https://api.maepay.com') {
        $this->pid = $pid;
        $this->key = $key;
        $this->apiUrl = rtrim($apiUrl, '/');
    }
    
    /**
     * 生成签名
     */
    private function sign($params) {
        ksort($params);
        $str = '';
        foreach ($params as $k => $v) {
            if ($v !== '' && $k !== 'sign') {
                $str .= $k . '=' . $v . '&';
            }
        }
        $str .= 'key=' . $this->key;
        return md5($str);
    }
    
    /**
     * 发起支付请求
     */
    public function submitPay($params) {
        $params['pid'] = $this->pid;
        $params['sign'] = $this->sign($params);
        
        $url = $this->apiUrl . '/submit.php';
        return $this->buildForm($url, $params);
    }
    
    /**
     * 发起API支付（返回JSON）
     */
    public function apiPay($params) {
        $params['pid'] = $this->pid;
        $params['sign'] = $this->sign($params);
        
        $url = $this->apiUrl . '/mapi.php';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
    }
    
    /**
     * 查询订单
     */
    public function queryOrder($out_trade_no) {
        $params = [
            'pid' => $this->pid,
            'out_trade_no' => $out_trade_no,
        ];
        $params['sign'] = $this->sign($params);
        
        $url = $this->apiUrl . '/api.php?act=order&' . http_build_query($params);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
    }
    
    /**
     * 验证回调签名
     */
    public function verifyNotify($params) {
        $sign = $params['sign'] ?? '';
        unset($params['sign']);
        
        $newSign = $this->sign($params);
        return $sign === $newSign;
    }
    
    /**
     * 构建自动提交表单
     */
    private function buildForm($url, $params) {
        $html = '<form id="paysubmit" name="paysubmit" action="' . $url . '" method="post">';
        foreach ($params as $k => $v) {
            $html .= '<input type="hidden" name="' . $k . '" value="' . htmlspecialchars($v) . '">';
        }
        $html .= '<input type="submit" value="正在跳转至支付页面..." style="display:none">';
        $html .= '</form>';
        $html .= '<script>document.forms["paysubmit"].submit();</script>';
        return $html;
    }
}
