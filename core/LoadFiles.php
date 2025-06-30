<?php

namespace ai_summary;

class LoadFiles
{
    static function init()
    {
        $core_dir = Config::$plugin_dir . '/core';
        require_once "$core_dir/Plugin.php";
        require_once "$core_dir/Options.php";
        require_once "$core_dir/WordPress.php";
        require_once "$core_dir/Ajax.php";
        require_once "$core_dir/Tools.php";
        require_once "$core_dir/app/WenXin.php";
        require_once "$core_dir/app/ChatGPT.php";
        require_once "$core_dir/app/Gemini.php";
        require_once "$core_dir/app/Doubao.php";
        require_once "$core_dir/app/Tongyi.php";
        require_once "$core_dir/app/AI.php";

        global $ai_summary_set;
        $ai_summary_set = Options::getOptions();
        Ajax::init();
        Plugin::init();
        add_action('admin_enqueue_scripts', [static::class, '_loadFileOnAdmin']);
        add_action('wp_enqueue_scripts', array(static::class, '_LoadFileOnSite'));
        add_action('admin_footer', array(static::class, '_echoOnAdmin'));
    }

     static function _echoOnAdmin()
        {
            ?>
            <script>
                jQuery.post(core_ai_power.ajax_url, {
                    action: core_ai_power.ajax_name,
                    fun: 'checkUpdate'
                })
            </script>
            <?php
        }

    static function _LoadFileOnSite()
    {
        if (is_single()) {
            global $ai_summary_set;
            if ($ai_summary_set['open_ai_summary']) {
                $post_id = get_the_ID();
                $meta_box = Options::getPostMeta($post_id);
                if ($meta_box['summary'] != '') {
                    $data['summary'] = $meta_box['summary'];
                    $data['img_url'] = Config::$img_url;
                    $data['ai_summary_animation'] = $ai_summary_set['ai_summary_animation'];
                    $data['ai_summary_see_other_btn'] = $ai_summary_set['ai_summary_see_other_btn'];
                    $data['ai_summary_feedback_url'] = $ai_summary_set['ai_summary_feedback_url'];
                    if ($data['ai_summary_see_other_btn']) {
                        $random_post = get_posts(array(
                            'numberposts' => 1,
                            'orderby' => 'rand'
                        ));
                        if ($random_post) {
                            $data['other_link'] = get_permalink($random_post[0]->ID);
                        } else {
                            $data['other_link'] = get_home_url();
                        }
                    }
                    WordPress::echoJson('ai_summary', $data);
                    WordPress::loadCss('ai-summary-frontend', 'summary.css');
                    WordPress::loadJS('ai-summary-frontend', 'summary-frontend.js', true, [], true);
                }
            }
        }
    }

    static function _loadFileOnAdmin($hook)
    {
        WordPress::loadCss('ai-summary-admin', 'admin.css');
        wp_localize_script('jquery', 'ai_summary', Options::getAdminSet());

        if ($hook == 'toplevel_page_ai_summary_set') {
            WordPress::loadJS('ai-summary-admin', 'admin-set.js', true, ['jquery'], true);
        }
        
        // 文章编辑页面加载AI助手
        if (in_array($hook, ['post.php', 'post-new.php'])) {
            WordPress::loadCss('ai-summary-post-editor', 'post-editor.css');
            WordPress::loadJS('ai-summary-post-editor', 'post-editor.js', true, ['jquery'], true);
        }
    }
}