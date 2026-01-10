<?php

$ga = new GoogleAuthenticator();

function getProvisioningUri($username, $issuer, $secret) {
    return 'otpauth://totp/' . rawurlencode($issuer) . ':' . rawurlencode($username) .
           '?secret=' . rawurlencode($secret) . '&issuer=' . rawurlencode($issuer);
}

function getQrCodeUrl($provisioningUri) {
    return 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($provisioningUri);
}

class GoogleAuthenticator
{
    public function createSecret($secretLength = 32)
    {
        $validChars = $this->getBase32LookupTable();
        $secret = '';
        for ($i = 0; $i < $secretLength; $i++) {
            $secret .= $validChars[array_rand($validChars)];
        }
        return $secret;
    }

    public function getQRGoogleUrl($name, $secret, $title = null, $params = [])
    {
        $urlencoded = urlencode("otpauth://totp/{$name}?secret={$secret}" . ($title ? "&issuer=" . urlencode($title) : ""));
        return "https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl={$urlencoded}";
    }

    public function verifyCode($secret, $code, $discrepancy = 1, $currentTimeSlice = null)
    {
        if ($currentTimeSlice === null) {
            $currentTimeSlice = floor(time() / 30);
        }
        if (strlen($code) != 6) {
            return false;
        }
        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $calculatedCode = $this->getCode($secret, $currentTimeSlice + $i);
            if ($calculatedCode === $code) {
                return true;
            }
        }
        return false;
    }

    public function getCode($secret, $timeSlice)
    {
        $key = $this->base32Decode($secret);
        $time = pack('N*', 0) . pack('N*', $timeSlice);
        $hm = hash_hmac('sha1', $time, $key, true);
        $offset = ord(substr($hm, -1)) & 0x0F;
        $hashpart = substr($hm, $offset, 4);
        $value = unpack('N', $hashpart)[1];
        $value = $value & 0x7FFFFFFF;
        $modulo = 1000000;
        return str_pad($value % $modulo, 6, '0', STR_PAD_LEFT);
    }

    private function base32Decode($secret)
    {
        if (empty($secret)) return '';
        $base32chars = $this->getBase32LookupTable();
        $base32charsFlipped = array_flip($base32chars);
        $secret = strtoupper($secret);
        $paddingCharCount = substr_count($secret, '=');
        $allowedValues = [6,4,3,1,0];
        if (!in_array($paddingCharCount, $allowedValues)) return false;
        $secret = str_replace('=', '', $secret);
        $binaryString = '';
        for ($i = 0; $i < strlen($secret); $i++) {
            if (!isset($base32charsFlipped[$secret[$i]])) return false;
            $binaryString .= str_pad(decbin($base32charsFlipped[$secret[$i]]), 5, '0', STR_PAD_LEFT);
        }
        $eightBits = str_split($binaryString, 8);
        $decoded = '';
        foreach ($eightBits as $bits) {
            if (strlen($bits) < 8) continue;
            $decoded .= chr(bindec($bits));
        }
        return $decoded;
    }

    private function getBase32LookupTable()
    {
        return ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','2','3','4','5','6','7'];
    }
}