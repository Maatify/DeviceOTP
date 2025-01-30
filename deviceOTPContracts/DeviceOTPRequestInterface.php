<?php
/**
 * @PHP       Version >= 8.2
 * @copyright ©2025 Maatify.dev
 * @author    Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since     2025-01-30 7:44 AM
 * @link      https://www.maatify.dev Maatify.com
 * @link      https://github.com/Maatify/DeviceOTP  view project on GitHub
 * @Maatify   DeviceOTP :: Maatify\DeviceOTPContracts\DeviceOTPRequestInterface
 */

namespace Maatify\DeviceOTPContracts;

use Maatify\AppController\Enums\EnumAppTypeId;

interface DeviceOTPRequestInterface
{
    // Singleton instance getter
    public static function obj(): DeviceOTPInterface;
    public function initFromOtherCron(string $entity_id, EnumAppTypeId $app_type_id, string $device_id):static;
    public function recordNew(string $receiver): void;
    public function waitingSecond(): int;
    public function newOtp(string $receiver): int;
    public function sendDeviceOtp(): void;
}
