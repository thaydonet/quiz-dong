# Quiz Bank Math 12 Plugin

Plugin WordPress để quản lý ngân hàng câu hỏi trắc nghiệm Toán 12 và tạo bài quiz.

## Mẫu câu hỏi đơn lẻ
- Bài học (ID 1): Đạo hàm, dạng: Cơ bản
- Câu hỏi: Giá trị của x nếu 2x + 3 = 7 là?
- A: 1
- B: 2
- C: 3
- D: 4
- Đáp án đúng: **B**
- Lời giải: 2x + 3 = 7 ⇒ x = 2

## Mẫu file JSON nhập hàng loạt
```json
[
  {
    "lesson_id": 1,
    "question": "Giá trị của x nếu 2x + 3 = 7 là?",
    "options": {
      "a": "1",
      "b": "2",
      "c": "3",
      "d": "4"
    },
    "correct": "b",
    "solution": "2x + 3 = 7 ⇒ x = 2"
  },
  {
    "lesson_id": 2,
    "question": "Giá trị của log_2 8 là?",
    "options": {
      "a": "2",
      "b": "3",
      "c": "4",
      "d": "8"
    },
    "correct": "b",
    "solution": "8 = 2^3 nên log_2 8 = 3"
  }
]
```

Tạo trước các bài học cùng dạng trong trang **Bài học**, sau đó tải file JSON theo định dạng trên và nhập qua trang “Nhập từ JSON” của plugin.
