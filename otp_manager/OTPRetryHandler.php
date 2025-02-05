<?php
/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    DeviceOTP
 * @Project     DeviceOTP
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-01-31 21:10 PM
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

class OTPRetryHandler {
    private array $retryDelays;
    private OTPRepository $otpDeviceRepository;

    private int $timeLeft = 0;
    private int $maxTimeForDenied;
    public function __construct(array $retryDelays, OTPRepository $otpRepository, int $maxTimeForDenied = 6000) {
        $this->retryDelays = $retryDelays;
        $this->otpDeviceRepository = $otpRepository;
        $this->maxTimeForDenied = $maxTimeForDenied;
    }

    public function getRetryAttempt(int $recipientId, string $deviceId): int {
        $pendingOTPs = $this->otpDeviceRepository->countPendingOTPs($recipientId, $deviceId);
        return max($pendingOTPs, 0);  // Ensure retry attempt is always at least 1
    }

    public function canRetry(int $retryAttempt, int $lastRequestTime): bool {
        if($retryAttempt >= sizeof($this->retryDelays)) {
            $this->timeLeft = $this->maxTimeForDenied;
            return false;
        }
        // Determine retry delay based on retry attempt
        if($retryAttempt < 1){
            $retryAttempt = 1;
        }
        $retryDelay = $this->retryDelays[$retryAttempt-1];

        // Calculate how long the user has to wait
        $this->timeLeft = max(0, $retryDelay - $lastRequestTime);

        // Allow retry if the last request time exceeds the retry delay
        return $lastRequestTime >= $retryDelay;
    }

    public function successTimeLeft(int $retryAttempt): int
    {
        $this->timeLeft = $this->retryDelays[$retryAttempt];
        return $this->timeLeft;
    }

    public function getTimeLeft(): int
    {
        return $this->timeLeft;
    }
}
