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
    'unique' => ':attribute đã được sử dụng.',
    'confirmed' => ':attribute xác nhận không khớp.',
    'attributes' => [
        'flashcard_ids' => 'Danh sách flashcard',
        'question_ids' => 'Danh sách câu hỏi',
        'flashcard' => [
            'original_word' => 'Từ gốc',
            'translated_word' => 'Bản dịch từ',
            'explanation' => 'Giải thích',
            'word_type_id' => 'Loại từ',
        ],
        'flashcard_collection' => [
            'collection_name' => 'Tên Bộ',
            'description' => 'Mô tả',
        ],
        'collection_test' => [
            'test_type_id' => 'Loại bài kiểm tra',
            'collection_id' => 'Bộ flashcard',
            'test_name' => 'Tên bài kiểm tra',
            'description' => 'Mô tả',
            'total_questions' => 'Tổng số câu hỏi',
            'status' => 'Trạng thái',
            'started_at' => 'Thời gian bắt đầu',
            'finished_at' => 'Thời gian kết thúc',
            'question_ids' => 'Danh sách câu hỏi',
        ],
        'user_test_attempt' => [
            'user_id' => 'Người dùng',
            'collection_test_id' => 'Bài kiểm tra',
            'status' => 'Trạng thái',
            'correct_count' => 'Số câu đúng',
            'total_score' => 'Tổng điểm',
            'started_time' => 'Thời gian bắt đầu',
            'finished_time' => 'Thời gian kết thúc',
            'total_time' => 'Tổng thời gian',
        ],
        'user_test_answer' => [
            'user_test_attempt_id' => 'Lượt làm bài',
            'question_id' => 'Câu hỏi',
            'user_answer' => 'Câu trả lời',
            'is_correct' => 'Đúng/Sai',
        ],
        'test_type' => [
            'test_type' => 'Loại bài kiểm tra',
        ],
        'question' => [
            'question_type_id' => 'Loại câu hỏi',
            'question_text' => 'Nội dung câu hỏi',
            'question_data' => 'Dữ liệu câu hỏi',
        ],
    ],
];
