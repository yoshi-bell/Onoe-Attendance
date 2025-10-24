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
        $rules = [
            'requested_start_time' => ['required', 'date_format:H:i'],
            'requested_end_time' => ['required', 'date_format:H:i', 'after:requested_start_time'],
            'reason' => ['required', 'string', 'max:400'],
            'rests' => ['present', 'array'],

            // 「新規追加」の休憩フォーム
            'rests.new.start_time' => ['nullable', 'required_with:rests.new.end_time', 'date_format:H:i', 'after:requested_start_time'],
            'rests.new.end_time' => ['nullable', 'required_with:rests.new.start_time', 'date_format:H:i', 'after:rests.new.start_time', 'before:requested_end_time'],
        ];

        // 既存の休憩
        $submittedRests = $this->input('rests', []);
        foreach ($submittedRests as $key => $value) {
            if (is_numeric($key)) {
                $rules["rests.{$key}.start_time"] = ['required', 'date_format:H:i', 'after:requested_start_time'];
                $rules["rests.{$key}.end_time"] = ['required', 'date_format:H:i', 'after:rests.'.$key.'.start_time', 'before:requested_end_time'];
            }
        }

        return $rules;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        $messages = [
            'requested_start_time.required' => '出勤時間を入力してください。',
            'requested_end_time.required' => '退勤時間を入力してください。',
            'requested_end_time.after' => '出勤時間もしくは退勤時間が不適切な値です。',
            'reason.required' => '備考を記入してください。',
            // 新規追加フォーム用のメッセージ
            'rests.new.start_time.required_with' => '休憩追加の開始時間を入力してください。',
            'rests.new.end_time.required_with' => '休憩追加の終了時間を入力してください。',
            'rests.new.end_time.after' => '休憩追加の時間が不適切です。',
            'rests.new.start_time.after' => '休憩時間が不適切な値です。',
            'rests.new.end_time.before' => '休憩時間もしくは退勤時間が不適切な値です。',
        ];

        // 既存の休憩データを取得
        $submittedRests = $this->input('rests', []);
        if (isset($submittedRests['new'])) {
            unset($submittedRests['new']);
        }

        foreach ($submittedRests as $key => $value) {
            if (is_numeric($key)) {
                // 既存の休憩用のメッセージ
                $messages["rests.{$key}.start_time.required"] = '既存の休憩の開始時間は必須です。';
                $messages["rests.{$key}.end_time.required"] = '既存の休憩の終了時間は必須です。';
                $messages["rests.{$key}.end_time.after"] = '休憩の時間が不適切な値です。';
                $messages["rests.{$key}.start_time.after"] = '休憩時間が不適切な値です。';
                $messages["rests.{$key}.end_time.before"] = '休憩時間もしくは退勤時間が不適切な値です。';
            }
        }

        return $messages;
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
