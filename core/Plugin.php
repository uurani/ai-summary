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
        
        // 注册更新相关钩子
        static::initUpdateHooks();
    }
    
    static function initUpdateHooks()
    {
        // 注册激活钩子
        register_activation_hook(Config::$plugin_dir . '/index.php', [static::class, 'scheduleUpdateCheck']);
        
        // 注册停用钩子
        register_deactivation_hook(Config::$plugin_dir . '/index.php', [static::class, 'clearUpdateSchedule']);
        
        // 注册定时检查更新的动作
        add_action('ai_summary_check_update_cron', [static::class, 'autoCheckUpdate']);
        
        // 在插件加载时检查是否需要安排定时任务
        add_action('init', [static::class, 'maybeScheduleUpdateCheck']);
        
        // 管理员通知
        add_action('admin_notices', [static::class, 'showUpdateNotice']);
    }
    
    static function scheduleUpdateCheck()
    {
        if (!wp_next_scheduled('ai_summary_check_update_cron')) {
            wp_schedule_event(time(), 'twicedaily', 'ai_summary_check_update_cron');
        }
    }
    
    static function clearUpdateSchedule()
    {
        wp_clear_scheduled_hook('ai_summary_check_update_cron');
    }
    
    static function maybeScheduleUpdateCheck()
    {
        global $ai_summary_set;
        $ai_summary_set = Options::getOptions();
        
        // 只有在启用自动检查时才安排定时任务
        if ($ai_summary_set['auto_check_update']) {
            if (!wp_next_scheduled('ai_summary_check_update_cron')) {
                wp_schedule_event(time(), 'twicedaily', 'ai_summary_check_update_cron');
            }
        } else {
            // 如果关闭了自动检查，清除定时任务
            wp_clear_scheduled_hook('ai_summary_check_update_cron');
        }
    }
    
    static function autoCheckUpdate()
    {
        global $ai_summary_set;
        $ai_summary_set = Options::getOptions();
        
        // 检查是否启用自动检查
        if (!$ai_summary_set['auto_check_update']) {
            return;
        }
        
        $time = time();
        $last_time = $ai_summary_set['last_check_time'];
        
        // 避免频繁检查（至少间隔1小时）
        if ($time - $last_time < 3600) {
            return;
        }
        
        $plugin_info = static::getServePluginInfo();
        $ai_summary_set['last_check_time'] = $time;
        
        if ($plugin_info !== false) {
            if (version_compare($plugin_info['version_name'], Config::$plugin_version_name, '>')) {
                $ai_summary_set['need_update'] = true;
            } else {
                $ai_summary_set['need_update'] = false;
            }
        }
        
        Options::saveSet($ai_summary_set);
    }
    
    static function showUpdateNotice()
    {
        // 只在插件管理页面显示通知
        $screen = get_current_screen();
        if (!$screen || strpos($screen->id, 'ai_summary') === false) {
            return;
        }
        
        global $ai_summary_set;
        $ai_summary_set = Options::getOptions();
        
        if (isset($ai_summary_set['need_update']) && $ai_summary_set['need_update']) {
            echo '<div class="notice notice-info is-dismissible">';
            echo '<p><strong>AI Summary</strong> 插件有新版本可用！';
            echo '<a href="' . admin_url('admin.php?page=ai_summary_set#about-tab') . '" style="margin-left: 10px;">查看更新</a>';
            echo '</p>';
            echo '</div>';
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
        }, 'dashicons-icon-ai-summary');
    }

    static function getServePluginInfo()
    {
        $url = Config::$plugin_server_url;
        $response = wp_remote_get($url, ['timeout' => 10]);
        if (is_wp_error($response)) {
            return false;
        }
        $body = wp_remote_retrieve_body($response);
        $json = json_decode($body, true);
        if (
            ! $json
            || ! isset($json['code'], $json['data'])
            || $json['code'] !== 200
        ) {
            return false;
        }
        $d = $json['data'];
        return [
            'version'      => isset($d['version']) ? floatval($d['version']) : 0,
            'version_name' => $d['version'],
            'down_url'     => $d['package_url'],
        ];
    }


    static function getSeoInfo($post_id)
    {
        $meta_box = Options::getPostMeta($post_id);
        $data['post_description'] = $meta_box['post_description'];
        $data['post_keywords'] = $meta_box['post_keywords'];
        return $data;
    }
}