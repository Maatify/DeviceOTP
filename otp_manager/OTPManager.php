<?php
/**
 * Created by Maatify.dev
 * User: Maatify.dev
 * Date: 2025-01-31
 * Time: 21:12
 * Project: DeviceOTP
 * IDE: PhpStorm
 * https://www.Maatify.dev
 */

declare(strict_types=1);

namespace Maatify\OTPManager;

namespace Maatify\OTPManager;

use App\Assist\Encryptions\OTPEncryption;
use Maatify\Functions\GeneralFunctions;
use Maatify\Logger\Logger;
use Random\RandomException;

class OTPManager {
    private OTPRepository $otpRepository;
    private OTPRoleChecker $roleChecker;
    private OTPRetryHandler $retryHandler;
    private int $expiry_of_code;

    public function __construct(OTPRepository $otpRepository, OTPRoleChecker $roleChecker, OTPRetryHandler $retryHandler, int $expiry_of_code = 180) {
        $this->otpRepository = $otpRepository;
        $this->roleChecker = $roleChecker;
        $this->retryHandler = $retryHandler;
        $this->expiry_of_code = $expiry_of_code;
    }

    public function requestOTP(int $recipientId, string $deviceId): array {


        if ($this->roleChecker->hasTooManyPendingOTPsForRole($recipientId)) {
            return [
                'status' => 'error',
                'code' => 429,
                'error' => 'E002',
                'message' => 'Too many pending OTP requests for this recipient.',
                'waiting_seconds' => 0,
            ];
        }

        if ($this->roleChecker->hasTooManyPendingOTPs($recipientId, $deviceId)) {
            return [
                'status' => 'error',
                'code' => 430,
                'error' => 'E001',
                'message' => 'Too many pending OTP requests for this device.',
                'waiting_seconds' => 0,
            ];
        }

        $retryAttempt = $this->retryHandler->getRetryAttempt($recipientId, $deviceId);
        $lastRequestTime = $this->otpRepository->getLastRequestTime($recipientId, $deviceId);
        $canRetry = $this->retryHandler->canRetry($retryAttempt, $lastRequestTime);
        $timeLeft = $this->retryHandler->getTimeLeft();
        if ($lastRequestTime && !$canRetry) {
            return [
                'status' => 'error',
                'code' => 400,
                'error' => 'E004',
                'message' => "Please wait $timeLeft seconds before retrying.",
                'waiting_seconds' => $timeLeft,
            ];
        }

        try {
            $otpCode = (string) random_int(100000, 999999);
        } catch (RandomException $e) {
            Logger::RecordLog($e, 'OTPManagerException');
            $otpCode = GeneralFunctions::GenerateOTP(6);
        }

        $otpCodeHashed = (new OTPEncryption())->hashOTP($otpCode);
//        $expiry = 180; // Example fixed expiry
        $this->otpRepository->insertOTP($recipientId, $deviceId, $otpCodeHashed, $this->expiry_of_code);

        $timeLeft = $this->retryHandler->successTimeLeft($retryAttempt);

        return [
            'status' => 'success',
            'code' => 200,
            'otp' => $otpCode,
            'expiry' => $this->expiry_of_code,
            'message' => "OTP Sent, Please wait $timeLeft seconds before retrying.",
            'waiting_seconds' => $timeLeft];
    }

    public function confirmOTP(int $recipientId, string $deviceId, string $otpCode): array {
        $result = $this->otpRepository->confirmOTP($recipientId, $deviceId, $otpCode);

        return match ($result) {
            200 => [
                'status'  => 'success',
                'code'    => 200,
                'message' => "Successfully verified the OTP.",
            ],
            401 => [
                'status'  => 'error',
                'code'    => 401,
                'message' => "Expired OTP code.",
            ],
            default => [
                'status'  => 'error',
                'code'    => 404,
                'message' => "Invalid/Not Found OTP code.",
            ],
        };
    }
}
