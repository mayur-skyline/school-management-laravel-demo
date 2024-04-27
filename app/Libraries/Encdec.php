<?php

namespace App\Libraries;

use Illuminate\Support\Facades\Hash;

class Encdec {

    function plainTohashPassword($password) {
        $plain_password = $password; // plain password

        $hash_new_password = Hash::make($plain_password);

        return $hash_new_password;
    }

    function matchHashWithPlainPassword($data) {
        $plain_password = $data["user_inputed_password"]; // plain password
        $hashed_password = $data["check_against_password"]; // hashed password

        $status = FALSE;
        if (Hash::check($plain_password, $hashed_password)) {
            $status = TRUE;
        }

        return $status;
    }

    function needsRehashPassword($data) {
        $plain_password = $data["user_inputed_password"]; // plain password
        $hashed_password = $data["check_against_password"]; // hashed password

        $status = FALSE;
        if (Hash::needsRehash($hashed_password)) {
            $status = TRUE;
        }
        return $status;
    }

    function checkAgainstDummyHash() { // Just to prevent timing attack in staff login...
        // $dummy_string = '#70Cua(GXRWItdeE';
        $dummy_hash = $this->dummyHash();

        $status = FALSE;
        if (Hash::check("", $dummy_hash)) {
            $status = FALSE;
        }
        return FALSE; // This should always be false
    }

    // encrypt username
    function enc_string($string) {
        $string = $this->encrypt_decrypt('encrypt', $string);
        return $string;
    }

    // encrypt username
    function enc_username($snd_username) {
        $snd_username = $this->encrypt_decrypt('encrypt', $snd_username);
        return $snd_username;
    }

    // encrypt password
    function enc_password($snd_password) {
        $snd_password = substr($snd_password, 0, 5) . $snd_password . substr($snd_password, -4, 4);
        $snd_password = $this->encrypt_decrypt('encrypt', $snd_password);
        return $snd_password;
    }

    function enc_dummy_password() { // Just to prevent timing attack in pupil login...
        $dummy_string = $this->dummyString();
        $snd_password = strrev($dummy_string);
        $snd_password = substr($snd_password, 0, 5) . $snd_password . substr($snd_password, -4, 4);
        $snd_password = $this->encrypt_decrypt('encrypt', $snd_password);
        return FALSE;
    }

    function dummyString() {
        $dummy_string = '#70Cua(GXRWItdeE';
        return $dummy_string;
    }

    function dummyHash() { // created from dummyString() string function
        $dummy_hash = '$2y$10$jWUdSUBUrbiqA4HaCYTER.M9aOPecZSjm8XE9m2dz684Reodg4Xpi';
        return $dummy_hash;
    }

    function dummyCookieSecretkey() {
        $cookie_secret_key = '70Cua)1GX^RWItdeE';
        return $cookie_secret_key;
    }

    // encrypt user_id for usr_login
    function enc_userid_login($snd_userid_login) {
        $snd_userid_login = sprintf("%'u7s", $snd_userid_login);
        $snd_userid_login = substr($snd_userid_login, 0, 3) . $snd_userid_login . substr($snd_userid_login, -2, 2);
        $snd_userid_login = $this->encrypt_decrypt('encrypt', $snd_userid_login);
        return $snd_userid_login;
    }

    // encrypt user_id for usr_profile
    function enc_userid_profile($snd_userid_profile) {
        $snd_userid_profile = sprintf("%'u7s", $snd_userid_profile);
        $snd_userid_profile = substr($snd_userid_profile, 0, 2) . $snd_userid_profile . substr($snd_userid_profile, -3, 3);
        $snd_userid_profile = $this->encrypt_decrypt('encrypt', $snd_userid_profile);
        return $snd_userid_profile;
    }

    /* DECRYPT */

    // decrypt string
    function dec_string($enc_string) {
        $decrypted = explode(" ", $this->encrypt_decrypt('decrypt', $enc_string));
        $dec_string = $decrypted[0];
        return $dec_string;
    }

    // decrypt username
    function dec_username($enc_username) {
        $decrypted = explode(" ", $this->encrypt_decrypt('decrypt', $enc_username));
        $enc_username = $decrypted[0];
        return $enc_username;
    }

    // decrypt password
    function dec_password($enc_password) {
        $decrypted = explode(" ", $this->encrypt_decrypt('decrypt', $enc_password));
        $enc_password = substr(substr($decrypted[0], 0, -4), 5);
        return $enc_password;
    }

    // decrypt user_id for usr_login
    function dec_userid_login($enc_userid_login) {
        $decrypted = explode(" ", $this->encrypt_decrypt('decrypt', $enc_userid_login));
        $enc_userid_login = str_replace("u", "", substr(substr($decrypted[0], 0, -2), 3));
        return $enc_userid_login;
    }

    // decrypt user_id for usr_profile
    function dec_userid_profile($enc_userid_profile) {
        $decrypted = explode(" ", $this->encrypt_decrypt('decrypt', $enc_userid_profile));
        $enc_userid_profile = str_replace("u", "", substr(substr($decrypted[0], 0, -3), 2));
        return $enc_userid_profile;
    }

    // Function for encrypt decrytpt
    function encrypt_decrypt($action, $string) {
        $output = false;
        $encrypt_method = "AES-256-CBC";
        if (empty($secret_key)) {
            $secret_key = '';
        }
        $sklen = mb_strlen($secret_key, 'utf8');
        $sk = substr($secret_key, (round($sklen / 2, 0, PHP_ROUND_HALF_UP)), (round($sklen / 2, 0, PHP_ROUND_HALF_DOWN))) . substr($secret_key, 0, (round($sklen / 2, 0, PHP_ROUND_HALF_UP)));
        $key = hash('sha256', $secret_key);
        if (empty($secret_iv)) {
            $secret_iv = '';
        }
        $iv = substr(hash('sha256', $secret_iv), 0, 16);
        if ($action == 'encrypt') {
            $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
            $output = base64_encode($output);
        } else if ($action == 'decrypt') {
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv) . "$sk";
        }
        return $output;
    }

    function generate_otp($length = 8) {
        return substr(str_shuffle("0123456789aAbBcCdDeEfFgGhHiIjJkKLmMnoONpPqQrRsStTuUvVwWxXyYzZ"), 0, $length);
    }

    function get_ip_address() {
        // check for shared internet/ISP IP
        if (!empty($_SERVER['HTTP_CLIENT_IP']) && $this->validate_ip($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {// check for IPs passing through proxies
            // check if multiple ips exist in var
            if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',') !== false) {
                $iplist = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                foreach ($iplist as $ip) {
                    if ($this->validate_ip($ip)) {
                        return $ip;
                    }
                }
            } else {
                if ($this->validate_ip($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                    return $_SERVER['HTTP_X_FORWARDED_FOR'];
                }else {
                    return $_SERVER['HTTP_X_FORWARDED_FOR'];  // added temporary to prevent Ip error for some pc
                }
            }
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED']) && $this->validate_ip($_SERVER['HTTP_X_FORWARDED'])) {
            return $_SERVER['HTTP_X_FORWARDED'];
        } elseif (!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) && $this->validate_ip($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) {
            return $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_FORWARDED_FOR']) && $this->validate_ip($_SERVER['HTTP_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_FORWARDED_FOR'];
        } else if (!empty($_SERVER['HTTP_FORWARDED']) && $this->validate_ip($_SERVER['HTTP_FORWARDED'])) {
            return $_SERVER['HTTP_FORWARDED'];
        } else {
//          return unreliable ip since all else failed
            if (filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP,FILTER_FLAG_IPV4)) {
                return $_SERVER['REMOTE_ADDR'];
            } else {
                $defaut_ip = '000.000.00.000';   // default Ip for creating a token value
                return $defaut_ip;
            }
        }
    }

    public function GenerateRandomStrongToken() { // generate a token, should be 128 - 256 bit
        $random = openssl_random_pseudo_bytes(64);
        $token = bin2hex($random);
        return $token;
    }

    public function saltOption() {
        $cost = 10;
        return array('cost' => $cost);
    }

    public function createHashForString($string) {
//        $hash_string = password_hash($string, PASSWORD_BCRYPT, ['salt' => $this->dummyString()]);
        $hash_string = password_hash($string, PASSWORD_BCRYPT, $this->saltOption());

        return $hash_string;
    }
    
    public function validate_ip($ip) {
        if (strtolower($ip) === 'unknown')
            return false;

        // generate ipv4 network address
        $ip = ip2long($ip);

        // if the ip is set and not equivalent to 255.255.255.255
        if ($ip !== false && $ip !== -1) {
            // make sure to get unsigned long representation of ip
            // due to discrepancies between 32 and 64 bit OSes and
            // signed numbers (ints default to signed in PHP)
            $ip = sprintf('%u', $ip);
            // do private network range checking
            if ($ip >= 0 && $ip <= 50331647) return false;
            if ($ip >= 167772160 && $ip <= 184549375) return false;
            if ($ip >= 2130706432 && $ip <= 2147483647) return false;
            if ($ip >= 2851995648 && $ip <= 2852061183) return false;
            if ($ip >= 2886729728 && $ip <= 2887778303) return false;
            if ($ip >= 3221225984 && $ip <= 3221226239) return false;
            if ($ip >= 3232235520 && $ip <= 3232301055) return false;
            if ($ip >= 4294967040) return false;
        }
        return true;
    }


}
