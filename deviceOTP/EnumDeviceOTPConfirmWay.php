<?php
/**
 * @PHP       Version >= 8.2
 * @copyright ©2025 Maatify.dev
 * @author    Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since     2025-01-30 6:43 AM
 * @link      https://www.maatify.dev Maatify.com
 * @link      https://github.com/Maatify/DeviceOTP  view project on GitHub
 * @Maatify   DeviceOTP :: Maatify\DeviceOTP\EnumDeviceOTPConfirmWay
 */

namespace Maatify\DeviceOTP;

enum EnumDeviceOTPConfirmWay: string
{
    case SMS = 'sms';

    case EMAIL = 'email';
}