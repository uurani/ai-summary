<?php

namespace ai_summary;

class AI
{
    static function getSeo($content)
    {
        $ai_summary_set = Options::getOptions();
        
        // 从配置中获取SEO参数
        $keywords_count = isset($ai_summary_set['ai_seo_keywords_length']) ? intval($ai_summary_set['ai_seo_keywords_length']) : 10;
        $description_length = isset($ai_summary_set['ai_seo_description_length']) ? intval($ai_summary_set['ai_seo_description_length']) : 150;
        
        $messages = [['role' => 'user', 'content' => $content]];
        $system = "你是SEO专家，我现在传一篇文章内容给你。需要你给我总结一下keywords和description，keywords最多选{$keywords_count}个，多个词语用英文逗号隔开，description不要超过{$description_length}个字符。你只能输出纯JSON格式，例如：{\"keywords\":[\"关键词1\",\"关键词2\"],\"description\":\"文章描述\"}，不能有其他的语句。";
        
        $result = self::callAI($ai_summary_set['ai_seo_path'], $messages, $system, $ai_summary_set);

        if ($result !== false) {
            // 查找JSON开始和结束位置
            $json_start = strpos($result, '{');
            $json_end = strrpos($result, '}');
            
            if ($json_start !== false && $json_end !== false && $json_end > $json_start) {
                $json_content = substr($result, $json_start, $json_end - $json_start + 1);
                $json_data = json_decode($json_content, true);
                
                if ($json_data && isset($json_data['keywords']) && isset($json_data['description'])) {
                    // 处理关键词
                    if (is_array($json_data['keywords'])) {
                        $keywords = $json_data['keywords'];
                    } else {
                        $keywords = explode(',', strval($json_data['keywords']));
                    }
                    
                    // 清理关键词
                    $keywords = array_map('trim', $keywords);
                    $keywords = array_filter($keywords, function($k) { return !empty($k); });
                    
                    $data = [
                        'keywords' => array_values($keywords), // 重新索引数组
                        'description' => strval($json_data['description'])
                    ];
                    
                    return $data;
                }
            }
            
            return false;
        }
        return false;
    }
    
    static function getSummary($content)
    {
        $ai_summary_set = Options::getOptions();
        $messages = [['role' => 'user', 'content' => $content]];
        $system = '你是一个专业的内容摘要专家。请根据用户提供的文章内容，生成一个简洁、准确且吸引人的摘要。摘要应该：
1. 控制在' . $ai_summary_set['ai_summary_word_number'] . '字以内
2. 突出文章的核心观点和重要信息
3. 使用通俗易懂的语言
4. 具有吸引力，能激发读者的阅读兴趣
5. 保持客观中性的语调
请直接输出摘要内容，不要包含"摘要："、"总结："等前缀，也不要包含任何其他说明文字。';
        
        return self::callAI($ai_summary_set['ai_summary_path'], $messages, $system, $ai_summary_set);
    }
    
    private static function callAI($provider, $messages, $system, $settings)
    {
        switch ($provider) {
            case 'wenxin':
                if (empty($settings['wenxin_api_key'])) {
                    return false;
                }
                $model = isset($settings['wenxin_model']) ? $settings['wenxin_model'] : 'ernie-3.5-8k';
                $base_url = isset($settings['wenxin_base_url']) ? $settings['wenxin_base_url'] : '';
                $custom_model = isset($settings['wenxin_custom_model']) ? $settings['wenxin_custom_model'] : '';
                return WenXin::chat($model, $messages, $settings['wenxin_api_key'], $system, 0.7, $base_url, $custom_model);
                
            case 'chatgpt':
                if (empty($settings['chatgpt_api_key'])) {
                    return false;
                }
                $base_url = isset($settings['chatgpt_base_url']) && !empty($settings['chatgpt_base_url'])
                    ? $settings['chatgpt_base_url']
                    : 'https://api.openai.com';
                $custom_model = isset($settings['chatgpt_custom_model']) ? $settings['chatgpt_custom_model'] : '';
                return ChatGPT::chat($settings['chatgpt_model'], $messages, $settings['chatgpt_api_key'], $system, 0.7, 2000, $base_url, $custom_model);
                
            case 'gemini':
                if (empty($settings['gemini_api_key'])) {
                    return false;
                }
                $base_url = isset($settings['gemini_base_url']) ? $settings['gemini_base_url'] : '';
                $custom_model = isset($settings['gemini_custom_model']) ? $settings['gemini_custom_model'] : '';
                return Gemini::chat($settings['gemini_model'], $messages, $settings['gemini_api_key'], $system, 0.7, $base_url, $custom_model);
                
            case 'doubao':
                if (empty($settings['doubao_api_key'])) {
                    return false;
                }
                // 优先使用自定义模型，如果没有则使用选择的模型
                $model = !empty($settings['doubao_custom_model']) 
                    ? $settings['doubao_custom_model'] 
                    : $settings['doubao_model'];
                $base_url = isset($settings['doubao_base_url']) && !empty($settings['doubao_base_url']) 
                    ? $settings['doubao_base_url'] 
                    : 'https://ark.cn-beijing.volces.com/api/v3';
                return Doubao::chat($model, $messages, $settings['doubao_api_key'], $system, 0.7, $base_url);
                
            case 'tongyi':
                if (empty($settings['tongyi_api_key'])) {
                    return false;
                }
                $base_url = isset($settings['tongyi_base_url']) ? $settings['tongyi_base_url'] : '';
                $custom_model = isset($settings['tongyi_custom_model']) ? $settings['tongyi_custom_model'] : '';
                return Tongyi::chat($settings['tongyi_model'], $messages, $settings['tongyi_api_key'], $system, 0.7, $base_url, $custom_model);
                
            default:
                return false;
        }
    }
}