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
            'requested_check_in' => 'required|date_format:H:i',
            'requested_check_out' => 'required|date_format:H:i|after:requested_check_in',
            'breaks' => 'nullable|array',
            'breaks.*.requested_break_start' => [
                'nullable',
                'required_with:breaks.*.requested_break_end',
                'date_format:H:i',
                'after:requested_check_in',
                'before:requested_check_out',
            ],

            'breaks.*.requested_break_end' => [
                'nullable',
                'required_with:breaks.*.requested_break_start',
                'date_format:H:i',
                'after:breaks.*.requested_break_start',
                'before:requested_check_out',
            ],
            'reason' => 'required|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'requested_check_in.required' => '出勤時間を入力してください',
            'requested_check_in.date_format' => '出勤時間を正しい形式で入力してください',

            'requested_check_out.required' => '退勤時間を入力してください',
            'requested_check_out.date_format' => '退勤時間を正しい形式で入力してください',
            'requested_check_out.after' => '出勤時間もしくは退勤時間が不適切な値です',

            'breaks.*.requested_break_start.required_with' => '休憩開始時間を入力してください',
            'breaks.*.requested_break_start.date_format' => '休憩開始時間を正しい形式で入力してください',
            'breaks.*.requested_break_start.after' => '休憩時間が不適切な値です',
            'breaks.*.requested_break_start.before' => '休憩時間が不適切な値です',

            'breaks.*.requested_break_end.required_with' => '休憩終了時間を入力してください',
            'breaks.*.requested_break_end.date_format' => '休憩終了時間を正しい形式で入力してください',
            'breaks.*.requested_break_end.after' => '休憩時間が勤務時間外です',
            'breaks.*.requested_break_end.before' => '休憩時間もしくは退勤時間が不適切な値です',

            'reason.required' => '備考を記入してください',
            'reason.string' => '備考を文字列で入力してください',
            'reason.max' => '備考は255文字以内で入力してください',
        ];
    }
}
