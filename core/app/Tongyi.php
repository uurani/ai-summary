<?php

namespace ai_summary;

class Tongyi
{
    static function chat($model, $messages, $api_key, $system = '', $temperature = 0.7)
    {
        $url = "https://dashscope.aliyuncs.com/compatible-mode/v1/chat/completions";
        
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
            error_log('Tongyi API Error: ' . $json['error']['message']);
            return false;
        }
        
        return false;
    }
    
    static function getModels()
    {
        return [
            ['name' => 'qwen-long', 'note' => '长文本版 - 输入0.5元/百万token，输出2元/百万token'],
            ['name' => 'qwen-turbo', 'note' => '极速版 - 输入2元/百万token，输出6元/百万token'],
            ['name' => 'qwen-plus', 'note' => '通用增强版 - 输入4元/百万token，输出12元/百万token'],
            ['name' => 'qwen-max', 'note' => '旗舰版 - 输入40元/百万token，输出120元/百万token']
        ];
    }
} 