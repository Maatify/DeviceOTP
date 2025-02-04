<?php
/**
 * Created by Maatify.dev
 * User: Maatify.dev
 * Date: 2025-02-02
 * Time: 06:37
 * Project: DeviceOTP
 * IDE: PhpStorm
 * https://www.Maatify.dev
 */

declare(strict_types=1);

namespace Maatify\OTPManager\Enums;

enum OtpSenderTypeIdEnum: int
{
    case SMS = 1;
    case EMAIL = 2;
}
