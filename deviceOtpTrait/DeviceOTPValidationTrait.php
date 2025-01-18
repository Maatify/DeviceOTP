<?php
/**
 * Created by Maatify.dev
 * User: Maatify.dev
 * Date: 2025-01-29
 * Time: 7:31 AM
 * https://www.Maatify.dev
 */

namespace Maatify\DeviceOtpTrait;

trait DeviceOTPValidationTrait
{
    public function validate(string $code): array
    {
        if ($exists = $this->pendingList()) {
            foreach ($exists as $exist) {
                if (password_verify($code, $this->encryption->DeHashed((string)$exist['code']))) {
                    if (strtotime($exist['time'] . ' + ' . $exist['expiry'] . ' minute') >= time()) {
                        //                        CustomerDeviceFieldsSms::obj()->RemoveFieldSms();
                        $this->markCodeSuccess($exist[self::IDENTIFY_TABLE_ID_COL_NAME]);

                        return [
                            'status' => true,
                            'code'   => $exist['code'],
                        ];
                    } else {
                        //                        CustomerLoginLog::obj()->RecordNew($ct_id, 'Expired SMS Code');
                        //                        Json::ErrorWithHeader400(401701, 'code expired', $this->class_name . __LINE__);
                        return [
                            'status' => false,
                            'code'   => 401701,
                        ];
                    }
                }
            }
            //            CustomerDeviceFieldsSms::obj()->AddFieldSms();
            //            AppLoginIncorrect::obj()->IncorrectLog($ct_id, 'Wrong SMS Code');
            //            Json::Incorrect('code', line: $this->class_name . __LINE__);
            return [
                'status' => false,
                'code'   => 2000,
            ];
        }

        //        Json::NotExist('code', 'Please request a new SMS', $this->class_name . __LINE__);
        return [
            'status' => false,
            'code'   => 6000,
        ];
    }

    private function markCodeSuccess(int $sms_id): void
    {
        $this->Edit(['is_success' => 1,], "`$this->identify_table_id_col_name` = ?", [$sms_id]);
    }
}