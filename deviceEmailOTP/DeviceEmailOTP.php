<?php
/**
 * @PHP       Version >= 8.0
 * @copyright ©2025 Maatify.dev
 * @author    Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since     2025-01-29 6:34 AM
 * @link      https://www.maatify.dev Maatify.com
 * @link      https://github.com/Maatify/DeviceOTP  view project on GitHub
 * @Maatify   DeviceOTP :: DeviceEmailOTP
 */

namespace Maatify\DeviceEmailOTP;

use App\Assist\Encryptions\EmailOtpEncryption;
use App\Assist\OpensslEncryption\OpenSslKeys;
use App\DB\DBS\DbConnector;
use Maatify\AppController\Enums\EnumAppTypeId;
use Maatify\CronEmail\CronEmailCustomerRecord;
use Maatify\DeviceOtpTrait\DeviceOtpTrait;

abstract class DeviceEmailOTP extends DbConnector
{
    use DeviceOtpTrait;
    public const    TABLE_NAME                 = 'ct_email_otp';
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
    protected CronEmailCustomerRecord $corn_sender;
    protected string $device_id;
    protected EnumAppTypeId $app_type_id = EnumAppTypeId::Web;
    protected int $entity_id;

    protected array $exist = [];

    protected int $exist_count = 0;
    protected ?int $all_count_of_customer_today = null;

    public function __construct()
    {
        parent::__construct();
        $this->encryption = new EmailOtpEncryption();
        $this->corn_sender = new CronEmailCustomerRecord();
    }
}