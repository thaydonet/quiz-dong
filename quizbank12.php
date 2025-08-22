<?php
/*
Plugin Name: Quiz Bank Math 12
Description: Ngân hàng câu hỏi trắc nghiệm Toán 12 với nhập JSON và tạo bài quiz.
Version: 1.0
Author: ChatGPT
*/

// Tạo bảng khi kích hoạt plugin
register_activation_hook(__FILE__, 'qb_math12_activate');
function qb_math12_activate() {
    global $wpdb;
    $questions_table = $wpdb->prefix . 'quizbank_math12';
    $lessons_table   = $wpdb->prefix . 'quizbank_math12_lessons';
    $charset_collate = $wpdb->get_charset_collate();

    $sql_lessons = "CREATE TABLE $lessons_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(100) NOT NULL,
        type varchar(100) NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    $sql_questions = "CREATE TABLE $questions_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        lesson_id mediumint(9) NOT NULL,
        question text NOT NULL,
        option_a text NOT NULL,
        option_b text NOT NULL,
        option_c text NOT NULL,
        option_d text NOT NULL,
        correct varchar(1) NOT NULL,
        solution text,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql_lessons);
    dbDelta($sql_questions);
}

// Thêm menu quản trị
add_action('admin_menu', 'qb_math12_admin_menu');
function qb_math12_admin_menu() {
    add_menu_page('Quiz Bank Math 12', 'Quiz Bank Math 12', 'manage_options', 'qb-math12', 'qb_math12_questions_page');
    add_submenu_page('qb-math12', 'Bài học', 'Bài học', 'manage_options', 'qb-math12-lessons', 'qb_math12_lessons_page');
    add_submenu_page('qb-math12', 'Nhập từ JSON', 'Nhập từ JSON', 'manage_options', 'qb-math12-import', 'qb_math12_import_page');
    add_submenu_page('qb-math12', 'Tạo Quiz', 'Tạo Quiz', 'manage_options', 'qb-math12-quiz', 'qb_math12_quiz_page');
}

// Trang quản lý bài học
function qb_math12_lessons_page() {
    global $wpdb;
    $table = $wpdb->prefix . 'quizbank_math12_lessons';

    if (isset($_POST['qb_add_lesson'])) {
        $name = sanitize_text_field($_POST['name']);
        $type = sanitize_text_field($_POST['type']);
        $wpdb->insert($table, ['name' => $name, 'type' => $type]);
        echo '<div class="updated"><p>Đã thêm bài học.</p></div>';
    }

    $lessons = $wpdb->get_results("SELECT * FROM $table");

    echo '<div class="wrap"><h1>Bài học</h1>';
    echo '<form method="post">';
    echo '<p><label>Tên bài học: <input type="text" name="name" required></label></p>';
    echo '<p><label>Dạng: <input type="text" name="type" required></label></p>';
    echo '<p><input type="submit" name="qb_add_lesson" class="button button-primary" value="Thêm"></p>';
    echo '</form>';
    if ($lessons) {
        echo '<h2>Danh sách</h2><ul>';
        foreach ($lessons as $l) {
            echo '<li>' . esc_html($l->id . '. ' . $l->name . ' (' . $l->type . ')') . '</li>';
        }
        echo '</ul>';
    }
    echo '</div>';
}

// Trang thêm câu hỏi
function qb_math12_questions_page() {
    global $wpdb;
    $table         = $wpdb->prefix . 'quizbank_math12';
    $lessons_table = $wpdb->prefix . 'quizbank_math12_lessons';
    $lessons = $wpdb->get_results("SELECT * FROM $lessons_table");

    if (isset($_POST['qb_add_question'])) {
        $lesson_id = intval($_POST['lesson_id']);
        $question  = wp_kses_post($_POST['question']);
        $a = sanitize_text_field($_POST['option_a']);
        $b = sanitize_text_field($_POST['option_b']);
        $c = sanitize_text_field($_POST['option_c']);
        $d = sanitize_text_field($_POST['option_d']);
        $correct  = sanitize_text_field($_POST['correct']);
        $solution = wp_kses_post($_POST['solution']);

        $wpdb->insert($table, [
            'lesson_id' => $lesson_id,
            'question'  => $question,
            'option_a'  => $a,
            'option_b'  => $b,
            'option_c'  => $c,
            'option_d'  => $d,
            'correct'   => $correct,
            'solution'  => $solution,
        ]);
        echo '<div class="updated"><p>\u0110\u00e3 th\u00eam c\u00e2u h\u1ecfi.</p></div>';
    }

    echo '<div class="wrap"><h1>Thêm câu hỏi</h1>';
    if (!$lessons) {
        echo '<p>Vui lòng tạo bài học trước.</p></div>';
        return;
    }
    echo '<form method="post">';
    echo '<p><label>Bài học: <select name="lesson_id" required>';
    foreach ($lessons as $l) {
        echo '<option value="' . esc_attr($l->id) . '">' . esc_html($l->name . ' - ' . $l->type) . '</option>';
    }
    echo '</select></label></p>';
    echo '<p><label>Câu hỏi:<br><textarea name="question" rows="4" cols="50" required></textarea></label></p>';
    echo '<p><label>A: <input type="text" name="option_a" required></label></p>';
    echo '<p><label>B: <input type="text" name="option_b" required></label></p>';
    echo '<p><label>C: <input type="text" name="option_c" required></label></p>';
    echo '<p><label>D: <input type="text" name="option_d" required></label></p>';
    echo '<p><label>Đáp án đúng (a,b,c,d): <input type="text" name="correct" maxlength="1" required></label></p>';
    echo '<p><label>Lời giải:<br><textarea name="solution" rows="4" cols="50"></textarea></label></p>';
    echo '<p><input type="submit" name="qb_add_question" class="button button-primary" value="Thêm"></p>';
    echo '</form></div>';
}

// Trang nhập JSON
function qb_math12_import_page() {
    global $wpdb;
    $table = $wpdb->prefix . 'quizbank_math12';

    if (!empty($_FILES['json_file']['tmp_name'])) {
        $json = file_get_contents($_FILES['json_file']['tmp_name']);
        $data = json_decode($json, true);
        if ($data) {
            foreach ($data as $item) {
                $wpdb->insert($table, [
                    'lesson_id' => intval($item['lesson_id']),
                    'question'  => wp_kses_post($item['question']),
                    'option_a'  => sanitize_text_field($item['options']['a']),
                    'option_b'  => sanitize_text_field($item['options']['b']),
                    'option_c'  => sanitize_text_field($item['options']['c']),
                    'option_d'  => sanitize_text_field($item['options']['d']),
                    'correct'   => sanitize_text_field($item['correct']),
                    'solution'  => isset($item['solution']) ? wp_kses_post($item['solution']) : ''
                ]);
            }
            echo '<div class="updated"><p>\u0110\u00e3 nh\u1eadp ' . count($data) . ' c\u00e2u h\u1ecfi.</p></div>';
        } else {
            echo '<div class="error"><p>JSON không hợp lệ.</p></div>';
        }
    }

    echo '<div class="wrap"><h1>Nhập từ JSON</h1>';
    echo '<form method="post" enctype="multipart/form-data">';
    echo '<input type="file" name="json_file" accept="application/json" required> ';
    echo '<input type="submit" class="button button-primary" value="Nhập">';
    echo '</form></div>';
}

// Trang hỗ trợ shortcode
function qb_math12_quiz_page() {
    echo '<div class="wrap"><h1>Tạo Quiz</h1>';
    echo '<p>Sử dụng shortcode: <code>[math_quiz lesson="1" count="5"]</code> hoặc <code>[math_quiz type="Đạo hàm" count="5"]</code></p>';
    echo '</div>';
}

// Shortcode hiển thị quiz
add_shortcode('math_quiz', 'qb_math12_shortcode');
function qb_math12_shortcode($atts) {
    $atts = shortcode_atts([
        'lesson' => '',
        'type'   => '',
        'count'  => 10,
    ], $atts, 'math_quiz');

    global $wpdb;
    $questions_table = $wpdb->prefix . 'quizbank_math12';
    $lessons_table   = $wpdb->prefix . 'quizbank_math12_lessons';
    $count = intval($atts['count']);

    if ($atts['lesson']) {
        $questions = $wpdb->get_results($wpdb->prepare("SELECT * FROM $questions_table WHERE lesson_id = %d ORDER BY RAND() LIMIT %d", intval($atts['lesson']), $count));
    } elseif ($atts['type']) {
        $questions = $wpdb->get_results($wpdb->prepare("SELECT q.* FROM $questions_table q JOIN $lessons_table l ON q.lesson_id = l.id WHERE l.type = %s ORDER BY RAND() LIMIT %d", $atts['type'], $count));
    } else {
        $questions = $wpdb->get_results($wpdb->prepare("SELECT * FROM $questions_table ORDER BY RAND() LIMIT %d", $count));
    }

    if (!$questions) {
        return '<p>Chưa có câu hỏi.</p>';
    }

    $output = '<form class="qb-math12-quiz">';
    foreach ($questions as $index => $q) {
        $i = $index + 1;
        $output .= '<div class="qb-question" data-correct="' . esc_attr($q->correct) . '">';
        $output .= '<p>' . $i . '. ' . esc_html($q->question) . '</p>';
        $output .= '<label><input type="radio" name="q' . $q->id . '" value="a"> ' . esc_html($q->option_a) . '</label><br>';
        $output .= '<label><input type="radio" name="q' . $q->id . '" value="b"> ' . esc_html($q->option_b) . '</label><br>';
        $output .= '<label><input type="radio" name="q' . $q->id . '" value="c"> ' . esc_html($q->option_c) . '</label><br>';
        $output .= '<label><input type="radio" name="q' . $q->id . '" value="d"> ' . esc_html($q->option_d) . '</label><br>';
        $output .= '<div class="qb-solution" style="display:none;">Lời giải: ' . esc_html($q->solution) . '</div>';
        $output .= '</div>';
    }
    $output .= '<button type="submit">Nộp bài</button> <div class="qb-result"></div>';
    $output .= '</form>';

    $output .= '<script>
    document.addEventListener("DOMContentLoaded", function(){
        var form = document.querySelector(".qb-math12-quiz");
        if(!form) return;
        form.addEventListener("submit", function(e){
            e.preventDefault();
            var score = 0, total = 0;
            form.querySelectorAll(".qb-question").forEach(function(q){
                total++;
                var correct = q.getAttribute("data-correct");
                var chosen = q.querySelector("input[type=radio]:checked");
                if(chosen && chosen.value === correct){ score++; }
                var sol = q.querySelector(".qb-solution");
                if(sol) sol.style.display = "block";
            });
            form.querySelector(".qb-result").innerHTML = "Kết quả: " + score + "/" + total;
        });
    });
    </script>';

    return $output;
}
