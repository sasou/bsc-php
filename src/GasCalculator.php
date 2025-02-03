<?php

namespace Binance;

class GasCalculator
{
    // BNB转账的标准Gas Limit
    const BEP20_GAS_LIMIT = 21000;

    // ANKR API端点
    private $ankrUrl = 'https://rpc.ankr.com/bsc';

    /**
     * 获取当前推荐的Gas Price（单位：Gwei）
     */
    public function getCurrentGasPrice()
    {
        // 构造JSON-RPC请求
        $payload = json_encode([
            'jsonrpc' => '2.0',
            'method' => 'eth_gasPrice',
            'params' => [],
            'id' => 1,
        ]);

        // 发送HTTP请求
        $ch = curl_init($this->ankrUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        // 解析响应
        $data = json_decode($response, true);
        if (isset($data['result'])) {
            return $data['result'];
        } else {
            throw new \Exception("Error fetching gas price: " . ($data['error']['message'] ?? 'Unknown error'));
        }
    }

    /**
     * 计算BEP20转账USDT的手续费
     *
     * @return string 手续费（单位：BNB）
     */
    public function gasPrice()
    {
        $price = $this->getCurrentGasPrice();
        // 将十六进制Gas Price转换为十进制（单位：Wei）
        $gasPriceInWei = hexdec(price);
        // 转换为Gwei（1 Gwei = 10^9 Wei）
        return $gasPriceInWei / 1e9;
    }

    /**
     * 计算BEP20转账USDT的手续费
     *
     * @return string 手续费（单位：BNB）
     */
    public function calculateGasFee()
    {
        // 获取当前Gas Price
        $gasPrice = $this->gasPrice();

        // 将Gas Price从Gwei转换为Wei（1 Gwei = 10^9 Wei）
        $gasPriceInWei = $gasPrice * 1e9;

        // 计算手续费（单位：Wei）
        $gasFeeInWei = self::BEP20_GAS_LIMIT * $gasPriceInWei;

        // 将手续费从Wei转换为BNB（1 BNB = 10^18 Wei）
        $gasFeeInBnb = $gasFeeInWei / 1e18;

        return number_format($gasFeeInBnb, 8); // 返回8位小数的BNB金额
    }
}
