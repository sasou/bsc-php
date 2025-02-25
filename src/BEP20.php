<?php

namespace Binance;

use Web3p\EthereumTx\Transaction;

class BEP20 extends Bnb
{
    protected $contractAddress;
    protected $decimals;

    function __construct(ProxyApi $proxyApi, array $config)
    {
        parent::__construct($proxyApi);

        $this->contractAddress = $config['contract_address'];
        $this->decimals = $config['decimals'];
    }

    public function balance(string $address)
    {
        $params = [];
        $params['to'] = $this->contractAddress;

        $method = 'balanceOf(address)';
        $formatMethod = Formatter::toMethodFormat($method);
        $formatAddress = Formatter::toAddressFormat($address);

        $params['data'] = "0x{$formatMethod}{$formatAddress}";

        $balance = $this->proxyApi->ethCall($params);
        return Utils::formatBalance($balance, $this->decimals);
    }

    public function transfer(string $privateKey, string $to, float $value, string $gasPrice = '')
    {
        $from = PEMHelper::privateKeyToAddress($privateKey);
        $nonce = $this->proxyApi->getNonce($from);
        if ($gasPrice == '') {
            $gasPrice = $this->proxyApi->gasPrice();
        }
        
        $params = [
            'nonce' => "$nonce",
            'from' => $from,
            'to' => $this->contractAddress,
            'gas' => '0xea60',
            'gasPrice' => "$gasPrice",
            'value' => Utils::NONE,
            'chainId' => self::getChainId($this->proxyApi->getNetwork()),
        ];
        $val = Utils::convertAmountToWei($value, $this->decimals);

        $method = 'transfer(address,uint256)';
        $formatMethod = Formatter::toMethodFormat($method);
        $formatAddress = Formatter::toAddressFormat($to);
        $formatInteger = Formatter::toIntegerFormat($val);

        $params['data'] = "0x{$formatMethod}{$formatAddress}{$formatInteger}";
        $transaction = new Transaction($params);

        $raw = $transaction->sign($privateKey);
        $res = $this->proxyApi->sendRawTransaction('0x' . $raw);
        return $res;
    }
}
