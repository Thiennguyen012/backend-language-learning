<?php

return [
    'invalid' => 'Dữ liệu không hợp lệ.',
    'required' => ':attribute là bắt buộc.',
    'string' => ':attribute phải là chuỗi ký tự.',
    'email' => ':attribute không đúng định dạng email.',
    'numeric' => ':attribute phải là số.',
    'integer' => ':attribute phải là số nguyên.',
    'boolean' => ':attribute phải là true hoặc false.',
    'date' => ':attribute không đúng định dạng ngày.',
    'array' => ':attribute phải là mảng.',
    'in' => ':attribute phải nằm trong các giá trị: :values.',
    'exists' => ':attribute không hợp lệ.',
    'min' => [
        'numeric' => ':attribute phải lớn hơn hoặc bằng :min.',
        'string' => ':attribute phải ít nhất :min ký tự.',
    ],
    'max' => [
        'numeric' => ':attribute không được lớn hơn :max.',
        'string' => ':attribute không được vượt quá :max ky tu.',
    ],
    'attributes' => [
        'flashcard_ids' => 'Danh sách flashcard',
        'flashcard' => [
            'original_word' => 'Từ gốc',
            'translated_word' => 'Bản dịch từ',
            'word_type_id' => 'Loại từ',
        ],
        'flashcard_collection' => [
            'collection_name' => 'Ten Bộ',
            'description' => 'Mô tả',
        ],
        'test_type' => [
            'test_type' => 'Loại bài test',
        ],
        'question' => [
            'question_type_id' => 'Loại câu hỏi',
            'question_text' => 'Nội dung câu hỏi',
            'question_data' => 'Dữ liệu câu hỏi',
        ],
    ],
];
