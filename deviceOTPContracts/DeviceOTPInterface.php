<?php
/**
 * @PHP       Version >= 8.2
 * @copyright ©2025 Maatify.dev
 * @author    Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since     2025-01-30 7:04 AM
 * @link      https://www.maatify.dev Maatify.com
 * @link      https://github.com/Maatify/DeviceOTP  view project on GitHub
 * @Maatify   DeviceOTP :: Maatify\DeviceOTPContracts\DeviceOTPInterface
 */

namespace Maatify\DeviceOTPContracts;

use Maatify\AppController\Enums\EnumAppTypeId;

interface DeviceOTPInterface
{
    // Singleton instance getter
    public static function obj(): self;
    public function setDeviceId(string $device_id): self;
    public function getDeviceId(): string;
    public function setAppTypeId(EnumAppTypeId $appTypeId): self;
    public function getAppTypeId(): EnumAppTypeId;
    public function setEntityId(int $entityId): self;
    public function getEntityId(): int;
    public function pendingList(): array;
    public function devicePendingList(): array;
    public function getAllCustomerAppSentOFToday(): int;
    public function lastPending(int $otp_id): array;
}