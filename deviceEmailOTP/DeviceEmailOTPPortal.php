<?php
/**
 * @PHP       Version >= 8.0
 * @copyright ©2025 Maatify.dev
 * @author    Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since     2025-01-29 6:35 AM
 * @link      https://www.maatify.dev Maatify.com
 * @link      https://github.com/Maatify/DeviceOTP  view project on GitHub
 * @Maatify   DeviceOTP :: Maatify\DeviceEmailOTP\DeviceEmailOTPPortal
 */

namespace Maatify\DeviceEmailOTP;

use Maatify\Portal\DbHandler\ParentClassHandler;
use Maatify\PostValidatorV2\ValidatorConstantsTypes;
use Maatify\PostValidatorV2\ValidatorConstantsValidators;

abstract class DeviceEmailOTPPortal extends ParentClassHandler
{
    public const IDENTIFY_TABLE_ID_COL_NAME = DeviceEmailOTP::IDENTIFY_TABLE_ID_COL_NAME;
    public const ENTITY_COL_NAME            = DeviceEmailOTP::ENTITY_COL_NAME;
    public const TABLE_NAME                 = DeviceEmailOTP::TABLE_NAME;
    public const TABLE_ALIAS                = DeviceEmailOTP::TABLE_ALIAS;
    public const LOGGER_TYPE                = DeviceEmailOTP::LOGGER_TYPE;
    public const LOGGER_SUB_TYPE            = DeviceEmailOTP::LOGGER_SUB_TYPE;
    public const COLS                       = DeviceEmailOTP::COLS;
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