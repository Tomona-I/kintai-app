<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
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
            'attendance_id' => ['required', 'exists:attendances,id'],
            'clock_in' => ['required', 'date_format:H:i'],
            'clock_out' => ['required','date_format:H:i'],
            'breaks' => ['nullable', 'array'],
            'breaks.*.start' => ['nullable', 'date_format:H:i'],
            'breaks.*.end' => ['nullable', 'date_format:H:i'],
            'notes' => ['required', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'notes.required' => '備考を記入してください',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $in  = $this->input('clock_in');
            $out = $this->input('clock_out');
            $breaks = $this->input('breaks', []);

            // 出勤・退勤時刻のバリデーション
            if ($in >= $out) {
                $validator->errors()->add(
                    'clock_out',
                    '出勤時間もしくは退勤時間が不適切な値です'
                );
            }

            // 各休憩時間のバリデーション
            foreach ($breaks as $index => $break) {
                $breakStart = $break['start'] ?? null;
                $breakEnd = $break['end'] ?? null;

                // 休憩開始時刻が勤務時間内かチェック
                if ($breakStart && !($in <= $breakStart && $breakStart <= $out)) {
                    $validator->errors()->add(
                        "breaks.{$index}.start",
                        '休憩時間が不適切な値です'
                    );
                }

                // 休憩終了時刻が勤務時間内かチェック
                if ($breakEnd && !($in <= $breakEnd && $breakEnd <= $out)) {
                    $validator->errors()->add(
                        "breaks.{$index}.end",
                        '休憩時間もしくは退勤時間が不適切な値です'
                    );
                }

                // 休憩開始 > 休憩終了 の場合
                if ($breakStart && $breakEnd && $breakStart >= $breakEnd) {
                    $validator->errors()->add(
                        "breaks.{$index}.end",
                        '休憩時間が不適切な値です'
                    );
                }
            }
        });
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        if ($this->expectsJson() || $this->wantsJson() || $this->isJson() || $this->ajax()) {
            $response = response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $validator->errors(),
            ], 422);
            throw new \Illuminate\Validation\ValidationException($validator, $response);
        }
        parent::failedValidation($validator);
    }
}