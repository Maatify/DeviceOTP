<?php
/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    DeviceOTP
 * @Project     DeviceOTP
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-02-05 10:44
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
 * Interface for recipient type enums.
 *
 * Implementing this interface allows you to extend or create custom recipient types.
 *
 * Usage Example:
 *
 * If you're working with a custom recipient system, you could create:
 *
 * ```
 * class CustomRecipientEnum implements RecipientTypeIdEnumInterface {
 *     // Custom logic here
 * }
 * ```
 */

declare(strict_types=1);

namespace Maatify\OTPManager\Contracts;

interface OTPSenderTypeIdInterface
{
    public static function validate(int $type_id): ?self;
    public function getValue(): int;

    public function getName(): string;
}