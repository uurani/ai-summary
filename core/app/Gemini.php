<?php

namespace ai_summary;

class Gemini
{
    static function chat($model, $messages, $api_key, $system = '', $temperature = 0.7)
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$api_key}";
        
        $contents = [];
        
        // 处理系统消息和用户消息
        if (!empty($system)) {
            $contents[] = [
                'role' => 'user',
                'parts' => [['text' => $system]]
            ];
        }
        
        foreach ($messages as $message) {
            $role = $message['role'] === 'user' ? 'user' : 'model';
            $contents[] = [
                'role' => $role,
                'parts' => [['text' => $message['content']]]
            ];
        }
        
        $data = [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => $temperature,
                'maxOutputTokens' => 2000
            ]
        ];
        
        $response = Tools::postJson($url, json_encode($data));
        $json = json_decode($response, true);
        
        if (!$json) {
            return false;
        }
        
        if (isset($json['candidates'][0]['content']['parts'][0]['text'])) {
            return $json['candidates'][0]['content']['parts'][0]['text'];
        }
        
        if (isset($json['error'])) {
            error_log('Gemini API Error: ' . $json['error']['message']);
            return false;
        }
        
        return false;
    }
    
    static function getModels()
    {
        return [
            ['name' => 'gemini-2.0-flash', 'note' => '最新2.0版本，速度最快'],
            ['name' => 'gemini-1.5-pro', 'note' => '1.5版本，功能全面'],
            ['name' => 'gemini-pro', 'note' => '经典版本'],
            ['name' => 'gemini-pro-vision', 'note' => '支持图像输入']
        ];
    }
    

} 