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
use Maatify\OTPManager\Contracts\OTPRepositoryInterface;
use Maatify\OTPManager\Enums\OTPSenderTypeIdEnum;
use Maatify\OTPManager\Enums\RecipientTypeIdEnum;
use Maatify\OTPManager\Service\OTPSenderTypeIdService;
use PDO;

class OTPRepository implements OTPRepositoryInterface
{
    private PDO $pdo;
    private string $tableName;
    private RecipientTypeIdInterface $recipientTypeId;
    private AppTypeIdInterface $appTypeId;
    private OTPSenderTypeIdInterface $otpSenderTypeId;
    private int $otp_sender_type_id;

    private int $otp_id;

    public function __construct(
        PDO $pdo,
        private readonly OTPEncryptionInterface $otpEncryption,
        string $tableName = 'ct_otp_code',
        RecipientTypeIdInterface $recipientTypeId = RecipientTypeIdEnum::Customer,
        AppTypeIdInterface $appTypeId = AppTypeIdEnum::Web,
        OTPSenderTypeIdInterface $otpSenderTypeId = OTPSenderTypeIdEnum::SMS
    )
    {
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
                AND t2.is_success > 0
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

    public function countAllTypesPendingOTPsForRole(int $recipientId): array
    {
        $stmt = $this->pdo->prepare("
        SELECT CAST(t1.otp_sender_type_id AS SIGNED) AS otp_sender_type_id,
        COUNT(*) AS count,
        MAX(t1.time) AS last_time
        FROM {$this->tableName} t1
        WHERE t1.recipient_type_id = :recipient_type_id 
          AND t1.recipient_id = :recipient_id
          AND t1.app_type_id = :app_type_id
          AND t1.is_success = 0
          AND t1.otp_id > (
              SELECT COALESCE(MAX(t2.otp_id), 0) 
              FROM {$this->tableName} t2
              WHERE t2.recipient_type_id = t1.recipient_type_id
                AND t2.recipient_id = t1.recipient_id
                AND t2.app_type_id = t1.app_type_id
                AND t2.is_success > 0
                AND t2.otp_sender_type_id = t1.otp_sender_type_id
          )
        GROUP BY t1.otp_sender_type_id
    ");

        $stmt->execute([
            ':recipient_type_id'  => $this->recipientTypeId->getValue(),
            ':recipient_id'       => $recipientId,
            ':app_type_id'        => $this->appTypeId->getValue(),
        ]);

        return $stmt->fetchAll(); // Return the array of count of pending OTPs
    }


    public function countPendingOTPs(int $recipientId, string $deviceId = ''): int
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
                AND t2.is_success > 0
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

    public function countAllTypesPendingOTPs(
        int $recipientId,
        string $deviceId = ''
    ): array
    {
        $stmt = $this->pdo->prepare("
        SELECT CAST(t1.otp_sender_type_id AS SIGNED) AS otp_sender_type_id,
        COUNT(*) AS count,
        MAX(t1.time) AS last_time
        FROM {$this->tableName} t1
        WHERE t1.recipient_type_id = :recipient_type_id 
          AND t1.recipient_id = :recipient_id 
          AND t1.device_id = :device_id
          AND t1.app_type_id = :app_type_id
          AND t1.is_success = 0 
          AND t1.otp_id > (
              SELECT COALESCE(MAX(t2.otp_id), 0) 
              FROM {$this->tableName} t2
              WHERE t2.recipient_type_id = t1.recipient_type_id
                AND t2.recipient_id = t1.recipient_id
                AND t2.device_id = t1.device_id
                AND t2.app_type_id = t1.app_type_id
                AND t2.is_success > 0
                AND t2.otp_sender_type_id = t1.otp_sender_type_id
          )
        GROUP BY t1.otp_sender_type_id
    ");

        $stmt->execute([
            ':recipient_type_id'  => $this->recipientTypeId->getValue(),
            ':recipient_id'       => $recipientId,
            ':device_id'          => $deviceId,
            ':app_type_id'        => $this->appTypeId->getValue(),
        ]);

        return $stmt->fetchAll(); // Return the array of count of pending OTPs
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
                AND t2.is_success > 0
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

    public function insertOTP(int $recipientId, string $otpCodeHashed, int $expiry_of_code, string $deviceId = ''): void
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
            ':code'               => $otpCodeHashed,
            ':expiry'             => $expiry_of_code,
            ':otp_sender_type_id' => $this->otpSenderTypeId->getValue(),
        ]);
    }

    public function confirmOTP(int $recipientId, string $deviceId, string $otpCode, bool $terminate_all_valide_codes = false, bool $confirm_by_any_sender_type = false): int
    {
        $query_string = "
            SELECT t1.otp_id, t1.code, TIMESTAMPDIFF(SECOND, t1.`time`, NOW()) AS elapsed_time, t1.expiry, t1.otp_sender_type_id
            FROM {$this->tableName} t1
            WHERE t1.recipient_type_id = :recipient_type_id 
              AND t1.recipient_id = :recipient_id 
              AND t1.device_id = :device_id 
              AND t1.app_type_id = :app_type_id 
              AND t1.is_success = 0 
              
              AND t1.otp_id > (
                  SELECT IFNULL(MAX(otp_id), 0) 
                  FROM {$this->tableName} t2
                  WHERE t2.recipient_type_id = t1.recipient_type_id
                    AND t2.recipient_id = t1.recipient_id
                    AND t2.device_id = t1.device_id
                    AND t2.app_type_id = t1.app_type_id
                    AND t2.is_success > 0
        ";

        $params = [
            ':recipient_type_id'  => $this->recipientTypeId->getValue(),
            ':recipient_id'       => $recipientId,
            ':device_id'          => $deviceId,
            ':app_type_id'        => $this->appTypeId->getValue(),
        ];

        if(!$confirm_by_any_sender_type) {
            $query_string .= " AND t2.otp_sender_type_id = t1.otp_sender_type_id
            )
            AND t1.otp_sender_type_id = :otp_sender_type_id ";

            $params[':otp_sender_type_id'] = $this->otpSenderTypeId->getValue();
        }else{
            $query_string .= ")";
        }

        $query_string .= " ORDER BY t1.otp_id DESC; "; //-- Check all pending OTPs in order of creation

        $stmt = $this->pdo->prepare($query_string);

        $stmt->execute($params);


        // Fetch the first row
        $otpRow = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if no rows were returned
        if (! $otpRow) {
            return 404;  // No matching OTP found
        }

        // Loop through the result set if the first row exists
        do {
            if ($this->otpEncryption->confirmOTP($otpCode, $otpRow['code'])) {
                if ($otpRow['elapsed_time'] <= $otpRow['expiry']) {
                    // Mark this specific OTP as used
                    $updateStmt = $this->pdo->prepare("UPDATE {$this->tableName} SET is_success = 1 WHERE otp_id = :otp_id");
                    $updateStmt->execute([':otp_id' => $otpRow['otp_id']]);

                    if ($terminate_all_valide_codes) {
                        // Mark all codes as expired for customer and device and app_type
                        $updateStmt = $this->pdo->prepare(
                            "UPDATE {$this->tableName} SET is_success = 2
             WHERE recipient_type_id = :recipient_type_id 
               AND recipient_id = :recipient_id 
               AND device_id = :device_id 
               AND app_type_id = :app_type_id 
               AND is_success = 0 
               AND otp_id > :otp_id");
                        $updateStmt->execute([
                            ':recipient_type_id' => $this->recipientTypeId->getValue(),
                            ':recipient_id'      => $recipientId,
                            ':device_id'         => $deviceId,
                            ':app_type_id'       => $this->appTypeId->getValue(),
                            ':otp_id'            => $otpRow['otp_id'],
                        ]);

                        $this->otp_sender_type_id = (int)$otpRow['otp_sender_type_id'];
                    }
                    $this->otp_id = (int)$otpRow['otp_id'];
                    return 200;
                } else {
                    // Expired Code
                    $this->otp_sender_type_id = (int)$otpRow['otp_sender_type_id'];
                    return 410;
                }
            }
        } while ($otpRow = $stmt->fetch(PDO::FETCH_ASSOC));  // Continue fetching rows


        // If no valid OTP was found after looping through
        return 401;
    }

    public function getOtpSenderTypeId(): int
    {
        return $this->otp_sender_type_id;
    }

    public function getOtpId(): int
    {
        return $this->otp_id;
    }

}
