<?php

/**
 * @PHP       Version >= 8.0
 * @copyright ©2023 Maatify.dev
 * @author    Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since     2024-12-11 3:43 AM
 * @link      https://www.maatify.dev Maatify.com
 * @link      https://github.com/Maatify/DeviceOTP  view project on GitHub
 * @Maatify   DeviceOTP :: Maatify\DeviceSmsOTP\DeviceSmsOTP
 */


namespace Maatify\DeviceSmsOTP;

use \App\Assist\Encryptions\SmsOtpEncryption;
use App\Assist\OpensslEncryption\OpenSslKeys;
use \App\DB\DBS\DbConnector;
use Maatify\AppController\Enums\EnumAppTypeId;
use Maatify\CronSms\CronRecordInterface;
use Maatify\CronSms\CronSmsCustomerRecord;
use Maatify\DeviceOTPContracts\DeviceOTPInterface;
use Maatify\DeviceOTPTrait\DeviceOTPTrait;

abstract class DeviceSmsOTP extends DbConnector implements DeviceOTPInterface
{
    use DeviceOTPTrait;

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
        'app_type_id'                    => 1,
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

    protected OpenSslKeys $encryption;
    protected CronRecordInterface $corn_sender;

    protected string $device_id;
    protected EnumAppTypeId $app_type_id = EnumAppTypeId::Web;
    protected int $entity_id;

    protected array $exist = [];

    protected int $exist_count = 0;

    protected ?int $all_count_of_customer_app_today = null;
    public function __construct()
    {
        parent::__construct();
        $this->encryption = new SmsOtpEncryption();
        $this->corn_sender = new CronSmsCustomerRecord();
    }
}