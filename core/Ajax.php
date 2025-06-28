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


}