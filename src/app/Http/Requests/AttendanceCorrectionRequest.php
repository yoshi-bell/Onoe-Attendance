<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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

            // 「新規追加」の休憩フォームは、入力が任意（ただし片方入力したらもう片方も必須）
            'rests.new.start_time' => ['nullable', 'required_with:rests.new.end_time', 'date_format:H:i'],
            'rests.new.end_time' => ['nullable', 'required_with:rests.new.start_time', 'date_format:H:i', 'after:rests.new.start_time'],
        ];

        // フォームから送信された休憩データを取得
        $submittedRests = $this->input('rests', []);

        // 既存の休憩（キーが数値のもの）に対して、動的に必須ルールを追加
        foreach ($submittedRests as $key => $value) {
            if (is_numeric($key)) {
                $rules["rests.{$key}.start_time"] = ['required', 'date_format:H:i'];
                $rules["rests.{$key}.end_time"] = ['required', 'date_format:H:i', 'after:rests.'.$key.'.start_time'];
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
                $messages["rests.{$key}.end_time.after"] = '既存の休憩の時間が不適切です。';
            }
        }

        return $messages;
    }
}
