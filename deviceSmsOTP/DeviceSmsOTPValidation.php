<?php
/**
 * @PHP       Version >= 8.0
 * @copyright ©2023 Maatify.dev
 * @author    Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since     2024-12-11 4:22 AM
 * @link      https://www.maatify.dev Maatify.com
 * @link      https://github.com/Maatify/DeviceOTP  view project on GitHub
 * @Maatify   DeviceOTP :: DeviceSmsOTPValidation
 */

namespace Maatify\DeviceSmsOTP;

use Maatify\DeviceOtpTrait\DeviceOTPValidationTrait;

abstract class DeviceSmsOTPValidation extends DeviceSmsOTP
{
    use DeviceOTPValidationTrait;
}