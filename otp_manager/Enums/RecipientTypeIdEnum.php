<?php
/**
 * Created by Maatify.dev
 * User: Maatify.dev
 * Date: 2025-02-02
 * Time: 06:28
 * Project: DeviceOTP
 * IDE: PhpStorm
 * https://www.Maatify.dev
 */

declare(strict_types=1);

namespace Maatify\OTPManager\Enums;

enum RecipientTypeIdEnum: int
{
    case Customer = 1;
    case Admin = 2;
    case Merchant = 3;
    case Channel = 4;
}
