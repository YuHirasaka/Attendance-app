<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminAttendanceRequest extends FormRequest
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
            'attendance_id' => ['nullable', 'exists:attendances,id'],
            'user_id' => ['required', 'exists:users,id'],
            'work_date' => ['required', 'date'],
            'check_in' => 'required|date_format:H:i',
            'check_out' => 'required|date_format:H:i|after:check_in',
            'breaks' => 'nullable|array',
            'breaks.*.break_start' => [
                'nullable',
                'required_with:breaks.*.break_end',
                'date_format:H:i',
                'after:check_in',
                'before:check_out',
            ],

            'breaks.*.break_end' => [
                'nullable',
                'required_with:breaks.*.break_start',
                'date_format:H:i',
                'after:breaks.*.break_start',
                'before:check_out',
            ],
            'note' => 'required|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'check_in.required' => '出勤時間を入力してください',
            'check_in.date_format' => '出勤時間を正しい形式で入力してください',

            'check_out.required' => '退勤時間を入力してください',
            'check_out.date_format' => '退勤時間を正しい形式で入力してください',
            'check_out.after' => '出勤時間もしくは退勤時間が不適切な値です',

            'breaks.*.break_start.required_with' => '休憩開始時間を入力してください',
            'breaks.*.break_start.date_format' => '休憩開始時間を正しい形式で入力してください',
            'breaks.*.break_start.after' => '休憩時間が不適切な値です',
            'breaks.*.break_start.before' => '休憩時間が不適切な値です',

            'breaks.*.break_end.required_with' => '休憩終了時間を入力してください',
            'breaks.*.break_end.date_format' => '休憩終了時間を正しい形式で入力してください',
            'breaks.*.break_end.after' => '休憩時間が勤務時間外です',
            'breaks.*.break_end.before' => '休憩時間もしくは退勤時間が不適切な値です',

            'note.required' => '備考を記入してください',
            'note.string' => '備考を文字列で入力してください',
            'note.max' => '備考は255文字以内で入力してください',
        ];
    }
}
