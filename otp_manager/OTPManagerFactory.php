<?php
/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    DeviceOTP
 * @Project     DeviceOTP
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-01-31 22:44 PM
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

use Maatify\AppController\Contracts\AppTypeIdInterface;
use Maatify\AppController\Enums\AppTypeIdEnum;
use Maatify\OTPManager\Contracts\Encryptions\OTPEncryptionInterface;
use Maatify\OTPManager\Contracts\Enums\OTPSenderTypeIdInterface;
use Maatify\OTPManager\Contracts\Enums\RecipientTypeIdInterface;
use Maatify\OTPManager\Enums\OTPSenderTypeIdEnum;
use Maatify\OTPManager\Enums\RecipientTypeIdEnum;
use PDO;

class OTPManagerFactory
{
    public static function create(
        PDO $pdo,
        OTPEncryptionInterface $otpEncryption,
        string $tableName = 'ct_otp_code',
        RecipientTypeIdInterface $recipientTypeId = RecipientTypeIdEnum::Customer,
        AppTypeIdInterface $appTypeId = AppTypeIdEnum::Web,
        OTPSenderTypeIdInterface $otpSenderTypeId = OTPSenderTypeIdEnum::SMS,
        array $retryDelays = [
            60,     // First attempt (or no previous OTP) requires 60 seconds wait
            180,    // First retry attempt after failure
            300,    // Second retry attempt requires 5 minutes wait
            300,    // Third retry requires 10 minutes wait
        ],
        int $maxRolePendingOTPs = 5,   //10 role-specific limits
        int $maxTimeForDenied = 6000,
        int $expiry_of_code = 180,
    ): OTPManager
    {
        // Instantiate repository and handlers
        $otpRepository = new OTPRepository(
            pdo            : $pdo,
            otpEncryption  : $otpEncryption,
            tableName      : $tableName,
            recipientTypeId: $recipientTypeId,
            appTypeId      : $appTypeId,
            otpSenderTypeId: $otpSenderTypeId
        );

        $otpRetryHandler = new OTPRetryHandler(
            $otpRepository,
            $retryDelays,
            maxTimeForDenied: $maxTimeForDenied,
        );

        //        $otpRoleChecker = new OTPRoleChecker($maxDevicePendingOTPs, $maxRolePendingOTPs, $otpRepository);
        $otpRoleChecker = new OTPRoleChecker($otpRepository, sizeof($retryDelays), $maxRolePendingOTPs);

        // Return fully constructed OTPManager
        return new OTPManager($otpEncryption, $otpRepository, $otpRoleChecker, $otpRetryHandler, $expiry_of_code);
    }
}
