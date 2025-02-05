<?php
/**
 * Created by Maatify.dev
 * User: Maatify.dev
 * Date: 2025-01-31
 * Time: 22:44
 * Project: DeviceOTP
 * IDE: PhpStorm
 * https://www.Maatify.dev
 */

declare(strict_types=1);

namespace Maatify\OTPDeviceManager;

use Maatify\AppController\Enums\EnumAppTypeId;
use Maatify\OTPManager\Contracts\OTPEncryptionInterface;
use Maatify\OTPManager\Enums\OtpSenderTypeIdEnum;
use Maatify\OTPManager\Enums\RecipientTypeIdEnum;
use PDO;

class OTPAppDeviceManagerFactory
{
    public static function create(
        PDO $pdo,
        OTPEncryptionInterface $otpEncryption,
        string $tableName = 'ct_otp_code',
        RecipientTypeIdEnum $recipientTypeId = RecipientTypeIdEnum::Customer,
        EnumAppTypeId $appTypeId = EnumAppTypeId::Web,
        OtpSenderTypeIdEnum $otpSenderTypeId = OtpSenderTypeIdEnum::SMS,
        array $retryDelays = [
            60,     // First attempt (or no previous OTP) requires 60 seconds wait
            180,    // First retry attempt after failure
            300,    // Second retry attempt requires 5 minutes wait
            300,    // Third retry requires 10 minutes wait
        ],
        int $maxRolePendingOTPs = 5,   //10 role-specific limits
        int $maxTimeForDenied = 6000,
        int $expiry_of_code = 180,
    ): OTPAppDeviceManager
    {
        // Instantiate repository and handlers
        $otpRepository = new OTPAppDeviceRepository(
            pdo            : $pdo,
            otpEncryption  : $otpEncryption,
            tableName      : $tableName,
            recipientTypeId: $recipientTypeId,
            appTypeId      : $appTypeId,
            otpSenderTypeId: $otpSenderTypeId
        );

        $otpRetryHandler = new OTPAppDeviceRetryHandler(
            $retryDelays,
            $otpRepository,
            maxTimeForDenied: $maxTimeForDenied,
        );

        //        $otpRoleChecker = new OTPRoleChecker($maxDevicePendingOTPs, $maxRolePendingOTPs, $otpRepository);
        $otpRoleChecker = new OTPAppDeviceRoleChecker(sizeof($retryDelays), $maxRolePendingOTPs, $otpRepository);

        // Return fully constructed OTPManager
        return new OTPAppDeviceManager($otpRepository, $otpRoleChecker, $otpRetryHandler, $expiry_of_code);
    }
}
