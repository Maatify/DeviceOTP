<?php
/**
 * Created by Maatify.dev
 * User: Maatify.dev
 * Date: 2025-02-05
 * Time: 07:26
 * Project: DeviceOTP
 * IDE: PhpStorm
 * https://www.Maatify.dev
 */

declare(strict_types=1);

namespace Maatify\OTPManager\Contracts;

interface OTPEncryptionInterface
{
    public function hashOTP(string $otp): string;
    public function confirmOTP(string $otp, string $hash): bool;
}