<?php

namespace ai_summary;


class Ajax
{

    static function init()
    {

        add_action('wp_ajax_' . Config::$plugin_name, array(static::class, 'ajax'));
        add_action('wp_ajax_nopriv_' . Config::$plugin_name, array(static::class, 'ajax'));
    }

    static function ajax()
    {
        $fun_name = $_POST['fun'];
        if (!isset($fun_name)) {
            $fun_name = $_GET['fun'];
            if (!isset($fun_name)) {
                self::setAjaxDataAndDie(500, '参数错误');
                return;
            }
        }
        
        if (method_exists(static::class, $fun_name)) {
            call_user_func(array(static::class, $fun_name));
        } else {
            self::setAjaxDataAndDie(500, '方法不存在');
        }
    }

    static private function setAjaxDataAndDie($code, $msg = '', $data = null)
    {
        $_data['code'] = $code;
        $_data['msg'] = $msg;
        if ($data != null) {
            $_data['data'] = $data;
        }
        wp_die(json_encode($_data, JSON_UNESCAPED_UNICODE));
    }

    static private function validationParameters($arr)
    {
        $parameter = [];
        foreach ($arr as $item) {
            if ($item[1] == 1) {
                if (!isset($_POST[$item[0]])) {
                    self::setAjaxDataAndDie(500, '参数错误');
                }
                $parameter[$item[0]] = $_POST[$item[0]];
            } else {
                if (!isset($_GET[$item[0]])) {
                    self::setAjaxDataAndDie(500, '参数错误');
                }
                $parameter[$item[0]] = $_GET[$item[0]];
            }
        }

        return $parameter;
    }

    static private function needLogin($user_type = 'user')
    {
        if (!is_user_logged_in()) {
            self::setAjaxDataAndDie(500, '无权限访问');
        }
        if ($user_type == 'admin') {
            if (!WordPress::isAdmin()) {
                self::setAjaxDataAndDie(500, '权限不足');
            }
        }
        if ($user_type == 'edit') {
            if (!is_user_logged_in()) {
                self::setAjaxDataAndDie(500, '无权限访问');
            }
            if (!user_can(get_current_user_id(), 'edit_posts')) {
                self::setAjaxDataAndDie(500, '无权限访问');
            }
        }
    }

    static function getWenXinAccessToken()
    {
        self::needLogin('admin');
        $arr = [['client_id', 1], ['client_secret', 1]];
        $arr = self::validationParameters($arr);
        $re = WenXin::getAccessToken($arr['client_id'], $arr['client_secret']);
        if ($re === false) {
            self::setAjaxDataAndDie(500);
        }
        self::setAjaxDataAndDie(200, '', $re);

    }

    static function saveSet()
    {
        self::needLogin('admin');
        $arr = [['set', 1]];
        $arr = self::validationParameters($arr);
        $re = Options::saveSet(json_decode(base64_decode($arr['set'])));
        if ($re) {
            self::setAjaxDataAndDie(200);
        }
        self::setAjaxDataAndDie(500);
    }

    static function testWenXin()
    {
        self::needLogin('admin');
        
        $arr = [['api_key', 1], ['model', 1]];
        $arr = self::validationParameters($arr);
        
        // 验证API Key格式
        if (strlen($arr['api_key']) < 20) {
            self::setAjaxDataAndDie(500, 'API Key格式不正确，请检查文心一言API Key');
        }
        
        // 验证模型名称
        $validModels = ['ernie-3.5-8k', 'ernie-4.0-8k', 'ernie-4.0-turbo-8k', 'ernie-3.5-128k', 'ernie-speed-8k', 'ernie-speed-128k'];
        if (!in_array($arr['model'], $validModels)) {
            self::setAjaxDataAndDie(500, "无效的文心一言模型：{$arr['model']}");
        }
        
        $messages = [['role' => 'user', 'content' => '请回答ok']];
        $re = WenXin::chat($arr['model'], $messages, $arr['api_key'], '', 0.7);
        
        if ($re !== false) {
            self::setAjaxDataAndDie(200, '', $re);
        }
        self::setAjaxDataAndDie(500, '文心一言连接失败，请检查API Key、模型名称和网络连接');
    }

    static function getSummary()
    {
        self::needLogin('edit');
        $arr = [['content', 1]];
        $arr = self::validationParameters($arr);
        
        // 获取文章标题（如果在文章编辑页面）
        $title = isset($_POST['title']) ? $_POST['title'] : '';
        
        // 提取关键内容，限制在500字以内
        $keyContent = Tools::extractKeyContent($arr['content'], $title, 500);
        
        if (empty($keyContent)) {
            self::setAjaxDataAndDie(500, '文章内容过短，无法生成摘要');
        }
        
        $re = AI::getSummary($keyContent);
        if ($re !== false) {
            self::setAjaxDataAndDie(200, '', $re);
        }
        self::setAjaxDataAndDie(500, '获取摘要失败，请检查AI通道');
    }


    static function getSeo()
    {
        self::needLogin('edit');
        $arr = [['content', 1]];
        $arr = self::validationParameters($arr);
        
        // 获取文章标题（如果在文章编辑页面）
        $title = isset($_POST['title']) ? $_POST['title'] : '';
        
        // 提取关键内容，限制在500字以内
        $keyContent = Tools::extractKeyContent($arr['content'], $title, 500);
        
        if (empty($keyContent)) {
            self::setAjaxDataAndDie(500, '文章内容过短，无法生成SEO');
        }
        
        $re = AI::getSeo($keyContent);
        if ($re !== false) {
            self::setAjaxDataAndDie(200, '', $re);
        }
        self::setAjaxDataAndDie(500, '获取SEO失败，请检查AI通道');
    }

    static function testChatGPT()
    {
        self::needLogin('admin');
        $arr = [['api_key', 1], ['model', 1]];
        $arr = self::validationParameters($arr);
        
        // base_url是可选参数
        $base_url = isset($_POST['base_url']) ? $_POST['base_url'] : 'https://api.openai.com';
        
        $messages = [['role' => 'user', 'content' => '请回答ok']];
        $re = ChatGPT::chat($arr['model'], $messages, $arr['api_key'], '', 0.7, 100, $base_url);
        if ($re !== false) {
            self::setAjaxDataAndDie(200, '', $re);
        }
        self::setAjaxDataAndDie(500, 'ChatGPT连接失败，请检查API密钥、base_url和网络');
    }

    static function testGemini()
    {
        self::needLogin('admin');
        $arr = [['api_key', 1], ['model', 1]];
        $arr = self::validationParameters($arr);
        $messages = [['role' => 'user', 'content' => '请回答ok']];
        $re = Gemini::chat($arr['model'], $messages, $arr['api_key'], '', 0.7);
        if ($re !== false) {
            self::setAjaxDataAndDie(200, '', $re);
        }
        self::setAjaxDataAndDie(500, 'Gemini连接失败，请检查API密钥和网络');
    }

    static function testDoubao()
    {
        self::needLogin('admin');
        $arr = [['api_key', 1], ['model', 1]];
        $arr = self::validationParameters($arr);
        
        // base_url是可选参数
        $base_url = isset($_POST['base_url']) ? $_POST['base_url'] : 'https://ark.cn-beijing.volces.com/api/v3';
        
        $messages = [['role' => 'user', 'content' => '请回答ok']];
        $re = Doubao::chat($arr['model'], $messages, $arr['api_key'], '', 0.7, $base_url);
        if ($re !== false) {
            self::setAjaxDataAndDie(200, '', $re);
        }
        self::setAjaxDataAndDie(500, '豆包连接失败，请检查API密钥、base_url和网络');
    }

    static function testTongyi()
    {
        self::needLogin('admin');
        $arr = [['api_key', 1], ['model', 1]];
        $arr = self::validationParameters($arr);
        
        $messages = [['role' => 'user', 'content' => '请回答ok']];
        $re = Tongyi::chat($arr['model'], $messages, $arr['api_key'], '', 0.7);
        if ($re !== false) {
            self::setAjaxDataAndDie(200, '', $re);
        }
        self::setAjaxDataAndDie(500, '通义千问连接失败，请检查API密钥和网络');
    }

    static function getPostList()
    {
        self::needLogin('edit');
        $arr = [['page', 1], ['page_size', 1]];
        $arr = self::validationParameters($arr);
        $args = array(
            'post_type' => 'post',
            'posts_per_page' => $arr['page_size'],
            'orderby' => 'date',
            'order' => 'DESC',
            'paged' => $arr['page']
        );
        $list = [];
        $query = new \WP_Query($args);
        while ($query->have_posts()) : $query->the_post();
            $_post = [
                'date' => get_the_time('Y-m-d H:i:s'),
                'post_title' => get_the_title(),
                'post_url' => get_permalink(),
                'id' => get_the_ID(),
                'edit' => get_edit_post_link(get_the_ID(),'1')
            ];

            $_post['ai_meta'] = Options::getPostMeta($_post['id']);
            $list[] = $_post;
        endwhile;
        wp_reset_postdata();
        $data['total'] = $query->found_posts;
        $data['list'] = $list;
        self::setAjaxDataAndDie(200, '', $data);
    }

    static function generatePostSummary()
    {
        self::needLogin('edit');
        $arr = [['post_id', 1]];
        $arr = self::validationParameters($arr);
        
        $post_id = intval($arr['post_id']);
        $post = get_post($post_id);
        
        if (!$post) {
            self::setAjaxDataAndDie(500, '文章不存在');
        }
        
        $content = $post->post_content;
        $title = $post->post_title;
        
        // 提取关键内容，限制在500字以内
        $keyContent = Tools::extractKeyContent($content, $title, 500);
        
        if (empty($keyContent)) {
            self::setAjaxDataAndDie(500, '文章内容过短，无法生成摘要');
        }
        
        $summary = AI::getSummary($keyContent);
        if ($summary !== false) {
            // 更新文章meta
            $post_meta = Options::getPostMeta($post_id);
            $post_meta['summary'] = $summary;
            $post_meta['last_update'] = date('Y-m-d H:i:s');
            
            $encoded_data = base64_encode(json_encode($post_meta));
            update_post_meta($post_id, 'ai_summary_post_meta', $encoded_data);
            
            self::setAjaxDataAndDie(200, '摘要生成成功', $summary);
        }
        
        self::setAjaxDataAndDie(500, '摘要生成失败，请检查AI通道');
    }

    static function generatePostSeo()
    {
        self::needLogin('edit');
        $arr = [['post_id', 1]];
        $arr = self::validationParameters($arr);
        
        $post_id = intval($arr['post_id']);
        $post = get_post($post_id);
        
        if (!$post) {
            self::setAjaxDataAndDie(500, '文章不存在');
        }
        
        $content = $post->post_content;
        $title = $post->post_title;
        
        // 提取关键内容，限制在500字以内
        $keyContent = Tools::extractKeyContent($content, $title, 500);
        
        if (empty($keyContent)) {
            self::setAjaxDataAndDie(500, '文章内容过短，无法生成SEO');
        }
        
        $seo_data = AI::getSeo($keyContent);
        if ($seo_data !== false) {
            // 更新文章meta
            $post_meta = Options::getPostMeta($post_id);
            $post_meta['post_description'] = $seo_data['description'];
            $post_meta['post_keywords'] = implode(', ', $seo_data['keywords']);
            $post_meta['last_update'] = date('Y-m-d H:i:s');
            
            $encoded_data = base64_encode(json_encode($post_meta));
            update_post_meta($post_id, 'ai_summary_post_meta', $encoded_data);
            
            self::setAjaxDataAndDie(200, 'SEO信息生成成功', $seo_data);
        }
        
        self::setAjaxDataAndDie(500, 'SEO生成失败，请检查AI通道');
    }

    static function checkUpdate()
    {
        global $ai_summary_set;
        $ai_summary_set = Options::getOptions();
        $time = time();
        $last_time = $ai_summary_set['last_check_time'];
        if ($time - $last_time < 3600) {
            self::setAjaxDataAndDie(200, 'time not');
        }
        $plugin_info = Plugin::getServePluginInfo();
        $ai_summary_set['last_check_time'] = $time;
        if ($plugin_info !== false) {
            if (version_compare($plugin_info['version_name'], Config::$plugin_version_name, '>')) {
                $ai_summary_set['need_update'] = true;
            } else {
                $ai_summary_set['need_update'] = false;
            }
        }
        Options::saveSet($ai_summary_set);
        self::setAjaxDataAndDie(200, 'check success');
    }

    static function checkUpdateOnSet()
    {
        global $ai_summary_set;
        self::needLogin('admin');
        $data = Plugin::getServePluginInfo();
        $ai_summary_set = Options::getOptions();
        $ai_summary_set['last_check_time'] = time();
        $re_data['can_update'] = false;
        if ($data !== false) {
            $re_data['current_version'] = Config::$plugin_version_name;
            $re_data['latest_version'] = $data['version_name'];
            $re_data['download_url'] = $data['down_url'];
            if (version_compare($data['version_name'], Config::$plugin_version_name, '>')) {
                $ai_summary_set['need_update'] = true;
                $re_data['can_update'] = true;
            } else {
                $ai_summary_set['need_update'] = false;
            }
            Options::saveSet($ai_summary_set);
            self::setAjaxDataAndDie(200, 'success', $re_data);
        } else {
            self::setAjaxDataAndDie(500, '检测失败，请检查网络连接');
        }
    }

    static function startUpdate()
    {
        self::needLogin('admin');
        if (Config::$is_development) {
            self::setAjaxDataAndDie(500, '当前为开发模式，无法自动更新');
        }
        
        $plugin_info = Plugin::getServePluginInfo();
        if ($plugin_info === false) {
            self::setAjaxDataAndDie(500, '获取更新信息失败，请检查网络连接');
        }
        
        if (!version_compare($plugin_info['version_name'], Config::$plugin_version_name, '>')) {
            self::setAjaxDataAndDie(500, '当前已是最新版本');
        }
        
        //准备更新
        if (!isset($plugin_info['down_url']) || empty($plugin_info['down_url'])) {
            self::setAjaxDataAndDie(500, '更新失败，未获取到下载地址');
        }
        
        $url = $plugin_info['down_url'];
        $temp_dir = WP_PLUGIN_DIR . '/ai_summary_update_tmp';
        
        // 创建临时目录
        if (!is_dir($temp_dir)) {
            if (!mkdir($temp_dir, 0755, true)) {
                self::setAjaxDataAndDie(500, '创建临时目录失败，请检查文件权限');
            }
        }
        
        try {
            // 下载文件
            $file_name = 'ai-summary-' . time() . '.zip';
            $file_path = $temp_dir . '/' . $file_name;
            
            $response = wp_remote_get($url, ['timeout' => 300]);
            if (is_wp_error($response)) {
                Tools::delDir($temp_dir);
                self::setAjaxDataAndDie(500, '下载失败：' . $response->get_error_message());
            }
            
            $body = wp_remote_retrieve_body($response);
            if (empty($body)) {
                Tools::delDir($temp_dir);
                self::setAjaxDataAndDie(500, '下载的文件为空');
            }
            
            if (file_put_contents($file_path, $body) === false) {
                Tools::delDir($temp_dir);
                self::setAjaxDataAndDie(500, '保存文件失败，请检查文件权限');
            }
            
            // 验证ZIP文件
            $zip = new \ZipArchive();
            $result = $zip->open($file_path);
            if ($result !== true) {
                Tools::delDir($temp_dir);
                self::setAjaxDataAndDie(500, '文件解压失败，下载的文件可能已损坏');
            }
            
            // 先解压到临时目录查看结构
            $extract_temp_dir = $temp_dir . '/extract';
            if (!mkdir($extract_temp_dir, 0755, true)) {
                $zip->close();
                Tools::delDir($temp_dir);
                self::setAjaxDataAndDie(500, '创建解压临时目录失败');
            }
            
            if (!$zip->extractTo($extract_temp_dir)) {
                $zip->close();
                Tools::delDir($temp_dir);
                self::setAjaxDataAndDie(500, '解压失败，可能没有权限或磁盘空间不足');
            }
            $zip->close();
            
            // 检查解压的目录结构
            $extracted_files = scandir($extract_temp_dir);
            $extracted_files = array_diff($extracted_files, array('.', '..'));
            
            if (empty($extracted_files)) {
                Tools::delDir($temp_dir);
                self::setAjaxDataAndDie(500, '解压的文件为空');
            }
            
            // 确定源目录路径
            $source_dir = '';
            if (count($extracted_files) == 1 && is_dir($extract_temp_dir . '/' . reset($extracted_files))) {
                // ZIP包含单个目录，很可能是 ai-summary/ 目录
                $folder_name = reset($extracted_files);
                $source_dir = $extract_temp_dir . '/' . $folder_name;
                
                // 验证是否是正确的插件目录
                if (!file_exists($source_dir . '/index.php') || !file_exists($source_dir . '/core/Config.php')) {
                    Tools::delDir($temp_dir);
                    self::setAjaxDataAndDie(500, '下载的ZIP文件结构不正确，缺少必要文件');
                }
            } else {
                // ZIP直接包含插件文件
                $source_dir = $extract_temp_dir;
                if (!file_exists($source_dir . '/index.php') || !file_exists($source_dir . '/core/Config.php')) {
                    Tools::delDir($temp_dir);
                    self::setAjaxDataAndDie(500, '下载的ZIP文件结构不正确，缺少必要文件');
                }
            }
            
            // 备份当前插件
            $backup_dir = WP_PLUGIN_DIR . '/ai-summary-backup-' . time();
            if (!rename(Config::$plugin_dir, $backup_dir)) {
                Tools::delDir($temp_dir);
                self::setAjaxDataAndDie(500, '无法备份当前插件，请检查文件权限');
            }
            
            // 复制新文件到插件目录
            if (!rename($source_dir, Config::$plugin_dir)) {
                // 如果复制失败，恢复备份
                rename($backup_dir, Config::$plugin_dir);
                Tools::delDir($temp_dir);
                self::setAjaxDataAndDie(500, '无法复制新文件，已恢复原版本');
            }
            
            // 更新成功，删除备份
            Tools::delDir($backup_dir);
            
            // 更新配置
            global $ai_summary_set;
            $ai_summary_set = Options::getOptions();
            $ai_summary_set['need_update'] = false;
            $ai_summary_set['last_check_time'] = time();
            Options::saveSet($ai_summary_set);
            
            // 清理临时文件
            Tools::delDir($temp_dir);
            
            self::setAjaxDataAndDie(200, '更新成功！页面即将刷新...');
            
        } catch (Exception $e) {
            // 出错时清理临时文件
            if (is_dir($temp_dir)) {
                Tools::delDir($temp_dir);
            }
            self::setAjaxDataAndDie(500, '更新失败：' . $e->getMessage());
        }
    }
}