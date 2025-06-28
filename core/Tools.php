<?php

namespace ai_summary;

class Tools
{
    static function post($url, $data = [])
    {
        //curl 发送post请求，并且忽略ssl证书验证
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }

    static function postJson($url, $jsonStr = '', $customHeaders = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        // 默认headers
        $headers = array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen($jsonStr)
        );
        
        // 合并自定义headers
        if (!empty($customHeaders)) {
            $headers = array_merge($headers, $customHeaders);
        }
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        // 添加超时和SSL配置
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        
        $response = curl_exec($ch);
        
        // 添加错误检查
        if (curl_error($ch)) {
            error_log('Tools::postJson cURL Error: ' . curl_error($ch) . ' for URL: ' . $url);
            curl_close($ch);
            return false;
        }
        
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // 记录非200状态码
        if ($http_code !== 200) {
            error_log("Tools::postJson HTTP Error: {$http_code} for URL: {$url}, Response: " . substr($response, 0, 500));
        }
        
        return $response;
    }

    /**
     * 提取文章关键内容，用于AI摘要和SEO生成
     * @param string $content 原始文章内容
     * @param string $title 文章标题
     * @param int $maxLength 最大字符数，默认500
     * @return string 提取的关键内容
     */
    static function extractKeyContent($content, $title = '', $maxLength = 500)
    {
        // 移除HTML标签和特殊字符
        $content = wp_strip_all_tags($content);
        $content = preg_replace('/\s+/', ' ', $content); // 合并多个空白字符
        $content = trim($content);
        
        if (empty($content)) {
            return '';
        }
        
        $keyContent = [];
        $usedLength = 0;
        
        // 1. 添加标题（如果提供）
        if (!empty($title)) {
            $title = trim($title);
            $keyContent[] = "标题：{$title}";
            $usedLength += mb_strlen($title) + 10;
        }
        
        // 2. 分割段落
        $paragraphs = preg_split('/[\r\n]+/', $content);
        $paragraphs = array_filter($paragraphs, function($p) {
            return mb_strlen(trim($p)) > 20; // 过滤过短的段落
        });
        
        if (empty($paragraphs)) {
            return implode(' ', $keyContent);
        }
        
        // 3. 提取首段（完整）
        if (count($paragraphs) > 0) {
            $firstParagraph = trim($paragraphs[0]);
            $firstLength = mb_strlen($firstParagraph);
            
            if ($usedLength + $firstLength < $maxLength * 0.4) { // 首段不超过40%
                $keyContent[] = "开头：{$firstParagraph}";
                $usedLength += $firstLength + 10;
            } else {
                // 如果首段太长，截取前部分
                $truncated = mb_substr($firstParagraph, 0, intval($maxLength * 0.3));
                $keyContent[] = "开头：{$truncated}...";
                $usedLength += mb_strlen($truncated) + 20;
            }
        }
        
        // 4. 提取中间关键段落
        if (count($paragraphs) > 2) {
            $middleStart = 1;
            $middleEnd = count($paragraphs) - 1;
            $remainingLength = $maxLength - $usedLength - 100; // 为结尾预留100字符
            
            for ($i = $middleStart; $i < $middleEnd && $remainingLength > 50; $i++) {
                $paragraph = trim($paragraphs[$i]);
                $paraLength = mb_strlen($paragraph);
                
                // 跳过过短的段落
                if ($paraLength < 30) continue;
                
                // 优先选择包含关键词的段落
                if (self::containsKeywords($paragraph)) {
                    if ($paraLength <= $remainingLength) {
                        $keyContent[] = "要点：{$paragraph}";
                        $usedLength += $paraLength + 10;
                        $remainingLength -= $paraLength + 10;
                    } else {
                        $truncated = mb_substr($paragraph, 0, $remainingLength - 20);
                        $keyContent[] = "要点：{$truncated}...";
                        $usedLength += $remainingLength;
                        break;
                    }
                }
            }
        }
        
        // 5. 提取结尾段（如果有空间）
        if (count($paragraphs) > 1) {
            $lastParagraph = trim($paragraphs[count($paragraphs) - 1]);
            $lastLength = mb_strlen($lastParagraph);
            $remainingLength = $maxLength - $usedLength;
            
            if ($remainingLength > 50 && $lastLength > 20) {
                if ($lastLength <= $remainingLength - 10) {
                    $keyContent[] = "结尾：{$lastParagraph}";
                } else {
                    $truncated = mb_substr($lastParagraph, -min($remainingLength - 20, $lastLength));
                    $keyContent[] = "结尾：{$truncated}";
                }
            }
        }
        
        $result = implode(' ', $keyContent);
        
        // 确保不超过最大长度
        if (mb_strlen($result) > $maxLength) {
            $result = mb_substr($result, 0, $maxLength - 3) . '...';
        }
        
        return $result;
    }
    
    /**
     * 检查段落是否包含关键词
     */
    private static function containsKeywords($paragraph)
    {
        $keywords = [
            // 中文关键词
            '重要', '关键', '主要', '核心', '基本', '原理', '方法', '步骤', '注意', '总结',
            '首先', '其次', '最后', '因此', '所以', '总之', '综上', '通过', '实现', '解决',
            '问题', '方案', '建议', '优势', '特点', '功能', '作用', '影响', '效果', '结果',
            // 英文关键词
            'important', 'key', 'main', 'core', 'basic', 'method', 'solution', 'result',
            'first', 'second', 'finally', 'therefore', 'conclusion', 'summary'
        ];
        
        $paragraph_lower = mb_strtolower($paragraph);
        foreach ($keywords as $keyword) {
            if (strpos($paragraph_lower, $keyword) !== false) {
                return true;
            }
        }
        
        return false;
    }

    static function delDir($dir)
    {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            if (is_dir("$dir/$file")) {
                $del_re = self::delDir("$dir/$file");
            } else {
                $del_re = unlink("$dir/$file");
            }
        }

        return rmdir($dir);
    }

    static function getSubstr($str, $leftStr, $rightStr)
    {
        $left = strpos($str, $leftStr);
        //echo '左边:'.$left;
        $right = strpos($str, $rightStr, $left);
        //echo '<br>右边:'.$right;
        if ($left < 0 or $right < $left) return '';
        return substr($str, $left + strlen($leftStr), $right - $left - strlen($leftStr));
    }
}