# Quiz Bank Math 12 Plugin

Plugin WordPress để quản lý ngân hàng câu hỏi trắc nghiệm Toán 12 và tạo bài quiz.

## Mẫu câu hỏi đơn lẻ
- Bài học: Đạo hàm
- Câu hỏi: Giá trị của x nếu 2x + 3 = 7 là?
- A: 1
- B: 2
- C: 3
- D: 4
- Đáp án đúng: **B**

## Mẫu file JSON nhập hàng loạt
```json
[
  {
    "lesson": "Đạo hàm",
    "question": "Giá trị của x nếu 2x + 3 = 7 là?",
    "options": {
      "a": "1",
      "b": "2",
      "c": "3",
      "d": "4"
    },
    "correct": "b"
  },
  {
    "lesson": "Logarit",
    "question": "Giá trị của log_2 8 là?",
    "options": {
      "a": "2",
      "b": "3",
      "c": "4",
      "d": "8"
    },
    "correct": "b"
  }
]
```

Tải file JSON theo định dạng trên và nhập qua trang “Nhập từ JSON” của plugin.
