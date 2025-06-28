<?php

namespace ai_summary;

class Plugin
{
    static function init()
    {
        global $ai_summary_set;
        add_action('admin_menu', [static::class, 'regMenu']);
        if ($ai_summary_set['open_ai_summary']) {
            add_action('add_meta_boxes', function () {
                add_meta_box('ai_summary', 'AI Summary设置', [static::class, 'addMetaBox'], 'post', 'advanced', 'high');
            });
            add_action('save_post', [static::class, 'savePostMeta']);
            add_filter('the_content', [static::class, 'addAISummaryTool']);
        }
    }


    static function addAISummaryTool($content)
    {
        $content = '<div id="ai-summary-tool"></div>' . $content;
        return $content;
    }

    static function savePostMeta($post_id)
    {
        // 临时调试日志
        error_log('AI Summary savePostMeta called for post_id: ' . $post_id);
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            error_log('AI Summary: DOING_AUTOSAVE, skipping');
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            error_log('AI Summary: User cannot edit post, skipping');
            return;
        }
        
        error_log('AI Summary: POST data available keys: ' . implode(', ', array_keys($_POST)));
        
        if (isset($_POST['ai-summary-meta-box'])) {
            $value = $_POST['ai-summary-meta-box'];
            error_log('AI Summary: Found ai-summary-meta-box data: ' . substr($value, 0, 100) . '...');
            $result = update_post_meta($post_id, 'ai_summary_post_meta', $value);
            error_log('AI Summary: update_post_meta result: ' . ($result ? 'success' : 'failed'));
        } else {
            error_log('AI Summary: ai-summary-meta-box not found in POST data');
        }
    }

    static function addMetaBox()
    {
        $post_id = get_the_ID();
        $post_meta = Options::getPostMeta($post_id);
        $data['set'] = $post_meta;
        WordPress::echoJson('ai_summary_post_meta', $data);
        ?>
        <div id="ai-summary-meta-box"></div>
        <?php
    }

    static function regMenu()
    {
        add_menu_page('AI Summary', 'AI Summary', 'administrator', 'ai_summary_set', function () {
            require_once Config::$plugin_dir . '/pages/admin-set.php';
        }, 'dashicons-controls-volumeon');
    }




    static function getSeoInfo($post_id)
    {
        $meta_box = Options::getPostMeta($post_id);
        $data['post_description'] = $meta_box['post_description'];
        $data['post_keywords'] = $meta_box['post_keywords'];
        return $data;
    }
}