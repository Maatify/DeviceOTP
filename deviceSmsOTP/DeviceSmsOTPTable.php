<?php
/**
 * Created by Maatify.dev
 * User: Maatify.dev
 * Date: 2025-01-30
 * Time: 12:25
 * Project: DeviceOTP
 * IDE: PhpStorm
 * https://www.Maatify.dev
 */

namespace Maatify\DeviceSmsOTP;

use Maatify\DeviceOTPTraits\DeviceOTPTableTrait;

abstract class DeviceSmsOTPTable extends DeviceSmsOTP
{
    use DeviceOTPTableTrait;
}