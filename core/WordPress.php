<?php

namespace ai_summary;

class WordPress
{
    static function loadJS($name, $file_name, $local = true, $need = [], $footer = false)
    {
        if ($local === true) {
            $file_path = Config::$js_url . "/$file_name";
        } else {
            $file_path = $file_name;
        }
        wp_enqueue_script($name, $file_path, $need, Config::$plugin_version, $footer);
    }

    static function echoJson($name, $data)
    {
        // 使用wp_add_inline_script来安全地定义全局变量
        $json_data = wp_json_encode($data);
        wp_add_inline_script('ai-summary-frontend', "window.{$name} = {$json_data};", 'before');
    }

    static function loadCss($name, $file_name, $local = true)
    {
        if ($local === true) {
            $file_path = Config::$css_url . "/$file_name";
        } else {
            $file_path = $file_name;
        }
        wp_enqueue_style($name, $file_path, [], Config::$plugin_version);
    }


    static function isAdmin()
    {
        if (current_user_can('manage_options')) {
            return true;
        }
        return false;
    }


    static function loadCssEcho($name)
    {
        echo '<link rel="stylesheet" href="' . Config::$css_url . '/' . $name . '?v=' . Config::$plugin_version . '">';
    }
}