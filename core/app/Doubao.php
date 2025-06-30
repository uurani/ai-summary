<?php

namespace ai_summary;

class Doubao
{
    static function chat($model, $messages, $api_key, $system = '', $temperature = 0.7, $base_url = 'https://ark.cn-beijing.volces.com/api/v3')
    {
        // 确保base_url以/结尾时去掉，避免双斜杠
        $base_url = rtrim($base_url, '/');
        $url = "{$base_url}/chat/completions";
        
        $data = [
            'model' => $model,
            'messages' => [],
            'temperature' => $temperature,
            'max_tokens' => 2000
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
            'Authorization: Bearer ' . $api_key,
            'Content-Type: application/json'
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
            error_log('Doubao API Error: ' . $json['error']['message']);
            return false;
        }
        
        return false;
    }
    
    static function getModels()
    {
        return [
            ['name' => 'doubao-lite-32k-240828', 'note' => '轻量版，32K上下文'],
            ['name' => 'doubao-pro-4k-240515', 'note' => '专业版，4K上下文'],
            ['name' => 'doubao-pro-32k-241215', 'note' => '专业版，32K上下文'],
            ['name' => 'doubao-lite-4k-character-240828', 'note' => '轻量版，4K上下文，角色版']
        ];
    }
} 