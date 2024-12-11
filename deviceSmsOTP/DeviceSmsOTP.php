<?php

/**
 * @PHP       Version >= 8.0
 * @copyright ©2023 Maatify.dev
 * @author    Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since     2024-12-11 3:43 AM
 * @link      https://www.maatify.dev Maatify.com
 * @link      https://github.com/Maatify/DeviceOTP  view project on GitHub
 * @Maatify   DeviceOTP :: DeviceSmsOTP
 */


namespace Maatify\DeviceSmsOTP;

use \App\Assist\Encryptions\SmsOtpEncryption;
use \App\DB\DBS\DbConnector;
use Maatify\CronSms\CronSmsAdminRecord;
use Maatify\CronSms\CronSmsCustomerRecord;

abstract class DeviceSmsOTP extends DbConnector
{
    public const    TABLE_NAME                 = 'ct_sms_otp';
    public const    TABLE_ALIAS                = '';
    public const    IDENTIFY_TABLE_ID_COL_NAME = 'otp_id';
    public const    ENTITY_COL_NAME            = 'ct_id';
    public const    EXPIRY                     = 60;

    public const LOGGER_TYPE     = self::TABLE_NAME;
    public const LOGGER_SUB_TYPE = '';
    public const COLS            = [
        self::IDENTIFY_TABLE_ID_COL_NAME => 1,
        self::ENTITY_COL_NAME            => 1,
        'device_id'                      => 0,
        'code'                           => 0,
        'time'                           => 0,
        'expiry'                         => 0,
        'is_success'                     => 1,
    ];

    protected string $tableName = self::TABLE_NAME;
    protected string $tableAlias = self::TABLE_ALIAS;
    protected string $identify_table_id_col_name = self::IDENTIFY_TABLE_ID_COL_NAME;
    protected string $entity_col_name = self::ENTITY_COL_NAME;
    protected int $expiry_time = self::EXPIRY;
    protected string $logger_type = self::LOGGER_TYPE;
    protected string $logger_sub_type = self::LOGGER_SUB_TYPE;
    protected array $cols = self::COLS;

    protected SmsOtpEncryption $encryption;
    protected CronSmsCustomerRecord|CronSmsAdminRecord $corn_sender;

    public function __construct()
    {
        parent::__construct();
        $this->encryption = new SmsOtpEncryption();
        $this->corn_sender = new CronSmsCustomerRecord();
    }

    public function pendingList(int $entity_id, string $device_id): array
    {
        return $this->RowsThisTable(
            "`$this->identify_table_id_col_name`, `code`, `time`, `expiry`",
            "`$this->entity_col_name` = ? AND `is_success` = ? AND `device_id` = ? 
            ORDER BY `$this->identify_table_id_col_name` DESC",
            [$entity_id, 0, $device_id]
        );
    }

    public function devicePendingList(int $entity_id, string $device_id): array
    {
        return $this->Rows(
            "`$this->tableName` 
            LEFT JOIN `$this->tableName` as pending 
                ON `pending`.`$this->identify_table_id_col_name` > `$this->tableName`.`$this->identify_table_id_col_name` 
                AND `pending`.`is_success` = '1' ",

            "`$this->tableName`.*",

            "`$this->tableName`.`$this->entity_col_name` = ? 
            AND `$this->tableName`.`is_success` = ?
            AND `$this->tableName`.`device_id` = ? 
            ORDER BY `$this->tableName`.`$this->identify_table_id_col_name` DESC",

            [$entity_id, 0, $device_id]);
    }

    public function lastPending(int $otp_id): array
    {
        return $this->RowThisTable(
            "`$this->identify_table_id_col_name`, `time`, `expiry`",
            "`$this->entity_col_name` = ? AND `is_success` = ? 
            ORDER BY `$this->identify_table_id_col_name` DESC LIMIT 1",
            [$otp_id, 0]
        );
    }

}