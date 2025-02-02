<?php
/**
 * @PHP       Version >= 8.0
 * @copyright ©2025 Maatify.dev
 * @author    Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since     2025-01-29 7:09 AM
 * @link      https://www.maatify.dev Maatify.com
 * @link      https://github.com/Maatify/DeviceOTP  view project on GitHub
 * @Maatify   DeviceOTP :: Maatify\DeviceOTPTraits\DeviceSmsOTPRequestTrait
 */


namespace Maatify\DeviceOTPTraits;

use \App\Assist\AppFunctions;
use Maatify\AppController\Enums\EnumAppTypeId;
use Maatify\Functions\GeneralFunctions;
use Maatify\Json\FunJson;

trait DeviceOTPRequestTrait
{
    public function initFromOtherCron(string $entity_id, EnumAppTypeId $app_type_id, string $device_id):static
    {
        $this->entity_id = $entity_id;
        $this->app_type_id = $app_type_id;
        $this->device_id =  $device_id;
        return $this;
    }

    private function addCode(): int
    {
        $code = $this->generateOTP();
        $this->row_id = $this->Add(
            [
                $this->entity_col_name => $this->entity_id,
                'app_type_id'          => $this->app_type_id->value,
                'device_id'            => $this->device_id,
                'code'                 => $this->encryption->Hash(password_hash($code, PASSWORD_DEFAULT)),
                'time'                 => AppFunctions::CurrentDateTime(),
                'expiry'               => $this->expiry_time,
                'is_success'           => 0,
            ]
        );

        return $code;
    }
    public function recordNew(string $receiver): void
    {
        if ($this->sendOtpValidation()) {
            $this->newOtp($receiver);
        }
    }


    private function generateOTP(): string
    {
        return GeneralFunctions::GenerateOTP($this->otp_length);
    }

    private function sendOtpValidation(): bool
    {
        $this->exist = $this->devicePendingList();
        if (empty($this->exist) || (! empty($this->exist[0]['time']) && $this->validateSender($this->exist[0]['time']))) {
            return true;
        }

        return false;
    }

    public function waitingSecond(): int
    {
        if (empty($this->exist_count)) {
            $this->exist = $this->devicePendingList();
            if (empty($this->exist_count)) {
                return $this->waitingTime(1) * 60;
            }
        }

        return $this->waitingTime($this->exist_count) * 60;
    }

    private function validateSender(string $dateTime): bool
    {
        $waiting = $this->waitingTime($this->exist_count);
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
                    401701,
                    'code',
                    'Need to wait ' . $waitingTime . ' Seconds ',
                    $waitingTime,
                );

                return false;
            }
        }

        return false;
    }

    protected function sendOtp(string $receiver): void
    {
        $this->recordNew($receiver);
    }

    private function newOtp(string $receiver): int
    {
        if ($code = $this->addCode()) {

            $this->corn_sender->RecordOTP($this->entity_id, $receiver, $code);

            $this->exist_count++;
            $this->all_count_of_customer_app_today ++;

            return $this->row_id;
        }

        return 0;
    }

}