<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Carbon\Carbon;

class AttendanceCorrectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // TODO: 自分の勤怠記録であることなどをチェックする認可ロジックを後で実装
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'requested_start_time' => ['required', 'date_format:H:i'],
            'requested_end_time' => ['required', 'date_format:H:i', 'after:requested_start_time'],
            'reason' => ['required', 'string', 'max:400'],
            'rests' => ['present', 'array'],

            // --- 休憩関連（新規・既存共通） ---
            'rests.*.start_time' => ['nullable', 'required_with:rests.*.end_time', 'date_format:H:i', 'after:requested_start_time', 'before:requested_end_time'],
            'rests.*.end_time' => ['nullable', 'required_with:rests.*.start_time', 'date_format:H:i', 'after:rests.*.start_time', 'before:requested_end_time'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'requested_start_time.required' => '出勤時間を入力してください',
            'requested_end_time.required' => '退勤時間を入力してください',
            'requested_end_time.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'reason.required' => '備考を記入してください',

            // --- 休憩関連（新規・既存共通） ---
            'rests.*.start_time.required_with' => '休憩の開始時間を入力してください',
            'rests.*.end_time.required_with' => '休憩の終了時間を入力してください',
            'rests.*.end_time.after' => '休憩の終了時間は、開始時間より後に設定してください',
            'rests.*.start_time.after' => '休憩時間が不適切な値です',
            'rests.*.start_time.before' => '休憩時間が不適切な値です',
            'rests.*.end_time.before' => '休憩時間もしくは退勤時間が不適切な値です',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $rests = $this->input('rests', []);
            $intervals = [];

            // 全ての休憩時間を収集
            foreach ($rests as $key => $rest) {
                if (!empty($rest['start_time']) && !empty($rest['end_time'])) {
                    $intervals[] = [
                        'start' => Carbon::parse($rest['start_time']),
                        'end' => Carbon::parse($rest['end_time']),
                        'key' => $key
                    ];
                }
            }

            if (count($intervals) < 2) {
                return;
            }

            // 開始時間でソート
            usort($intervals, function ($a, $b) {
                return $a['start'] <=> $b['start'];
            });

            // 重複をチェック
            for ($i = 0; $i < count($intervals) - 1; $i++) {
                if ($intervals[$i]['end']->gt($intervals[$i + 1]['start'])) {
                    $key1 = $intervals[$i]['key'];
                    $key2 = $intervals[$i + 1]['key'];
                    $validator->errors()->add("rests.{$key1}.end_time", '休憩時間が重複しています。');
                    $validator->errors()->add("rests.{$key2}.start_time", '休憩時間が重複しています。');
                    break; // 最初の重複が見つかった時点で終了
                }
            }
        });
    }
}
