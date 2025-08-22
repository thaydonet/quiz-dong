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
    $table_name = $wpdb->prefix . 'quizbank_math12';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        lesson varchar(100) NOT NULL,
        question text NOT NULL,
        option_a text NOT NULL,
        option_b text NOT NULL,
        option_c text NOT NULL,
        option_d text NOT NULL,
        correct varchar(1) NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Thêm menu quản trị
add_action('admin_menu', 'qb_math12_admin_menu');
function qb_math12_admin_menu() {
    add_menu_page('Quiz Bank Math 12', 'Quiz Bank Math 12', 'manage_options', 'qb-math12', 'qb_math12_questions_page');
    add_submenu_page('qb-math12', 'Nhập từ JSON', 'Nhập từ JSON', 'manage_options', 'qb-math12-import', 'qb_math12_import_page');
    add_submenu_page('qb-math12', 'Tạo Quiz', 'Tạo Quiz', 'manage_options', 'qb-math12-quiz', 'qb_math12_quiz_page');
}

// Trang thêm câu hỏi
function qb_math12_questions_page() {
    global $wpdb;
    $table = $wpdb->prefix . 'quizbank_math12';

    if (isset($_POST['qb_add_question'])) {
        $lesson = sanitize_text_field($_POST['lesson']);
        $question = wp_kses_post($_POST['question']);
        $a = sanitize_text_field($_POST['option_a']);
        $b = sanitize_text_field($_POST['option_b']);
        $c = sanitize_text_field($_POST['option_c']);
        $d = sanitize_text_field($_POST['option_d']);
        $correct = sanitize_text_field($_POST['correct']);

        $wpdb->insert($table, [
            'lesson' => $lesson,
            'question' => $question,
            'option_a' => $a,
            'option_b' => $b,
            'option_c' => $c,
            'option_d' => $d,
            'correct'  => $correct,
        ]);
        echo '<div class="updated"><p>\u0110\u00e3 th\u00eam c\u00e2u h\u1ecfi.</p></div>';
    }

    echo '<div class="wrap"><h1>Thêm câu hỏi</h1>';
    echo '<form method="post">';
    echo '<p><label>Bài học: <input type="text" name="lesson" required></label></p>';
    echo '<p><label>Câu hỏi:<br><textarea name="question" rows="4" cols="50" required></textarea></label></p>';
    echo '<p><label>A: <input type="text" name="option_a" required></label></p>';
    echo '<p><label>B: <input type="text" name="option_b" required></label></p>';
    echo '<p><label>C: <input type="text" name="option_c" required></label></p>';
    echo '<p><label>D: <input type="text" name="option_d" required></label></p>';
    echo '<p><label>Đáp án đúng (a,b,c,d): <input type="text" name="correct" maxlength="1" required></label></p>';
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
                    'lesson'   => sanitize_text_field($item['lesson']),
                    'question' => wp_kses_post($item['question']),
                    'option_a' => sanitize_text_field($item['options']['a']),
                    'option_b' => sanitize_text_field($item['options']['b']),
                    'option_c' => sanitize_text_field($item['options']['c']),
                    'option_d' => sanitize_text_field($item['options']['d']),
                    'correct'  => sanitize_text_field($item['correct'])
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
    echo '<p>Sử dụng shortcode: <code>[math_quiz lesson="1" count="5"]</code></p>';
    echo '</div>';
}

// Shortcode hiển thị quiz
add_shortcode('math_quiz', 'qb_math12_shortcode');
function qb_math12_shortcode($atts) {
    $atts = shortcode_atts([
        'lesson' => '',
        'count'  => 10,
    ], $atts, 'math_quiz');

    global $wpdb;
    $table = $wpdb->prefix . 'quizbank_math12';
    $count = intval($atts['count']);

    if ($atts['lesson']) {
        $questions = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE lesson = %s ORDER BY RAND() LIMIT %d", $atts['lesson'], $count));
    } else {
        $questions = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table ORDER BY RAND() LIMIT %d", $count));
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
            });
            form.querySelector(".qb-result").innerHTML = "Kết quả: " + score + "/" + total;
        });
    });
    </script>';

    return $output;
}
