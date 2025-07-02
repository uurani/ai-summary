<?php

namespace ai_summary;

class ChatGPT
{
    static function chat($model, $messages, $api_key, $system = '', $temperature = 0.7, $max_tokens = 2000, $base_url = 'https://api.openai.com', $custom_model = '')
    {
        // 优先使用自定义模型名称
        if (!empty($custom_model)) {
            $model = $custom_model;
        }
        
        // 确保base_url以/结尾时去掉，避免双斜杠
        $base_url = rtrim($base_url, '/');
        $url = "{$base_url}/v1/chat/completions";
        
        $data = [
            'model' => $model,
            'messages' => [],
            'temperature' => $temperature,
            'max_tokens' => $max_tokens
        ];
        
        // 添加系统消息
        if (!empty($system)) {
            $data['messages'][] = ['role' => 'system', 'content' => $system];
        }
        
        // 添加用户消息
        foreach ($messages as $message) {
            $data['messages'][] = $message;
        }
        
        $headers = [
            'Authorization: Bearer ' . $api_key
        ];
        
        $response = Tools::postJson($url, json_encode($data), $headers);
        $json = json_decode($response, true);
        
        if (!$json) {
            return false;
        }
        
        if (isset($json['choices'][0]['message']['content'])) {
            return $json['choices'][0]['message']['content'];
        }
        
        if (isset($json['error'])) {
            error_log('ChatGPT API Error: ' . $json['error']['message']);
            return false;
        }
        
        return false;
    }
    
    static function getModels()
    {
        return [
            ['name' => 'gpt-3.5-turbo', 'note' => '速度快，成本低'],
            ['name' => 'gpt-3.5-turbo-16k', 'note' => '支持更长上下文'],
            ['name' => 'gpt-4', 'note' => '最高质量'],
            ['name' => 'gpt-4-turbo-preview', 'note' => '最新模型']
        ];
    }
    

} 