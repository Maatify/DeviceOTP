<?php
/**
 * @PHP       Version >= 8.0
 * @copyright ©2025 Maatify.dev
 * @author    Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since     2025-01-29 6:38 AM
 * @link      https://www.maatify.dev Maatify.com
 * @link      https://github.com/Maatify/DeviceOTP  view project on GitHub
 * @Maatify   DeviceOTP :: Maatify\DeviceEmailOTP\DeviceEmailOTPRequest
 */

namespace Maatify\DeviceEmailOTP;

use Maatify\DeviceOTPContracts\DeviceOTPRequestInterface;
use Maatify\DeviceOTPTraits\DeviceOTPRequestTrait;

abstract class DeviceEmailOTPRequest extends DeviceEmailOTP implements DeviceOTPRequestInterface
{
    use DeviceOTPRequestTrait;
    public const TRIES_SECOND_CODES = 3; //for wait for second code
    protected int $tries_second_codes = self::TRIES_SECOND_CODES;

    public const TRIES_THIRD_CODES = 5; // for wait for third code
    protected int $tries_third_codes = self::TRIES_THIRD_CODES;

    public const TRIES_FOURTH_CODES = 10; // for wait for fourth code
    protected int $tries_fourth_codes = self::TRIES_FOURTH_CODES;

    public const TRIES_FIFTH_CODES = 15; // for wait for fourth code
    protected int $tries_fifth_codes = self::TRIES_FIFTH_CODES;

    public const TRIES_LAST_CODES = 20; // for wait for third code
    protected int $tries_last_codes = self::TRIES_LAST_CODES;

    public const OTP_LENGTH = 6;
    protected int $otp_length = self::OTP_LENGTH;

    public function newOtp(string $receiver): int
    {
        if ($code = $this->addCode()) {

            $this->corn_sender->RecordConfirmCode($this->entity_id, $receiver, $code);
            $this->exist_count++;
            $this->all_count_of_customer_app_today ++;

            return $this->row_id;
        }

        return 0;
    }

    private function waitingTime(int $timesOfSent): int
    {
        return match ($timesOfSent) {
            0 => 0,
            1 => $this->tries_second_codes,
            2 => $this->tries_third_codes,
            3 => $this->tries_fourth_codes,
            4 => $this->tries_fifth_codes,
            5 => $this->tries_last_codes,
            default => 1000,
        };
    }
}