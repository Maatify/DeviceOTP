<?php
/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    DeviceOTP
 * @Project     DeviceOTP
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-01-31 21:11 PM
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/DeviceOTP  view project on GitHub
 * @link        https://github.com/Maatify/AppHandler  (maatify/app-handler)
 * @note        This Project using for OTP with MYSQL PDO (PDO_MYSQL).
 * @note        This Project extends other libraries maatify/app-handler.
 *
 * @note        This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 *
 */

declare(strict_types=1);

namespace Maatify\OTPManager;

class OTPRoleChecker {
    private int $maxDevicePendingOTPs;
    private int $maxRolePendingOTPs;
    private OTPRepository $otpDeviceRepository;

    public function __construct(int $maxDevicePendingOTPs, int $maxRolePendingOTPs, OTPRepository $otpRepository) {
        $this->maxDevicePendingOTPs = $maxDevicePendingOTPs;
        $this->maxRolePendingOTPs = $maxRolePendingOTPs;
        $this->otpDeviceRepository = $otpRepository;
    }

    public function hasTooManyPendingOTPs(int $recipientId, string $deviceId): bool {
        return $this->otpDeviceRepository->countPendingOTPs($recipientId, $deviceId) >= $this->maxDevicePendingOTPs;
    }

    public function hasTooManyPendingOTPsForRole(int $recipientId): bool {
        return $this->otpDeviceRepository->countPendingOTPsForRole($recipientId) >= $this->maxRolePendingOTPs;
    }
}
