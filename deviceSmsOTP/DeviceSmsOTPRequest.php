<?php
/**
 * @PHP       Version >= 8.0
 * @copyright ©2023 Maatify.dev
 * @author    Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since     2024-12-11 4:01 AM
 * @link      https://www.maatify.dev Maatify.com
 * @link      https://github.com/Maatify/DeviceOTP  view project on GitHub
 * @Maatify   DeviceOTP :: DeviceSmsOTPRequest
 */

namespace Maatify\DeviceSmsOTP;

use \App\Assist\AppFunctions;
use Maatify\Functions\GeneralFunctions;
use Maatify\Json\FunJson;

abstract class DeviceSmsOTPRequest extends DeviceSmsOTP
{
    public const TRIES_SECOND_CODES = 3; //for wait for second code
    protected int $tries_second_codes = self::TRIES_SECOND_CODES;

    public const TRIES_THIRD_CODES = 5; // for wait for third code
    protected int $tries_third_codes = self::TRIES_THIRD_CODES;

    public const TRIES_LAST_CODES = 10; // for wait for third code
    protected int $tries_last_codes = self::TRIES_LAST_CODES;

    public const OTP_LENGTH = 6;
    protected int $otp_length = self::OTP_LENGTH;

    private array $exist = [];

    public function recordNew(int $entity_id, string $phone, $device_id): void
    {
        if ($this->sendSmsValidation($entity_id, $device_id)) {
            $this->newOtp($entity_id, $device_id, $phone);
        }
    }

    public function newOtp(int $entity_id, string $phone, string $device_id): int
    {
        if ($code = $this->generateOTP()) {
            if ($this->row_id = $this->Add(
                [
                    $this->entity_col_name => $entity_id,
                    'device_id'            => $device_id,
                    'code'                 => $this->encryption->Hash(password_hash($code, PASSWORD_DEFAULT)),
                    'time'                 => AppFunctions::CurrentDateTime(),
                    'expiry'               => $this->expiry_time,
                    'is_success'           => 0,
                ]
            )) {
                $this->corn_sender->RecordOTP($entity_id, $phone, $code);
                $this->exist = $this->devicePendingList($entity_id, $device_id);

                return $this->row_id;
            }
        }

        return 0;
    }

    private function generateOTP(): string
    {
        return GeneralFunctions::GenerateOTP($this->otp_length);
    }

    private function sendSmsValidation(int $entity_id, string $device_id): bool
    {
        $this->exist = $this->devicePendingList($entity_id, $device_id);
        $size = sizeof($this->exist);
        if (empty($this->exist)) {
            return true;
        } elseif (! empty($this->exist[0]['time']) && $this->validateSender($size, $this->exist[0]['time'])) {
            return true;
        }

        return false;
    }

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

    public function waitingSecond(int $entity_id, string $device_id): int
    {
        $size = sizeof($this->exist);
        if (empty($size)) {
            $exists = $this->devicePendingList($entity_id, $device_id);
            $size = sizeof($exists);
            if (empty($size)) {
                return $this->waitingTime(1) * 6;
            }
        }

        return $this->waitingTime($size) * 60;
    }

    private function validateSender(int $timesOfSent, string $dateTime): bool
    {
        $waiting = $this->waitingTime($timesOfSent);
        if ($waiting === 0) {
            return true;
        }
        if ($waiting !== 1000) {
            $timeOfLastMessage = strtotime($dateTime . ' + ' . $waiting . ' minute');
            if ($timeOfLastMessage <= time()) {
                return true;
            } else {
                $waitingTime = $timeOfLastMessage - time();
                FunJson::ErrorWithHeader400(
                    401601,
                    'code',
                    'Need to wait ' . $waitingTime . ' Seconds ',
                    $waitingTime,
                );

                return false;
            }
        }

        return false;
    }
}