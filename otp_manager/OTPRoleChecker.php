<?php
/**
 * Created by Maatify.dev
 * User: Maatify.dev
 * Date: 2025-01-31
 * Time: 21:11
 * Project: DeviceOTP
 * IDE: PhpStorm
 * https://www.Maatify.dev
 */

declare(strict_types=1);

namespace Maatify\OTPManager;

class OTPRoleChecker {
    private int $maxDevicePendingOTPs;
    private int $maxRolePendingOTPs;
    private OTPRepository $otpRepository;

    public function __construct(int $maxDevicePendingOTPs, int $maxRolePendingOTPs, OTPRepository $otpRepository) {
        $this->maxDevicePendingOTPs = $maxDevicePendingOTPs;
        $this->maxRolePendingOTPs = $maxRolePendingOTPs;
        $this->otpRepository = $otpRepository;
    }

    public function hasTooManyPendingOTPs(int $recipientId, string $deviceId): bool {
        return $this->otpRepository->countPendingOTPs($recipientId, $deviceId) >= $this->maxDevicePendingOTPs;
    }

    public function hasTooManyPendingOTPsForRole(int $recipientId): bool {
        return $this->otpRepository->countPendingOTPsForRole($recipientId) >= $this->maxRolePendingOTPs;
    }
}
