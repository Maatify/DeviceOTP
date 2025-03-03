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

use Maatify\OTPManager\Contracts\OTPRepositoryInterface;
use Maatify\OTPManager\Contracts\OTPRoleCheckerInterface;

class OTPRoleChecker implements OTPRoleCheckerInterface{
    private int $maxDevicePendingOTPs;
    private int $maxRolePendingOTPs;

    public function __construct(
        private readonly OTPRepositoryInterface $otpDeviceRepository,
        int $maxDevicePendingOTPs,
        int $maxRolePendingOTPs
    ) {
        $this->maxDevicePendingOTPs = $maxDevicePendingOTPs;
        $this->maxRolePendingOTPs = $maxRolePendingOTPs;
    }

    public function hasTooManyPendingOTPs(int $recipientId, string $deviceId): bool {
        return $this->otpDeviceRepository->countPendingOTPs($recipientId, $deviceId) >= $this->maxDevicePendingOTPs;
    }

    public function hasTooManyPendingOTPsForRole(int $recipientId): bool {
        return $this->otpDeviceRepository->countPendingOTPsForRole($recipientId) >= $this->maxRolePendingOTPs;
    }
}
