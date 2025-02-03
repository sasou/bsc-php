<?php

namespace Binance;

use Web3p\EthereumTx\Transaction;

class Bnb
{
    protected $proxyApi;

    function __construct(ProxyApi $proxyApi)
    {
        $this->proxyApi = $proxyApi;
    }

    function __call($name, $arguments)
    {
        return call_user_func_array([$this->proxyApi, $name], $arguments);
    }

    public static function getChainId($network): int
    {
        $chainId = 56;
        switch ($network) {
            case 'mainnet':
                $chainId = 56;
                break;
            case 'testnet':
                $chainId = 97;
                break;
            default:
                break;
        }

        return $chainId;
    }

    public function transfer(string $privateKey, string $to, float $value, string $gasPrice = '')
    {
        $from = PEMHelper::privateKeyToAddress($privateKey);
        $nonce = $this->proxyApi->getNonce($from);
        $gasPrice = $this->proxyApi->gasPrice();

        $eth = Utils::convertAmountToWei($value, 18);
        $eth = Utils::convertMinUnitToHex($eth);
        $transaction = new Transaction([
            'nonce' => "$nonce",
            'from' => $from,
            'to' => $to,
            'gas' => '0x76c0',
            'gasPrice' => "$gasPrice",
            'value' => '0x' . $eth,
            'chainId' => self::getChainId($this->proxyApi->getNetwork()),
        ]);

        $raw = $transaction->sign($privateKey);
        $res = $this->proxyApi->sendRawTransaction('0x' . $raw);
        return $res;
    }
}
