<?php
/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    DeviceOTP
 * @Project     DeviceOTP
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-01-31 21:09 PM
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

use Maatify\AppController\Contracts\AppTypeIdInterface;
use Maatify\AppController\Enums\AppTypeIdEnum;
use Maatify\OTPManager\Contracts\OTPEncryptionInterface;
use Maatify\OTPManager\Contracts\OTPSenderTypeIdInterface;
use Maatify\OTPManager\Contracts\RecipientTypeIdInterface;
use Maatify\OTPManager\Enums\OTPSenderTypeIdEnum;
use Maatify\OTPManager\Enums\RecipientTypeIdEnum;
use PDO;

class OTPRepository
{
    private PDO $pdo;
    private string $tableName;
    private RecipientTypeIdInterface $recipientTypeId;
    private AppTypeIdInterface $appTypeId;
    private OTPSenderTypeIdInterface $otpSenderTypeId;

    private OTPEncryptionInterface $otpEncryption;

    public function __construct(
        PDO $pdo,
        OTPEncryptionInterface $otpEncryption,
        string $tableName = 'ct_otp_code',
        RecipientTypeIdInterface $recipientTypeId = RecipientTypeIdEnum::Customer,
        AppTypeIdInterface $appTypeId = AppTypeIdEnum::Web,
        OTPSenderTypeIdInterface $otpSenderTypeId = OTPSenderTypeIdEnum::SMS
    )
    {
        $this->otpEncryption = $otpEncryption;
        $this->pdo = $pdo;
        $this->tableName = $tableName;
        $this->recipientTypeId = $recipientTypeId;
        $this->appTypeId = $appTypeId;
        $this->otpSenderTypeId = $otpSenderTypeId;
    }

    public function countPendingOTPsForRole(int $recipientId): int
    {
        $stmt = $this->pdo->prepare("
        SELECT COUNT(*) AS pending_count
        FROM {$this->tableName} t1
        WHERE t1.recipient_type_id = :recipient_type_id 
          AND t1.recipient_id = :recipient_id
          AND t1.app_type_id = :app_type_id
          AND t1.otp_sender_type_id = :otp_sender_type_id
          AND t1.is_success = 0
          AND t1.otp_id > (
              SELECT COALESCE(MAX(t2.otp_id), 0) 
              FROM {$this->tableName} t2
              WHERE t2.recipient_type_id = t1.recipient_type_id
                AND t2.recipient_id = t1.recipient_id
                AND t2.app_type_id = t1.app_type_id
                AND t2.is_success = 1
                AND t2.otp_sender_type_id = t1.otp_sender_type_id
          )
    ");

        $stmt->execute([
            ':recipient_type_id'  => $this->recipientTypeId->getValue(),
            ':recipient_id'       => $recipientId,
            ':app_type_id'        => $this->appTypeId->getValue(),
            ':otp_sender_type_id' => $this->otpSenderTypeId->getValue(),
        ]);

        return (int)$stmt->fetchColumn(); // Return the count of pending OTPs
    }


    public function countPendingOTPs(
        int $recipientId,
        string $deviceId = ''
    ): int
    {
        $stmt = $this->pdo->prepare("
        SELECT COUNT(*) AS pending_count
        FROM {$this->tableName} t1
        WHERE t1.recipient_type_id = :recipient_type_id 
          AND t1.recipient_id = :recipient_id 
          AND t1.device_id = :device_id
          AND t1.app_type_id = :app_type_id
          AND t1.otp_sender_type_id = :otp_sender_type_id
          AND t1.is_success = 0 
          AND t1.otp_id > (
              SELECT COALESCE(MAX(t2.otp_id), 0) 
              FROM {$this->tableName} t2
              WHERE t2.recipient_type_id = t1.recipient_type_id
                AND t2.recipient_id = t1.recipient_id
                AND t2.device_id = t1.device_id
                AND t2.app_type_id = t1.app_type_id
                AND t2.is_success = 1
                AND t2.otp_sender_type_id = t1.otp_sender_type_id
          )
    ");

        $stmt->execute([
            ':recipient_type_id'  => $this->recipientTypeId->getValue(),
            ':recipient_id'       => $recipientId,
            ':device_id'          => $deviceId,
            ':app_type_id'        => $this->appTypeId->getValue(),
            ':otp_sender_type_id' => $this->otpSenderTypeId->getValue(),
        ]);

        return (int)$stmt->fetchColumn(); // Return the count of pending OTPs
    }


    public function getLastRequestTime(
        int $recipientId,
        string $deviceId = ''
    ): int
    {
        $stmt = $this->pdo->prepare("
        SELECT IFNULL(TIMESTAMPDIFF(SECOND, `time`, NOW()), 0) AS last_request 
        FROM {$this->tableName} t1
        WHERE t1.recipient_type_id = :recipient_type_id 
          AND t1.recipient_id = :recipient_id 
          AND t1.device_id = :device_id
          AND t1.app_type_id = :app_type_id
          AND t1.otp_sender_type_id = :otp_sender_type_id
          AND t1.is_success = 0 
          AND t1.otp_id > (
              SELECT IFNULL(MAX(otp_id), 0) 
              FROM {$this->tableName} t2
              WHERE t2.recipient_type_id = t1.recipient_type_id
                AND t2.recipient_id = t1.recipient_id
                AND t2.device_id = t1.device_id
                AND t2.app_type_id = t1.app_type_id
                AND t2.is_success = 1
                AND t2.otp_sender_type_id = t1.otp_sender_type_id
          )
        ORDER BY t1.otp_id DESC
        LIMIT 1;
    ");

        $stmt->execute([
            ':recipient_type_id'  => $this->recipientTypeId->getValue(),
            ':recipient_id'       => $recipientId,
            ':device_id'          => $deviceId,
            ':app_type_id'        => $this->appTypeId->getValue(),
            ':otp_sender_type_id' => $this->otpSenderTypeId->getValue(),
        ]);

        return (int)$stmt->fetchColumn(); // Return the last time
    }

    public function insertOTP(int $recipientId, string $otpCode, int $expiry, string $deviceId = ''): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO {$this->tableName} (recipient_type_id, recipient_id, app_type_id, device_id, code, `time`, expiry, otp_sender_type_id)
            VALUES (:recipient_type_id, :recipient_id, :app_type_id, :device_id, :code, NOW(), :expiry, :otp_sender_type_id)
        ");
        $stmt->execute([
            ':recipient_type_id'  => $this->recipientTypeId->getValue(),
            ':recipient_id'       => $recipientId,
            ':app_type_id'        => $this->appTypeId->getValue(),
            ':device_id'          => $deviceId,
            ':code'               => $otpCode,
            ':expiry'             => $expiry,
            ':otp_sender_type_id' => $this->otpSenderTypeId->getValue(),
        ]);
    }

    public function confirmOTP(int $recipientId, string $deviceId, string $otpCode): int
    {
        $stmt = $this->pdo->prepare("
            SELECT t1.otp_id, t1.code, TIMESTAMPDIFF(SECOND, t1.`time`, NOW()) AS elapsed_time, t1.expiry
            FROM {$this->tableName} t1
            WHERE t1.recipient_type_id = :recipient_type_id 
              AND t1.recipient_id = :recipient_id 
              AND t1.device_id = :device_id 
              AND t1.app_type_id = :app_type_id 
              AND t1.is_success = 0 
              AND t1.otp_sender_type_id = :otp_sender_type_id 
              AND t1.otp_id > (
                  SELECT IFNULL(MAX(otp_id), 0) 
                  FROM {$this->tableName} t2
                  WHERE t2.recipient_type_id = t1.recipient_type_id
                    AND t2.recipient_id = t1.recipient_id
                    AND t2.device_id = t1.device_id
                    AND t2.app_type_id = t1.app_type_id
                    AND t2.is_success = 1
                    AND t2.otp_sender_type_id = t1.otp_sender_type_id
              )
            ORDER BY t1.otp_id DESC;  -- Check all pending OTPs in order of creation
        ");

        $stmt->execute([
            ':recipient_type_id'  => $this->recipientTypeId->getValue(),
            ':recipient_id'       => $recipientId,
            ':device_id'          => $deviceId,
            ':app_type_id'        => $this->appTypeId->getValue(),
            ':otp_sender_type_id' => $this->otpSenderTypeId->getValue(),
        ]);


        // Fetch the first row
        $otpRow = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if no rows were returned
        if (!$otpRow) {
            return 404;  // No matching OTP found
        }

        // Loop through the result set if the first row exists
        do {
            if ($this->otpEncryption->confirmOTP($otpCode, $otpRow['code'])) {
                if ($otpRow['elapsed_time'] <= $otpRow['expiry']) {
                    // Mark this specific OTP as used
                    $updateStmt = $this->pdo->prepare("UPDATE {$this->tableName} SET is_success = 1 WHERE otp_id = :otp_id");
                    $updateStmt->execute([':otp_id' => $otpRow['otp_id']]);

                    return 200;
                } else {
                    // Expired Code
                    return 410;
                }
            }
        } while ($otpRow = $stmt->fetch(PDO::FETCH_ASSOC));  // Continue fetching rows


        // If no valid OTP was found after looping through
        return 401;
    }

}
