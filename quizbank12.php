<?php
/**
 * Plugin Name: Quiz Bank Toán 12
 * Description: Ngân hàng trắc nghiệm Toán 12 (đổi số liệu), làm bài online + xuất đề TXT; Admin UI quản lý - tạo đề A/B/C. Shortcode front-end CHỈ hiển thị đề cho HS làm (không có panel xây đề).
 * Version: 1.3.3
 * Author: Do Le + GPT-5 Thinking
 * License: GPL2+
 * Text Domain: quizbank12
 */

if (!defined('ABSPATH')) exit;

class QB12_Plugin {
    const VER = '1.3.3';
    const HANDLE_JS = 'qb12-app-js';
    const HANDLE_CSS = 'qb12-app-css';
    const HANDLE_MATHJAX = 'qb12-mathjax';
    const HANDLE_ADMIN_CSS = 'qb12-admin-css';

    public function __construct() {
        add_action('init', [$this, 'register_types']);
        add_action('admin_menu', [$this, 'admin_menus']);
        add_action('add_meta_boxes', [$this, 'register_metaboxes']);
        add_action('save_post', [$this, 'save_post_meta'], 10, 2);
        add_action('admin_post_qb12_import_json', [$this, 'handle_import_json']);
        add_shortcode('quizbank12', [$this, 'shortcode']);
        add_action('wp_enqueue_scripts', [$this, 'register_front_assets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_filter('manage_qb12_question_posts_columns', [$this, 'q_columns']);
        add_action('manage_qb12_question_posts_custom_column', [$this, 'q_column_content'], 10, 2);
    }

    public function register_types(){
        register_post_type('qb12_question', [
            'labels' => [
                'name' => __('Quiz Bank', 'quizbank12'),
                'singular_name' => __('Question', 'quizbank12'),
                'add_new' => __('Thêm câu hỏi', 'quizbank12'),
                'add_new_item' => __('Thêm câu hỏi', 'quizbank12'),
                'edit_item' => __('Sửa câu hỏi', 'quizbank12'),
                'new_item' => __('Câu hỏi mới', 'quizbank12'),
                'view_item' => __('Xem câu hỏi', 'quizbank12'),
                'search_items' => __('Tìm câu hỏi', 'quizbank12'),
            ],
            'public' => false,
            'show_ui' => true,
            'menu_icon' => 'dashicons-welcome-learn-more',
            'supports' => ['title'],
            'has_archive' => false,
            'show_in_menu' => false,
        ]);
        register_taxonomy('qb12_lesson', ['qb12_question'], [
            'labels' => ['name' => 'Bài học (cate)'],
            'public' => false, 'show_ui' => true, 'hierarchical' => false, 'show_in_quick_edit' => true,
        ]);
        register_taxonomy('qb12_tag', ['qb12_question'], [
            'labels' => ['name' => 'Dạng (tag)'],
            'public' => false, 'show_ui' => true, 'hierarchical' => false, 'show_in_quick_edit' => true,
        ]);

        register_post_type('qb12_quiz', [
            'labels' => [
                'name' => __('Đề thi (Quiz)', 'quizbank12'),
                'singular_name' => __('Quiz', 'quizbank12'),
                'add_new' => __('Tạo đề', 'quizbank12'),
                'add_new_item' => __('Tạo đề mới', 'quizbank12'),
                'edit_item' => __('Sửa đề', 'quizbank12'),
                'new_item' => __('Đề mới', 'quizbank12'),
                'view_item' => __('Xem đề', 'quizbank12'),
                'search_items' => __('Tìm đề', 'quizbank12'),
            ],
            'public' => false,
            'show_ui' => true,
            'menu_icon' => 'dashicons-edit-page',
            'supports' => ['title'],
            'has_archive' => false,
            'show_in_menu' => false,
        ]);
    }

    public function admin_menus(){
        add_menu_page('Quiz Bank 12', 'Quiz Bank 12', 'edit_posts', 'qb12', [$this, 'render_main_page'], 'dashicons-welcome-learn-more', 26);
        add_submenu_page('qb12', 'Đề thi (Quiz)', 'Đề thi (Quiz)', 'edit_posts', 'edit.php?post_type=qb12_quiz');
        add_submenu_page('qb12', 'Quiz Bank (Ngân hàng)', 'Quiz Bank (Ngân hàng)', 'edit_posts', 'edit.php?post_type=qb12_question');
        add_submenu_page('qb12', 'Bài học (cate)', 'Bài học (cate)', 'manage_categories', 'edit-tags.php?taxonomy=qb12_lesson&post_type=qb12_question');
        add_submenu_page('qb12', 'Dạng (tag)', 'Dạng (tag)', 'manage_categories', 'edit-tags.php?taxonomy=qb12_tag&post_type=qb12_question');
        add_submenu_page('qb12', 'Thêm câu hỏi', 'Thêm câu hỏi', 'edit_posts', 'qb12-import', [$this, 'render_import_page']);
        add_submenu_page('qb12', 'Xây đề (demo)', 'Xây đề (demo)', 'edit_posts', 'qb12-builder', [$this, 'render_builder_page']);
    }

    public function render_main_page(){
        echo '<div class="wrap qb12-admin"><h1>Quiz Bank 12 – Hướng dẫn nhanh</h1>';
        echo '<ol>';
        echo '<li><strong>Bước 1:</strong> Vào <em>Quiz Bank (Ngân hàng)</em> để xem/sửa câu hỏi. Dùng <em>Bài học (cate)</em> và <em>Dạng (tag)</em> để phân loại.</li>';
        echo '<li><strong>Bước 2:</strong> Muốn thêm hàng loạt → vào <em>Thêm câu hỏi</em>, dán JSON: <code>{"questions":[...]}</code>. ';
        echo 'Tại đây có tuỳ chọn <em>Bỏ gán Bài học/Tag từ JSON</em> nếu bạn đã tạo sẵn danh mục.</li>';
        echo '<li><strong>Bước 3:</strong> Vào <em>Đề thi (Quiz)</em> → tạo đề → Chọn câu hỏi (tick) → Lưu. ';
        echo 'Ở khung bên phải có 3 shortcode mã đề A/B/C. Chèn vào Page/Post:</li>';
        echo '</ol>';
        echo '<pre>[quizbank12 quiz=123 variant=A autostart=1 mode=exam minutes=30]</pre>';
        echo '<p>Front-end (Page/Post) chỉ hiển thị đề cho HS làm, <strong>không</strong> có panel xây đề.</p>';
        echo '<p>Màn hình <em>Xây đề (demo)</em> dành cho GV đã chuyển vào <strong>wp-admin → Quiz Bank 12 → Xây đề (demo)</strong>.</p>';
        echo '</div>';
    }

    public function register_metaboxes(){
        add_meta_box('qb12_q_payload', 'Nội dung câu hỏi (JSON)', [$this, 'metabox_question_payload'], 'qb12_question', 'normal', 'high');
        add_meta_box('qb12_quiz_select', 'Chọn câu hỏi cho đề', [$this, 'metabox_quiz_select'], 'qb12_quiz', 'normal', 'high');
        add_meta_box('qb12_quiz_codes', 'Mã đề & Shortcode', [$this, 'metabox_quiz_codes'], 'qb12_quiz', 'side', 'high');
    }

    public function metabox_question_payload($post){
        $payload = get_post_meta($post->ID, '_qb12_payload', true);
        echo '<p>Dán 1 object JSON theo schema "static" hoặc "dynamic" (giống import hàng loạt). Plugin sẽ dùng để sinh đề.</p>';
        echo '<textarea style="width:100%;height:260px;font-family:ui-monospace" name="qb12_payload">'.esc_textarea($payload).'</textarea>';
        echo '<p class="description">Gợi ý đặt <strong>lesson</strong>, <strong>topic</strong>, <strong>format</strong>, <strong>type</strong>, ...</p>';
        wp_nonce_field('qb12_save_question', 'qb12_nonce');
    }

    public function metabox_quiz_select($post){
        $selected = (array) get_post_meta($post->ID, '_qb12_questions', true);
        $search = isset($_GET['qb12_s']) ? sanitize_text_field($_GET['qb12_s']) : '';
        $args = ['post_type'=>'qb12_question','posts_per_page'=>50,'s'=>$search,'orderby'=>'date','order'=>'DESC'];
        $q = new WP_Query($args);
        echo '<form method="get" style="margin-bottom:8px">';
        echo '<input type="hidden" name="post" value="'.intval($post->ID).'"/><input type="hidden" name="action" value="edit"/>';
        echo '<input type="text" name="qb12_s" value="'.esc_attr($search).'" placeholder="Tìm theo tiêu đề..." /> ';
        echo '<button class="button">Tìm</button>';
        echo '</form>';
        echo '<div class="qb12-listbox">';
        if ($q->have_posts()){
            while ($q->have_posts()){ $q->the_post();
                $id = get_the_ID();
                $title = get_the_title();
                echo '<label class="qb12-row"><input type="checkbox" name="qb12_questions[]" value="'.$id.'" '.(in_array($id,$selected)?'checked':'').'> '.esc_html($title).'</label>';
            }
            wp_reset_postdata();
        } else {
            echo '<p>Không có câu hỏi.</p>';
        }
        echo '</div>';
        echo '<p class="description">Chọn nhiều câu; lưu bài viết để cập nhật.</p>';
        wp_nonce_field('qb12_save_quiz', 'qb12_quiz_nonce');
    }

    public function metabox_quiz_codes($post){
        $seeds = get_post_meta($post->ID, '_qb12_variant_seeds', true);
        if (!is_array($seeds) || empty($seeds)){
            $seeds = ['A'=>rand(1, PHP_INT_MAX), 'B'=>rand(1, PHP_INT_MAX), 'C'=>rand(1, PHP_INT_MAX)];
            update_post_meta($post->ID, '_qb12_variant_seeds', $seeds);
        }
        $shortA = '[quizbank12 quiz='.$post->ID.' variant=A]';
        $shortB = '[quizbank12 quiz='.$post->ID.' variant=B]';
        $shortC = '[quizbank12 quiz='.$post->ID.' variant=C]';
        echo '<p><strong>Mã đề A</strong><br/><code>'.$shortA.'</code></p>';
        echo '<p><strong>Mã đề B</strong><br/><code>'.$shortB.'</code></p>';
        echo '<p><strong>Mã đề C</strong><br/><code>'.$shortC.'</code></p>';
        echo '<p><button class="button" name="qb12_regen_codes" value="1">Tạo lại seed A/B/C</button></p>';
        echo '<p class="description">Chèn shortcode vào Page/Post để học sinh làm bài.</p>';
    }

    public function save_post_meta($post_id, $post){
        if ($post->post_type === 'qb12_question'){
            if (!isset($_POST['qb12_nonce']) || !wp_verify_nonce($_POST['qb12_nonce'], 'qb12_save_question')) return;
            if (isset($_POST['qb12_payload'])){
                update_post_meta($post_id, '_qb12_payload', wp_kses_post($_POST['qb12_payload']));
            }
        }
        if ($post->post_type === 'qb12_quiz'){
            if (isset($_POST['qb12_quiz_nonce']) && wp_verify_nonce($_POST['qb12_quiz_nonce'], 'qb12_save_quiz')){
                $ids = isset($_POST['qb12_questions']) ? array_map('intval', (array)$_POST['qb12_questions']) : [];
                update_post_meta($post_id, '_qb12_questions', $ids);
            }
            if (isset($_POST['qb12_regen_codes'])){
                $seeds = ['A'=>rand(1, PHP_INT_MAX), 'B'=>rand(1, PHP_INT_MAX), 'C'=>rand(1, PHP_INT_MAX)];
                update_post_meta($post_id, '_qb12_variant_seeds', $seeds);
            }
        }
    }

    public function render_import_page(){
        if (!current_user_can('edit_posts')) return;
        echo '<div class="wrap qb12-admin"><h1>Nhập câu hỏi từ JSON</h1>';
        echo '<form method="post" action="'.admin_url('admin-post.php').'">';
        echo '<input type="hidden" name="action" value="qb12_import_json" />';
        wp_nonce_field('qb12_import_json');
        echo '<p><label><input type="checkbox" name="qb12_skip_terms" value="1" /> Bỏ gán <strong>Bài học/Dạng</strong> từ JSON (sử dụng danh mục có sẵn)</label></p>';
        echo '<p><textarea name="qb12_json" style="width:100%;height:300px;font-family:ui-monospace" placeholder=\'{ "questions":[ ... ] }\'></textarea></p>';
        echo '<p><button class="button button-primary">Import</button></p>';
        echo '</form></div>';
    }

    public function render_builder_page(){
        if (!current_user_can('edit_posts')) return;
        $this->register_front_assets();
        wp_enqueue_style(self::HANDLE_CSS);
        wp_enqueue_script(self::HANDLE_JS);
        $bootstrap = [
            'questions' => ['questions' => []],
            'seed' => null,
            '__REV' => 'builder',
            '__CFG' => [ 'admin_builder' => true ]
        ];
        wp_add_inline_script(self::HANDLE_JS, 'window.QB12_BOOTSTRAP = '.wp_json_encode($bootstrap, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).';', 'before');
        echo '<div class="wrap"><h1>Xây đề (demo)</h1><p class="description">Chỉ dành cho giáo viên. Có thể import JSON, chọn mẫu và xem trước đề.</p><div id="qb12-root"></div></div>';
    }

    public function register_front_assets() {
        wp_register_style(self::HANDLE_CSS, plugins_url('assets/css/app.css', __FILE__), [], self::VER);
        $mj_cfg = "window.MathJax = { tex:{ inlineMath:[['\\\\(','\\\\)']], displayMath:[['\\\\[','\\\\]']] }, svg:{ fontCache:'global' } };";
        wp_register_script(self::HANDLE_MATHJAX, 'https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-svg.js', [], null, true);
        wp_add_inline_script(self::HANDLE_MATHJAX, $mj_cfg, 'before');
        wp_register_script(self::HANDLE_JS, plugins_url('assets/js/app.js', __FILE__), [self::HANDLE_MATHJAX], self::VER, true);
    }

    public function enqueue_admin_assets($hook){
        if (strpos($hook, 'qb12') !== false){
            wp_enqueue_style(self::HANDLE_ADMIN_CSS, plugins_url('assets/css/app.css', __FILE__), [], self::VER);
            $css = '.qb12-listbox{max-height:340px; overflow:auto; border:1px solid #ccd; padding:8px; background:#fff} .qb12-row{display:block; padding:6px 4px; border-bottom:1px dashed #dde} .qb12-admin textarea{background:#111827; color:#eaf2ff}';
            wp_add_inline_style(self::HANDLE_ADMIN_CSS, $css);
        }
    }

    public function q_columns($cols){
        $new = [];
        foreach($cols as $k=>$v){
            $new[$k] = $v;
            if ($k==='title'){
                $new['qb12_lesson'] = 'Bài học';
                $new['qb12_tag'] = 'Dạng';
            }
        }
        return $new;
    }
    public function q_column_content($column, $post_id){
        if ($column==='qb12_lesson'){
            $terms = wp_get_post_terms($post_id, 'qb12_lesson', ['fields'=>'names']);
            echo esc_html(implode(', ', $terms));
        }
        if ($column==='qb12_tag'){
            $terms = wp_get_post_terms($post_id, 'qb12_tag', ['fields'=>'names']);
            echo esc_html(implode(', ', $terms));
        }
    }

    public function shortcode($atts) {
        $atts = shortcode_atts([
            'quiz' => 0,
            'variant' => '',
            'v' => '',
            'autostart' => '1',
            'mode' => '',
            'minutes' => '',
        ], $atts, 'quizbank12');

        wp_enqueue_style(self::HANDLE_CSS);
        wp_enqueue_script(self::HANDLE_JS);

        $bootstrap = [
            'questions' => null,
            'seed' => null,
            '__REV' => sanitize_text_field($atts['v']),
            '__CFG' => [
                'autostart' => $atts['autostart'] !== '0',
                'mode' => in_array($atts['mode'], ['practice','exam']) ? $atts['mode'] : '',
                'minutes' => is_numeric($atts['minutes']) ? intval($atts['minutes']) : '',
                'admin_builder' => false,
            ],
        ];

        $quiz_id = intval($atts['quiz']);
        if ($quiz_id){
            $ids = (array) get_post_meta($quiz_id, '_qb12_questions', true);
            $payloads = [];
            foreach ($ids as $qid){
                $json = get_post_meta(intval($qid), '_qb12_payload', true);
                if ($json){
                    $item = json_decode($json, true);
                    if ($item) $payloads[] = $item;
                }
            }
            $bootstrap['questions'] = ['questions' => $payloads];
            $seeds = get_post_meta($quiz_id, '_qb12_variant_seeds', true);
            if (is_array($seeds) && !empty($atts['variant']) && isset($seeds[$atts['variant']])){
                $bootstrap['seed'] = intval($seeds[$atts['variant']]);
            }
        }

        wp_add_inline_script(self::HANDLE_JS, 'window.QB12_BOOTSTRAP = '.wp_json_encode($bootstrap, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).';', 'before');

        ob_start(); ?>
        <div id="qb12-root"></div>
        <?php
        return ob_get_clean();
    }
}

new QB12_Plugin();
