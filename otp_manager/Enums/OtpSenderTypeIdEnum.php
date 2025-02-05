<?php
/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    DeviceOTP
 * @Project     DeviceOTP
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-02-02 06:37
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

namespace Maatify\OTPManager\Enums;

use Maatify\OTPManager\Contracts\OtpSenderTypeIdEnumInterface;

enum OtpSenderTypeIdEnum: int implements OtpSenderTypeIdEnumInterface
{
    case SMS = 1;
    case EMAIL = 2;
    case PUSH_NOTIFICATION = 3;
    case WHATSAPP = 4;
    case TELEGRAM = 5;
    case FACEBOOK = 6;
    case TWITTER = 7;
    case LINKEDIN = 8;

    public function getValue(): int
    {
        return $this->value;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
