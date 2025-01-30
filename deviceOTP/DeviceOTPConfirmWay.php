<?php
/**
 * @PHP       Version >= 8.2
 * @copyright ©2025 Maatify.dev
 * @author    Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since     2025-01-30 6:44 AM
 * @link      https://www.maatify.dev Maatify.com
 * @link      https://github.com/Maatify/DeviceOTP  view project on GitHub
 * @Maatify   DeviceOTP :: Maatify\DeviceOTP\DeviceOTPConfirmWay
 */

namespace Maatify\DeviceOTP;

use Maatify\DeviceOTPContracts\DeviceOTPInterface;

class DeviceOTPConfirmWay
{
    private static self $instance;
    // Singleton instance getter
    public static function obj(): self
    {
        return self::$instance ??= new self();
    }

    private EnumDeviceOTPConfirmWay $way;

    private bool $confirm_by_sms;
    private bool $confirm_by_email;

    private function __construct(){
        $this->confirm_by_sms = !empty($_ENV['CONFIRM_BY_SMS']);
        $this->confirm_by_email = !empty($_ENV['CONFIRM_BY_EMAIL']);
    }

    public function setConfirmWay(EnumDeviceOTPConfirmWay $way): static
    {
        $this->way = $way;
        return $this;
    }

    public function getConfirmWay(): ?EnumDeviceOTPConfirmWay
    {
        return $this->way;
    }

    public function autoSetConfirmWay(DeviceOTPInterface $deviceOTPClass, int $max_numbers): ?EnumDeviceOTPConfirmWay
    {
        if($this->confirm_by_sms && $max_numbers <= $deviceOTPClass->getAllCustomerAppSentOFToday()){
            $this->setConfirmWay(EnumDeviceOTPConfirmWay::SMS);
        }elseif($this->confirm_by_email){
            $this->setConfirmWay(EnumDeviceOTPConfirmWay::EMAIL);
        }
        return $this->way;
    }
}