<?php
/**
 * @PHP       Version >= 8.2
 * @copyright ©2025 Maatify.dev
 * @author    Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since     2025-01-30 7:47 AM
 * @link      https://www.maatify.dev Maatify.com
 * @link      https://github.com/Maatify/DeviceOTP  view project on GitHub
 * @Maatify   DeviceOTP :: Maatify\DeviceOTPContracts\DeviceOTPValidationInterface
 */

namespace Maatify\DeviceOTPContracts;

interface DeviceOTPValidationInterface
{
    // Singleton instance getter
    public static function obj(): DeviceOTPInterface;
    public function validate(string $code): array;
}