<?php
/**
 * @PHP       Version >= 8.0
 * @copyright ©2023 Maatify.dev
 * @author    Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since     2024-12-11 4:01 AM
 * @link      https://www.maatify.dev Maatify.com
 * @link      https://github.com/Maatify/DeviceOTP  view project on GitHub
 * @Maatify   DeviceOTP :: Maatify\DeviceSmsOTP\DeviceSmsOTPRequest
 */

namespace Maatify\DeviceSmsOTP;

use Maatify\DeviceOTPTraits\DeviceOTPRequestTrait;

abstract class DeviceSmsOTPRequest extends DeviceSmsOTP
{
    use DeviceOTPRequestTrait;

    public const TRIES_SECOND_CODES = 3; //for wait for second code
    protected int $tries_second_codes = self::TRIES_SECOND_CODES;

    public const TRIES_THIRD_CODES = 5; // for wait for third code
    protected int $tries_third_codes = self::TRIES_THIRD_CODES;

    public const TRIES_LAST_CODES = 10; // for wait for third code
    protected int $tries_last_codes = self::TRIES_LAST_CODES;

    public const OTP_LENGTH = 6;
    protected int $otp_length = self::OTP_LENGTH;

    private function waitingTime(int $timesOfSent): int
    {
        return match ($timesOfSent) {
            0 => 0,
            1 => $this->tries_second_codes,
            2 => $this->tries_third_codes,
            3 => $this->tries_last_codes,
            default => 1000,
        };
    }
}