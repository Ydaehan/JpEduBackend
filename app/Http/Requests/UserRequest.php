<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // 이 값은 현재 유저가 저장이 가능한 지 검사하는 역할
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nickname' => [
                'required',
                'string',
                // 한글 영문 모두 합쳐서 10글자 제한
                function ($attribute, $value, $fail) {
                    if (mb_strlen($value) > 10) {
                        $fail($attribute . ' is too long.');
                    }
                },
                'unique:users',
            ],
            'email' => 'required|email|max:50|unique:users',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-zA-Z])(?=.*\d)(?=.*[\d\X])(?=.*[^\w\d\s:])([^\s]){10,}$/',
            ],
            'phone' => 'required|numeric|digits_between:11,15',
            'birthday' => 'required|date',
        ];
    }

    public function messages()
    {
        return [
            'nickname.required' => '닉네임을 입력해주세요.',
            'nickname.string' => '닉네임은 문자열로 입력해주세요.',
            'nickname.max' => '닉네임은 10자 이내로 입력해주세요.',
            'nickname.unique' => '이미 존재하는 닉네임입니다.',
            'email.required' => '이메일을 입력해주세요.',
            'email.email' => '이메일 형식에 맞게 입력해주세요.',
            'email.unique' => '이미 존재하는 이메일입니다.',
            'password.required' => '비밀번호를 입력해주세요.',
            'password.string' => '비밀번호는 문자열로 입력해주세요.',
            'password.min' => '비밀번호는 10자 이상으로 입력해주세요.',
            'password.confirmed' => '비밀번호 확인이 일치하지 않습니다.',
            'phone.required' => '전화번호를 입력해주세요.',
            'phone.numeric' => '전화번호는 숫자로 입력해주세요.',
            'phone.digits_between' => '전화번호는 11자 이상 15자 이하로 입력해주세요.',
            'birthday.required' => '생일을 입력해주세요.',
            'birthday.date' => '생일은 날짜 형식으로 입력해주세요.',
        ];
    }
}
