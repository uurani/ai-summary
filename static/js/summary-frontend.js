// AI Summary 前端显示脚本
(function() {
    'use strict';

    // 等待DOM加载完成
    document.addEventListener('DOMContentLoaded', function() {
        initAISummary();
    });

    function initAISummary() {
        // 检查是否有AI摘要数据
        if (typeof ai_summary === 'undefined' || !ai_summary.summary) {
            return;
        }

        // 查找目标容器
        const targetContainer = document.getElementById('ai-summary-tool');
        if (!targetContainer) {
            return;
        }

        // 创建AI摘要组件
        createSummaryComponent(targetContainer, ai_summary);
    }

    function createSummaryComponent(container, data) {
        // 清空容器
        container.innerHTML = '';

        // 创建主容器
        const summaryContainer = document.createElement('div');
        summaryContainer.className = 'ai-summary-container';

        // 创建突出的机器人头像（放在容器外）
        const botSection = createBotSection(data);
        summaryContainer.appendChild(botSection);

        // 创建头部
        const header = createHeader(data);
        summaryContainer.appendChild(header);

        // 创建内容区域
        const content = createContent(data.summary, data.ai_summary_animation);
        summaryContainer.appendChild(content);

        // 创建底部
        const footer = createFooter(data);
        summaryContainer.appendChild(footer);

        // 添加到页面
        container.appendChild(summaryContainer);

        // 添加入场动画
        setTimeout(() => {
            summaryContainer.style.opacity = '1';
            summaryContainer.style.transform = 'translateY(0)';
        }, 100);
    }

    function createHeader(data) {
        const header = document.createElement('div');
        header.className = 'ai-summary-header';

        header.innerHTML = `
            <div class="ai-summary-title-row">
                <h3 class="ai-summary-title">AI文摘</h3>
                <div class="ai-summary-brand">
                    <a href="https://muxui.com/490/" target="_blank">AI Summary</a>
                </div>
            </div>
            <p class="ai-summary-subtitle">此内容由AI根据文章内容自动生成</p>
        `;

        return header;
    }

    function createBotSection(data) {
        const botSection = document.createElement('div');
        botSection.className = 'ai-summary-bot-section';

        // 直接使用bot.svg图标
        const botIconUrl = (data.img_url || '') + '/bot.svg';

        botSection.innerHTML = `
            <div class="ai-summary-main-bot" id="ai-main-bot">
                <!-- 初始容器，SVG加载后会添加眼睛 -->
            </div>
        `;

        // 直接加载bot.svg
        loadMainBotSvg(botIconUrl);

        return botSection;
    }

    function loadMainBotSvg(url) {
        // 如果url为空或无效，尝试使用相对路径
        if (!url || url.endsWith('/bot.svg')) {
            const possibleUrls = [
                url,
                './static/img/bot.svg',
                '../static/img/bot.svg',
                '/wp-content/plugins/ai-summary/static/img/bot.svg'
            ].filter(u => u); // 过滤空值
            
            tryLoadSvgFromUrls(possibleUrls, 0);
        } else {
            tryLoadSingleSvg(url);
        }
    }

    function tryLoadSvgFromUrls(urls, index) {
        if (index >= urls.length) {
            return;
        }
        
        const url = urls[index];
        
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }
                return response.text();
            })
            .then(svgText => {
                processSvgContent(svgText, url);
            })
            .catch(error => {
                // 尝试下一个URL
                tryLoadSvgFromUrls(urls, index + 1);
            });
    }

    function tryLoadSingleSvg(url) {
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }
                return response.text();
            })
            .then(svgText => {
                processSvgContent(svgText, url);
            })
            .catch(error => {
                // 静默失败
            });
    }

    function processSvgContent(svgText, url) {
        const botIconContainer = document.getElementById('ai-main-bot');
        if (botIconContainer && svgText.includes('<svg')) {
            // 解析SVG
            const parser = new DOMParser();
            const svgDoc = parser.parseFromString(svgText, 'image/svg+xml');
            const svgElement = svgDoc.querySelector('svg');
            
            if (svgElement) {
                // 设置SVG尺寸
                svgElement.setAttribute('width', '65');
                svgElement.setAttribute('height', '54');
                svgElement.style.position = 'relative';
                svgElement.style.top = '5px';
                
                // 先清空容器
                botIconContainer.innerHTML = '';
                
                // 添加SVG
                botIconContainer.appendChild(svgElement);
                
                // 创建眼睛元素
                createEyes(botIconContainer);
            }
        }
    }

    function createEyes(container) {
        // 创建左眼
        const leftEye = document.createElement('div');
        leftEye.className = 'bot-eye-left';
        
        // 创建右眼
        const rightEye = document.createElement('div');
        rightEye.className = 'bot-eye-right';
        
        // 添加到容器
        container.appendChild(leftEye);
        container.appendChild(rightEye);
    }

    function createContent(summaryText, enableAnimation) {
        const content = document.createElement('div');
        content.className = 'ai-summary-content';

        const textElement = document.createElement('p');
        textElement.className = 'ai-summary-text';

        if (enableAnimation && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            // 启用打字机效果
            textElement.innerHTML = '';
            content.appendChild(textElement);
            startTypingAnimation(textElement, summaryText);
        } else {
            // 直接显示文本
            textElement.textContent = summaryText;
            content.appendChild(textElement);
        }

        return content;
    }

    function createFooter(data) {
        const footer = document.createElement('div');
        footer.className = 'ai-summary-footer';

        const actions = document.createElement('div');
        actions.className = 'ai-summary-actions';

        // 显示关键词区域
        const keywordsSection = document.createElement('div');
        keywordsSection.className = 'ai-summary-keywords-section';

        // 添加"# 关键词"标题
        const keywordsTitle = document.createElement('div');
        keywordsTitle.className = 'ai-summary-keywords-title';
        keywordsTitle.textContent = '# 关键词';
        keywordsSection.appendChild(keywordsTitle);

        // 添加关键词标签容器
        const keywordsContainer = document.createElement('div');
        keywordsContainer.className = 'ai-summary-keywords-container';

        // 尝试从多个来源获取关键词
        let keywordList = [];
        
        // 方法1：从AI摘要数据中获取
        if (data.ai_summary_seo && data.ai_summary_seo.keywords) {
            const keywords = data.ai_summary_seo.keywords;
            if (typeof keywords === 'string') {
                keywordList = keywords.split(/[,，、\s]+/).filter(k => k.trim());
            } else if (Array.isArray(keywords)) {
                keywordList = keywords.filter(k => k && k.trim());
            }
        }
        
        // 方法2：从meta标签中获取（如果上面没有获取到）
        if (keywordList.length === 0) {
            const metaKeywords = document.querySelector('meta[name="keywords"]');
            if (metaKeywords && metaKeywords.content) {
                keywordList = metaKeywords.content.split(/[,，、]+/).map(k => k.trim()).filter(k => k);
            }
        }
        
        // 方法3：从AI摘要数据的其他字段获取
        if (keywordList.length === 0 && data.seo_keywords) {
            if (typeof data.seo_keywords === 'string') {
                keywordList = data.seo_keywords.split(/[,，、\s]+/).filter(k => k.trim());
            } else if (Array.isArray(data.seo_keywords)) {
                keywordList = data.seo_keywords.filter(k => k && k.trim());
            }
        }

        // 显示所有关键词
        if (keywordList.length > 0) {
            keywordList.forEach(keyword => {
                const keywordTag = document.createElement('span');
                keywordTag.className = 'ai-summary-keyword-tag';
                keywordTag.textContent = keyword.trim();
                keywordsContainer.appendChild(keywordTag);
            });
        }

        // 如果没有关键词，显示默认标签
        if (keywordsContainer.children.length === 0) {
            const defaultTag = document.createElement('span');
            defaultTag.className = 'ai-summary-keyword-tag';
            defaultTag.textContent = 'AI摘要';
            keywordsContainer.appendChild(defaultTag);
        }

        keywordsSection.appendChild(keywordsContainer);
        actions.appendChild(keywordsSection);
        footer.appendChild(actions);

        // 只保留反馈链接（如果有的话）
        if (data.ai_summary_feedback_url) {
            const settings = document.createElement('div');
            settings.className = 'ai-summary-settings';
            
            const feedbackLink = document.createElement('a');
            feedbackLink.href = data.ai_summary_feedback_url;
            feedbackLink.className = 'ai-summary-link';
            feedbackLink.target = '_blank';
            feedbackLink.textContent = '反馈';
            settings.appendChild(feedbackLink);
            
            footer.appendChild(settings);
        }

        return footer;
    }

    // 简化的打字机动画 - 移除眼睛动画
    function startTypingAnimation(element, text) {
        element.classList.add('typing');
        
        let charIndex = 0;
        const typingSpeed = 50; // 打字速度 (毫秒)
        
        function typeNextChar() {
            if (charIndex < text.length) {
                // 直接添加字符，保持换行
                element.textContent = text.substring(0, charIndex + 1);
                charIndex++;
                setTimeout(typeNextChar, typingSpeed);
            } else {
                // 打字完成
                element.classList.remove('typing');
            }
        }
        
        typeNextChar();
    }

    function getArticleContent() {
        // 尝试获取文章内容的多种方式
        const selectors = [
            '.entry-content',
            '.post-content', 
            '.content',
            'article',
            '.single-content',
            '#content'
        ];

        for (let selector of selectors) {
            const element = document.querySelector(selector);
            if (element) {
                return element.innerText.trim().substring(0, 3000); // 限制长度
            }
        }

        // 如果都找不到，返回页面标题作为fallback
        return document.title;
    }

    // 添加样式到页面
    function addStyles() {
        const style = document.createElement('style');
        style.textContent = `
            .ai-summary-container {
                opacity: 0;
                transform: translateY(20px);
                transition: all 0.5s ease;
            }
            
            /* 确保打字机光标在正确位置 */
            .ai-summary-text.typing {
                position: relative;
            }
        `;
        document.head.appendChild(style);
    }

    // 初始化样式
    addStyles();

})(); 