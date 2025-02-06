<?php
/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    DeviceOTP
 * @Project     DeviceOTP
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-02-06 16:56 PM
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/DeviceOTP  view project on GitHub
 * @link        https://github.com/Maatify/AppHandler  (maatify/app-handler)
 * @link        https://github.com/Maatify/Logger  (maatify/logger)
 * @note        This Project using for OTP with MYSQL PDO (PDO_MYSQL).
 * @note        This Project extends other libraries maatify/app-handler and maatify/logger.
 *
 * @note        This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 *
 */

declare(strict_types=1);

namespace Maatify\OTPManager;

class OTPGenerator
{
    public function generateOTP(int $length = 6): string
    {
        $otp = '';
        $codeAlphabet = '0123456789';
        $max = strlen($codeAlphabet); // edited
        for ($i = 0; $i < $length; $i++) {
            $otp .= $codeAlphabet[$this->crypto_rand_secure($max - 1)];
        }

        return $otp;
    }

    private function crypto_rand_secure(int $range): int
    {
        if ($range < 1) {
            return 0;
        } // not so random...
        $log = ceil(log($range, 2));
        $bytes = (int)($log / 8) + 1;    // length in bytes
        $bits = (int)$log + 1;           // length in bits
        $filter = (int)(1 << $bits) - 1; // set all lower bits to 1
        do {
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
            $rnd = $rnd & $filter; // discard irrelevant bits
        } while ($rnd > $range);

        return $rnd;
    }
}