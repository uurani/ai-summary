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
        $data = json_encode($data);
        echo "<script>let {$name}=JSON.parse('$data')</script>";
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