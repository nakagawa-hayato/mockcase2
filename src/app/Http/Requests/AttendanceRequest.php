<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Carbon\Carbon;

class AttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        // 認可はここでは true（別途ポリシーやミドルウェアで制御）
        return true;
    }

        public function rules()
    {
        return [
            'clock_in_at' => 'required',
            'clock_out_at' => 'required',
            'breaks' => 'nullable',
            'breaks.*.start_time' => 'nullable',
            'breaks.*.end_time' => 'nullable',
            'reason' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'clock_in_at.required' => '出勤時間を記入してください',
            'clock_out_at.required' => '退勤時間を記入してください',
            'reason.required' => '備考を記入してください',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $data = $this->all();
            $clockIn  = $data['clock_in_at'] ?? null;
            $clockOut = $data['clock_out_at'] ?? null;

            // 出勤/退勤の前後チェック
            if ($clockIn && $clockOut && strtotime($clockIn) > strtotime($clockOut)) {
                $validator->errors()->add('clock_in_out', '出勤時間もしくは退勤時間が不適切な値です');
            }

            // 休憩時間チェック
            if (!empty($data['breaks'])) {
                foreach ($data['breaks'] as $i => $break) {
                    $start = $break['start_time'] ?? null;
                    $end   = $break['end_time'] ?? null;
                    $errorMessage = null;

                    if ($start) {
                        if ($clockIn && strtotime($start) < strtotime($clockIn)) {
                            $errorMessage = '休憩時間もしくは退勤時間が不適切な値です';
                        }
                        if ($clockOut && strtotime($start) > strtotime($clockOut)) {
                            $errorMessage = '休憩時間もしくは退勤時間が不適切な値です';
                        }
                    }

                    if ($end) {
                        if ($clockOut && strtotime($end) > strtotime($clockOut)) {
                            $errorMessage = '休憩時間もしくは退勤時間が不適切な値です';
                        }
                        if ($start && strtotime($end) < strtotime($start)) {
                            $errorMessage = '休憩時間もしくは退勤時間が不適切な値です';
                        }
                    }

                    if ($errorMessage) {
                        $validator->errors()->add("breaks.$i.start_end", $errorMessage);
                    }
                }
            }
        });
    }
}
