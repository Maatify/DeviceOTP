<?php
/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    DeviceOTP
 * @Project     DeviceOTP
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-01-31 21:12 PM
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

use Maatify\Logger\Logger;
use Maatify\OTPManager\Contracts\Encryptions\OTPEncryptionInterface;
use Maatify\OTPManager\Contracts\OTPRepositoryInterface;
use Maatify\OTPManager\Contracts\OTPRetryHandlerInterface;
use Maatify\OTPManager\Contracts\OTPRoleCheckerInterface;
use Random\RandomException;

class OTPManager
{
    private int $expiry_of_code;

    public function __construct(
        private readonly OTPEncryptionInterface $otpEncryption,
        private readonly OTPRepositoryInterface $otpRepository,
        private readonly OTPRoleCheckerInterface $roleChecker,
        private readonly OTPRetryHandlerInterface $retryHandler,
        int $expiry_of_code = 180)
    {
        $this->expiry_of_code = $expiry_of_code;
    }

    public function requestOTP(int $recipientId, string $deviceId = '', string $own_otp = ''): array
    {
        if ($this->roleChecker->hasTooManyPendingOTPsForRole($recipientId)) {
            return [
                'status'          => 'error',
                'code'            => 429,
                'error'           => 'E002',
                'message'         => 'Too many pending OTP requests for this recipient.',
                'waiting_seconds' => 0,
            ];
        }

        if ($this->roleChecker->hasTooManyPendingOTPs($recipientId, $deviceId)) {
            return [
                'status'          => 'error',
                'code'            => 430,
                'error'           => 'E001',
                'message'         => 'Too many pending OTP requests for this device.',
                'waiting_seconds' => 0,
            ];
        }

        $retryAttempt = $this->retryHandler->getRetryAttempt($recipientId, $deviceId);
        $lastRequestTime = $this->otpRepository->getLastRequestTime($recipientId, $deviceId);
        $canRetry = $this->retryHandler->canRetry($retryAttempt, $lastRequestTime);
        $timeLeft = $this->retryHandler->getTimeLeft();
        if ($lastRequestTime && ! $canRetry) {
            return [
                'status'          => 'error',
                'code'            => 400,
                'error'           => 'E004',
                'message'         => "Please wait $timeLeft seconds before retrying.",
                'waiting_seconds' => $timeLeft,
            ];
        }

        if($own_otp){
            $otpCode = $own_otp;
        }else{
            try {
                $otpCode = (string)random_int(100000, 999999);
            } catch (RandomException $e) {
                Logger::RecordLog($e, 'OTPManagerException');
                $otpCode = (new OTPGenerator())->generateOTP();
            }
        }

        $otpCodeHashed = $this->otpEncryption->hashOTP($otpCode);

        $this->otpRepository->insertOTP($recipientId, $otpCodeHashed, $this->expiry_of_code, $deviceId);

        $timeLeft = $this->retryHandler->successTimeLeft($retryAttempt);

        return [
            'status'          => 'success',
            'code'            => 200,
            'otp'             => $otpCode,
            'expiry'          => $this->expiry_of_code,
            'message'         => "OTP Sent, Please wait $timeLeft seconds before retrying.",
            'waiting_seconds' => $timeLeft];
    }

    public function isCodePendingExist(int $recipientId, string $deviceId = ''): array
    {
        if ($this->roleChecker->hasTooManyPendingOTPsForRole($recipientId)) {
            return ['pending' => true, 'waiting_seconds' => 0];
        }

        if ($this->roleChecker->hasTooManyPendingOTPs($recipientId, $deviceId)) {
            return ['pending' => true, 'waiting_seconds' => 0];
        }

        $retryAttempt = $this->retryHandler->getRetryAttempt($recipientId, $deviceId);
        $lastRequestTime = $this->otpRepository->getLastRequestTime($recipientId, $deviceId);
        $canRetry = $this->retryHandler->canRetry($retryAttempt, $lastRequestTime);

        if ($lastRequestTime && ! $canRetry) {
            return ['pending' => true, 'waiting_seconds' => $this->retryHandler->getTimeLeft()];
        }

        return ['pending' => false, 'waiting_seconds' => 0];
    }

    public function countAllTypesPendingOTPsForRole(int $recipientId): array
    {
        return $this->otpRepository->countAllTypesPendingOTPsForRole($recipientId);
    }

    public function countAllTypesPendingOTPs(int $recipientId, string $deviceId = ''): array
    {
        return $this->otpRepository->countAllTypesPendingOTPs($recipientId, $deviceId);
    }

    public function confirmOTP(int $recipientId, string $otpCode, string $deviceId = '', bool $terminate_all_valide_codes = false, bool $confirm_by_any_sender_type = false): array
    {
        $result = $this->otpRepository->confirmOTP($recipientId, $deviceId, $otpCode, $terminate_all_valide_codes, $confirm_by_any_sender_type);

        return match ($result) {
            200 => [
                'status'         => 'success',
                'code'           => 200,
                'message'        => "Successfully verified the OTP.",
                'sender_type_id' => $this->otpRepository->getOtpSenderTypeId(),
                'otp_id'         => $this->otpRepository->getOtpId(),
            ],
            410 => [
                'status'         => 'error',
                'code'           => 410,
                'message'        => "Expired OTP code.",
                'sender_type_id' => $this->otpRepository->getOtpSenderTypeId(),
                'otp_id'         => 0,
            ],
            401 => [
                'status'         => 'error',
                'code'           => 401,
                'message'        => "Invalid OTP code.",
                'sender_type_id' => null,
                'otp_id'         => 0,
            ],
            default => [
                'status'         => 'error',
                'code'           => 404,
                'message'        => "Not Found OTP code.",
                'sender_type_id' => null,
                'otp_id'         => 0,
            ],
        };
    }

    public function remarkOTPidAsUnusedAndRefreshTime(int $otp_id, string $date_time): void
    {
        $this->otpRepository->remarkOTPidAsUnusedAndRefreshTime($otp_id, $date_time);
    }
}
