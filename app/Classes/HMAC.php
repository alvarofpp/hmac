<?php
/**
 * Copyright (C) 2018 Álvaro Ferreira Pires de Paiva
 * Github: alvarofpp
 * E-mail: alvarofepipa@gmail.com
 */
namespace App\Classes;


use App\Models\Setting;

/**
 * This class performs the operations required to run the Hash-based Message Authentication Code (HMAC).
 */
class HMAC
{
    protected $ipadKey, $opadKey;
    protected $ipad, $opad;
    protected $sizeB;
    protected $key;

    /**
     * HMAC constructor.
     */
    function __construct()
    {
        $this->ipad = '00110110';
        $this->opad = '01011100';
        $this->sizeB = 64;

        $this->defineKey();
        $this->createKeys();
    }

    /**
     * Takes the key set or assigns the default value to the key.
     *
     * @return void
     */
    private function defineKey()
    {
        $key = Setting::query()
            ->where('name', 'hmac_key')
            ->first();

        if (is_null($key)) {
            $this->key = $key->value;
        } else {
            $this->key = 'segurancaEmR3d3s';
        }
    }

    /**
     * Checks the key length and performs the required procedure.
     *
     * @return void
     */
    private function lengthKeys()
    {
        $len = strlen($this->key);

        if ($len < $this->sizeB) {
            $this->key = str_pad($this->key, $this->sizeB, 0, STR_PAD_LEFT);

        } elseif ($len > $this->sizeB) {
            $this->key = md5($this->key);
        }
    }

    /**
     * Creates keys that will be used by HMAC in manipulation of hash.
     *
     * @return void
     */
    private function createKeys()
    {
        $this->lengthKeys();
        $key = $this->key;

        $arrayKey = str_split($key);
        $ipadArray = str_split($this->ipad);
        $opadArray = str_split($this->opad);

        $ipadKey = [];
        $opadKey = [];
        $ipadKeyTemp = [];
        $opadKeyTemp = [];

        // XOR
        for ($i = 0; $i < strlen($key); $i++) {
            $letterBinArray = str_pad(decbin(ord($arrayKey[$i])), 8, 0, STR_PAD_LEFT);

            for ($c = 0; $c < 8; $c++) {
                $ipadKeyTemp[$c] = ($letterBinArray[$c] xor $ipadArray[$c]) ? '1' : '0';
                $opadKeyTemp[$c] = ($letterBinArray[$c] xor $opadArray[$c]) ? '1' : '0';
            }

            $ipadKey[$i] = implode("", $ipadKeyTemp);
            $opadKey[$i] = implode("", $opadKeyTemp);
        }

        // Convert to binary and after to char
        for ($i = 0; $i < sizeof($ipadKey); $i++) {
            $ipadKey[$i] = chr(bindec($ipadKey[$i]));
            $opadKey[$i] = chr(bindec($opadKey[$i]));
        }

        $this->ipadKey = implode("", $ipadKey);
        $this->opadKey = implode("", $opadKey);
    }

    /**
     * Performs HMAC in the $hash.
     *
     * @param string $hash Hash's key.
     * @return string Final hash
     */
    public function execute($hash)
    {
        $hash = md5($hash . $this->ipadKey);

        return md5($this->opadKey . $hash);
    }
}
