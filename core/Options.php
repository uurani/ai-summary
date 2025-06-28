<?php

namespace ai_summary;


class Options
{
    static function getAdminSet()
    {
        global $ai_summary_set;
        $data['ajax_url'] = admin_url('admin-ajax.php');
        $data['ajax_name'] = Config::$plugin_name;
        $data['img_url'] = Config::$img_url;
        $data['set'] = $ai_summary_set;
        $data['version_name'] = Config::$plugin_version_name;
        $data['version'] = Config::$plugin_version;
        $data['ai_structure'] = self::getAiTypeStructure();
        return $data;
    }

    static function getAiTypeStructure()
    {
        $data[] = [
            'type' => 'wenxin',
            'name' => '文心一言',
            'icon' => 'wenxin.svg',
            'gpt' => WenXin::getModels()
        ];
        
        $data[] = [
            'type' => 'chatgpt',
            'name' => 'ChatGPT',
            'icon' => 'openai.svg',
            'gpt' => ChatGPT::getModels()
        ];
        
        $data[] = [
            'type' => 'gemini',
            'name' => 'Gemini',
            'icon' => 'gemini.svg',
            'gpt' => Gemini::getModels()
        ];
        
        return $data;
    }

    static function getDefaultOptions()
    {
        
        // 文心一言配置
        $data['wenxin_api_key'] = '';
        $data['wenxin_model'] = 'ernie-3.5-8k';
        
        // ChatGPT配置
        $data['chatgpt_api_key'] = '';
        $data['chatgpt_model'] = 'gpt-3.5-turbo';
        $data['chatgpt_base_url'] = 'https://api.openai.com';
        
        // Gemini配置
        $data['gemini_api_key'] = '';
        $data['gemini_model'] = 'gemini-2.0-flash';
        
        // AI摘要配置
        $data['open_ai_summary'] = true;
        $data['ai_summary_path'] = 'wenxin';
        $data['ai_summary_word_number'] = 200;
        $data['ai_summary_animation'] = true;
        $data['ai_summary_see_other_btn'] = true;
        $data['ai_summary_feedback_url'] = '';
        
        // AI SEO配置
        $data['ai_seo_open'] = false;
        $data['ai_seo_path'] = 'wenxin';
        $data['ai_seo_description_length'] = 150;
        $data['ai_seo_keywords_length'] = 10;
        
        return $data;
    }

    static function getPostMeta($post_id)
    {
        $data = get_post_meta($post_id, 'ai_summary_post_meta', true);
        $default_data = self::getPostMetaDefault();
        if (empty($data)) {
            return $default_data;
        }
        $json = json_decode(base64_decode($data), true);
        if ($json) {
            return self::updateOptions($json, $default_data);
        }
        return $default_data;
    }

    static function getPostMetaDefault()
    {
        $data['summary'] = '';
        $data['last_update'] = '';
        $data['post_description'] = '';
        $data['post_keywords'] = '';
        return $data;
    }

    static function getOptions()
    {
        $default = self::getDefaultOptions();
        $set = get_option(Config::$set_name, false);
        if ($set === false) {
            return $default;
        }
        if (!is_string($set)) {
            return $default;
        }
        $set_obj = json_decode(base64_decode($set), true);
        if ($set_obj === false) {
            return $default;
        } else {
            return self::updateOptions($set_obj, $default);
        }
    }

    static function saveSet($set)
    {
        return update_option(Config::$set_name, base64_encode(json_encode($set)));
    }

    private static function updateOptions($set, $default_set)
    {
        foreach ($default_set as $key => &$item) {
            if (isset($set[$key])) {
                $item = $set[$key];
            }
        }
        return $default_set;
    }
}