<?php
/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    DeviceOTP
 * @Project     DeviceOTP
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-02-02 06:28
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

/**
 * Represents different types of recipients in the system.
 *
 * Usage Example:
 *
 * ```
 *
 * $recipientType = RecipientTypeIdEnum::validate(1);
 *
 * if ($recipientType) {
 *
 *     echo "Recipient Type: " . $recipientType->getName();  // Outputs: "Customer"
 *
 * } else {
 *
 *     echo "Invalid recipient type.";
 *
 * }
 * ```
 */

declare(strict_types=1);

namespace Maatify\OTPManager\Enums;

use Maatify\OTPManager\Contracts\Enums\RecipientTypeIdInterface;

enum RecipientTypeIdEnum: int implements RecipientTypeIdInterface
{
    case Customer = 1;
    case Admin = 2;
    case Merchant = 3;
    case Channel = 4;

    /**
     * Validate and get the corresponding EnumAppTypeId case.
     *
     * @param   int  $type_id
     *
     * @return ?RecipientTypeIdInterface
     */
    public static function validate(int $type_id): ?RecipientTypeIdInterface
    {
        return self::tryFrom($type_id);
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
