<?php
/**
 * Created by Maatify.dev
 * User: Maatify.dev
 * Date: 2025-01-31
 * Time: 21:10
 * Project: DeviceOTP
 * IDE: PhpStorm
 * https://www.Maatify.dev
 */

declare(strict_types=1);

namespace Maatify\OTPManager;
class OTPRetryHandler {
    private array $retryDelays;
    private OTPRepository $otpRepository;

    private int $timeLeft = 0;
    private int $maxTimeForDenied;
    public function __construct(array $retryDelays, OTPRepository $otpRepository, int $maxTimeForDenied = 6000) {
        $this->retryDelays = $retryDelays;
        $this->otpRepository = $otpRepository;
        $this->maxTimeForDenied = $maxTimeForDenied;
    }

    public function getRetryAttempt(int $recipientId, string $deviceId): int {
        $pendingOTPs = $this->otpRepository->countPendingOTPs($recipientId, $deviceId);
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
