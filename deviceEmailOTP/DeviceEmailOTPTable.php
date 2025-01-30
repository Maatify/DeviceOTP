<?php
/**
 * @PHP       Version >= 8.0
 * @copyright ©2025 Maatify.dev
 * @author    Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since     2025-01-29 6:34 AM
 * @link      https://www.maatify.dev Maatify.com
 * @link      https://github.com/Maatify/DeviceOTP  view project on GitHub
 * @Maatify   DeviceOTP :: Maatify\DeviceEmailOTP\DeviceEmailOTP
 */

namespace Maatify\DeviceEmailOTP;

use Maatify\DeviceOTPTraits\DeviceOTPTableTrait;

abstract class DeviceEmailOTPTable extends DeviceEmailOTP
{
    use DeviceOTPTableTrait;
}