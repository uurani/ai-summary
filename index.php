<?php
/*
Plugin Name: AI Summary
Plugin URI: https://muxui.com/490/
Description: 为WordPress接入多种AI摘要生成能力，支持文心一言、ChatGPT、Gemini、豆包、通义千问
Version: 1.1.1
Requires at least: 5.7
Tested up to: 8.0
Requires PHP: 5.6
Author: muxui
Author URI: https://muxui.com/
Network: false
Text Domain: ai-summary
*/

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

require_once WP_PLUGIN_DIR . '/ai-summary/core/Config.php';