<?php
/**
 * @PHP       Version >= 8.0
 * @copyright ©2025 Maatify.dev
 * @author    Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since     2025-01-29 6:58 AM
 * @link      https://www.maatify.dev Maatify.com
 * @link      https://github.com/Maatify/DeviceOTP  view project on GitHub
 * @Maatify   DeviceOTP :: Maatify\DeviceOtpTrait\DeviceOtpTrait
 */

namespace Maatify\DeviceOTPTrait;

use Maatify\AppController\Enums\EnumAppTypeId;

trait DeviceOTPTrait
{

    public function setDeviceId(string $device_id): self
    {
        $this->device_id = $device_id;

        return $this;
    }

    public function getDeviceId(): string
    {
        return $this->device_id;
    }

    public function setAppTypeId(EnumAppTypeId $appTypeId): self
    {
        $this->app_type_id = $appTypeId;

        return $this;
    }

    public function getAppTypeId(): EnumAppTypeId
    {
        return $this->app_type_id;
    }

    public function setEntityId(int $entityId): self
    {
        $this->entity_id = $entityId;

        return $this;
    }

    public function getEntityId(): int
    {
        return $this->entity_id;
    }

    public function pendingList(): array
    {
        return $this->RowsThisTable(
            "`$this->identify_table_id_col_name`, `code`, `time`, `expiry`",
            "`$this->entity_col_name` = ? 
            AND `app_type_id` = ?  
            AND `is_success` = ? 
            AND `device_id` = ? 
            ORDER BY `$this->identify_table_id_col_name` DESC",
            [$this->entity_id, $this->app_type_id->value, 0, $this->device_id]
        );
    }

    public function devicePendingList(): array
    {
        if(is_null($this->all_count_of_customer_app_today)){
            $this->allCountOfCustomerAppToday();
        }

        $this->exist = $this->Rows(
            "`$this->tableName` 
            LEFT JOIN `$this->tableName` as pending 
                ON `pending`.`$this->identify_table_id_col_name` > `$this->tableName`.`$this->identify_table_id_col_name` 
                AND `pending`.`is_success` = '1' ",

            "`$this->tableName`.*",

            "`$this->tableName`.`$this->entity_col_name` = ? 
            AND `$this->tableName`.`app_type_id` = ? 
            AND `$this->tableName`.`is_success` = ?
            AND `$this->tableName`.`device_id` = ? 
            ORDER BY `$this->tableName`.`$this->identify_table_id_col_name` DESC",

            [$this->entity_id, $this->app_type_id->value, 0, $this->device_id]);

        if(!empty($this->exist)){
            $this->exist_count = sizeof($this->exist);
        }

        return $this->exist;
    }

    public function getAllCustomerAppSentOFToday(): int
    {
        return $this->allCountOfCustomerAppToday();
    }

    protected function allCountOfCustomerAppToday(): int
    {
        if(is_null($this->all_count_of_customer_app_today)) {
            $this->all_count_of_customer_app_today = $this->CountThisTableRows(self::IDENTIFY_TABLE_ID_COL_NAME,
                "`time` >= CURDATE() 
            AND `$this->entity_col_name` = ? 
            AND `app_type_id` = ? ",
                [$this->entity_id, $this->app_type_id->value]);
        }

        return $this->all_count_of_customer_app_today;
    }

    public function lastPending(int $otp_id): array
    {
        return $this->RowThisTable(
            "`$this->identify_table_id_col_name`, `time`, `expiry`",
            "`$this->entity_col_name` = ? 
            AND `is_success` = ? 
            ORDER BY `$this->identify_table_id_col_name` DESC LIMIT 1",
            [$otp_id, 0]
        );
    }
}