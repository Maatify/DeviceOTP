<?php
/**
 * @PHP       Version >= 8.0
 * @copyright ©2023 Maatify.dev
 * @author    Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since     2024-12-11 3:51 AM
 * @link      https://www.maatify.dev Maatify.com
 * @link      https://github.com/Maatify/DeviceOTP  view project on GitHub
 * @Maatify   DeviceOTP :: DeviceSmsOTPPortal
 */


namespace Maatify\DeviceSmsOTP;

use Maatify\Portal\DbHandler\ParentClassHandler;
use Maatify\PostValidatorV2\ValidatorConstantsTypes;
use Maatify\PostValidatorV2\ValidatorConstantsValidators;

abstract class DeviceSmsOTPPortal extends ParentClassHandler
{
    public const IDENTIFY_TABLE_ID_COL_NAME = DeviceSmsOTP::IDENTIFY_TABLE_ID_COL_NAME;
    public const ENTITY_COL_NAME            = DeviceSmsOTP::ENTITY_COL_NAME;
    public const TABLE_NAME                 = DeviceSmsOTP::TABLE_NAME;
    public const TABLE_ALIAS                = DeviceSmsOTP::TABLE_ALIAS;
    public const LOGGER_TYPE                = DeviceSmsOTP::LOGGER_TYPE;
    public const LOGGER_SUB_TYPE            = DeviceSmsOTP::LOGGER_SUB_TYPE;
    public const COLS                       = DeviceSmsOTP::COLS;
    public const IMAGE_FOLDER               = self::TABLE_NAME;

    protected string $identify_table_id_col_name = self::IDENTIFY_TABLE_ID_COL_NAME;
    protected string $entity_col_name = self::ENTITY_COL_NAME;
    protected string $tableName = self::TABLE_NAME;
    protected string $tableAlias = self::TABLE_ALIAS;
    protected string $logger_type = self::LOGGER_TYPE;
    protected string $logger_sub_type = self::LOGGER_SUB_TYPE;
    protected array $cols = self::COLS;
    protected string $image_folder = self::IMAGE_FOLDER;

    // to use in list of AllPaginationThisTableFilter()
    protected array $inner_language_tables = [];

    // to use in list of source and destination rows with names
    protected string $inner_language_name_class = '';

    protected array $cols_to_add = [
        //        [ValidatorConstantsTypes::Description, ValidatorConstantsTypes::Description, ValidatorConstantsValidators::Require],
        //        [Currency::IDENTIFY_TABLE_ID_COL_NAME, ValidatorConstantsTypes::Int, ValidatorConstantsValidators::Require],
        //        ['percentage_fees', ValidatorConstantsTypes::Float, ValidatorConstantsValidators::Require],
    ];

    protected array $cols_to_edit = [
        //        [ValidatorConstantsTypes::Description, ValidatorConstantsTypes::Description, ValidatorConstantsValidators::Optional],
        //        [Currency::IDENTIFY_TABLE_ID_COL_NAME, ValidatorConstantsTypes::Int, ValidatorConstantsValidators::Optional],
        //        ['percentage_fees', ValidatorConstantsTypes::Float, ValidatorConstantsValidators::Optional],
    ];

    protected array $cols_to_filter = [
        [self::IDENTIFY_TABLE_ID_COL_NAME, ValidatorConstantsTypes::Int, ValidatorConstantsValidators::Optional],
        [self::ENTITY_COL_NAME, ValidatorConstantsTypes::Int, ValidatorConstantsValidators::Optional],
        ['is_success', ValidatorConstantsTypes::Bool, ValidatorConstantsValidators::Optional],
        ['device_id', ValidatorConstantsTypes::DeviceId, ValidatorConstantsValidators::Optional],
    ];

    // to use in add if child classes no have language_id
    protected array $child_classes = [];

    // to use in add if child classes have language_id
    protected array $child_classe_languages = [];
}