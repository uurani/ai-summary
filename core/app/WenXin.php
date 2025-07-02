<?php

namespace ai_summary;

class WenXin
{
    /**
     * 使用API Key调用千帆大模型统一接口
     */
    static function chat($model, $messages, $api_key, $system = '', $temperature = 0.7, $base_url = '', $custom_model = '')
    {
        // 优先使用自定义模型名称
        if (!empty($custom_model)) {
            $model = $custom_model;
        }
        
        // 处理自定义API地址
        if (empty($base_url)) {
            $base_url = 'https://qianfan.baidubce.com';
        }
        $base_url = rtrim($base_url, '/');
        
        $url = "{$base_url}/v2/chat/completions";
        
        // 构建消息数组
        $finalMessages = [];
        
        // 添加系统消息
        if (!empty($system)) {
            $finalMessages[] = ['role' => 'system', 'content' => $system];
        }
        
        // 添加用户消息
        foreach ($messages as $message) {
            $finalMessages[] = $message;
        }
        
        // 构建请求数据
        $data = [
            'messages' => $finalMessages,
            'model' => $model,
            'temperature' => $temperature,
            'top_p' => 0.8,
            'penalty_score' => 1.0,
            'disable_search' => true,
            'enable_citation' => false,
            'max_tokens' => 2048,
            'stream' => false
        ];
        
        // 构建请求头
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $api_key
        ];
        
        // 发送请求
        $response = Tools::postJson($url, json_encode($data), $headers);
        
        if ($response === false) {
            return false;
        }
        
        $json = json_decode($response, true);
        
        if (!$json) {
            return false;
        }
        
        // 检查API响应
        if (isset($json['choices'][0]['message']['content'])) {
            return $json['choices'][0]['message']['content'];
        }
        
        // 处理错误
        if (isset($json['error']['message'])) {
            return false;
        }
        
        if (isset($json['error_msg'])) {
            return false;
        }
        
        return false;
    }
    
    /**
     * 获取支持的模型列表
     */
    static function getModels()
    {
        return [
            ['name' => 'ernie-3.5-8k', 'note' => '默认推荐模型'],
            ['name' => 'ernie-4.0-8k', 'note' => '最新4.0版本'],
            ['name' => 'ernie-4.0-turbo-8k', 'note' => '4.0涡轮版'],
            ['name' => 'ernie-3.5-128k', 'note' => '长上下文版本'],
            ['name' => 'ernie-speed-8k', 'note' => '速度优化版'],
            ['name' => 'ernie-speed-128k', 'note' => '速度+长上下文']
        ];
    }
}