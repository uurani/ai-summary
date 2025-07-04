// AI Summary 文章编辑页面脚本

jQuery(document).ready(function($) {
    'use strict';

    // 初始化AI助手面板
    initAIAssistant();

    function initAIAssistant() {
        if ($('#ai-summary-meta-box').length > 0) {
            renderAIInterface();
            bindEvents();
            
            // 延迟加载数据，确保所有元素都已渲染
            setTimeout(() => {
                loadExistingData();
            }, 500);
            
            // 再次延迟加载，处理动态加载的B2主题字段
            setTimeout(() => {
                if (!$('#ai-seo-description').val() && !$('#ai-seo-keywords').val()) {
                    console.log('AI字段为空，尝试从B2主题字段加载');
                    loadFromB2ThemeSEO();
                }
            }, 1500);
        }
    }

    // 渲染AI界面
    function renderAIInterface() {
        const html = `
            <div class="ai-assistant-panel">
                <div class="ai-section">
                    <h4><span class="dashicons dashicons-format-quote"></span> AI摘要</h4>
                    <div class="ai-content-area">
                        <textarea id="ai-summary-content" placeholder="点击生成AI摘要..." rows="4"></textarea>
                        <div class="ai-buttons">
                            <button type="button" class="button button-primary" id="generate-summary">
                                <span class="dashicons dashicons-admin-generic"></span> 生成摘要
                            </button>
                            <button type="button" class="button" id="clear-summary">清空</button>
                        </div>
                    </div>
                </div>
                
                <div class="ai-section">
                    <h4><span class="dashicons dashicons-search"></span> AI SEO</h4>
                    <div class="ai-content-area">
                        <div class="seo-field">
                            <label>SEO描述：</label>
                            <textarea id="ai-seo-description" placeholder="点击生成SEO描述..." rows="2"></textarea>
                        </div>
                        <div class="seo-field">
                            <label>关键词：</label>
                            <input type="text" id="ai-seo-keywords" placeholder="点击生成关键词...">
                        </div>
                        <div class="ai-buttons">
                            <button type="button" class="button button-primary" id="generate-seo">
                                <span class="dashicons dashicons-admin-generic"></span> 生成SEO
                            </button>
                            <button type="button" class="button" id="clear-seo">清空SEO</button>
                        </div>
                    </div>
                </div>
                
                <div class="ai-tips">
                    <p style="color: #666; font-size: 12px; margin: 10px 0;">
                        <span class="dashicons dashicons-info"></span> 
                        生成成功后将自动保存，记得点击"更新文章"完成保存
                    </p>
                </div>
            </div>
        `;
        
        $('#ai-summary-meta-box').html(html);
    }

    // 绑定事件
    function bindEvents() {
        // AI面板折叠展开功能
        $('.ai-section h4').off('click').on('click', function() {
            const section = $(this).closest('.ai-section');
            const contentArea = section.find('.ai-content-area');
            
            // 切换折叠状态
            section.toggleClass('collapsed');
            
            // 滑动动画
            contentArea.slideToggle(300);
            
            // 保存状态到localStorage
            const sectionId = section.index();
            const isCollapsed = section.hasClass('collapsed');
            localStorage.setItem(`ai-section-${sectionId}-collapsed`, isCollapsed);
        });
        
        // 恢复之前保存的折叠状态
        $('.ai-section').each(function(index) {
            const saved = localStorage.getItem(`ai-section-${index}-collapsed`);
            if (saved === 'true') {
                $(this).addClass('collapsed');
                $(this).find('.ai-content-area').hide();
            }
        });
        
        // 生成摘要
        $('#generate-summary').on('click', function() {
            generateSummary();
        });

        // 生成SEO
        $('#generate-seo').on('click', function() {
            generateSEO();
        });

        // 清空功能
        $('#clear-summary').on('click', function() {
            $('#ai-summary-content').val('');
        });

        $('#clear-seo').on('click', function() {
            $('#ai-seo-description').val('');
            $('#ai-seo-keywords').val('');
        });

        // 保存数据功能已改为自动保存，无需手动绑定
        
        // 测试功能已移除
        
        // 添加实时同步功能 - AI字段变化时同步到B2主题字段
        $('#ai-seo-description').on('input blur', function() {
            const value = $(this).val();
            if (value) {
                syncToB2ThemeFields('description', value);
            }
        });
        
        $('#ai-seo-keywords').on('input blur', function() {
            const value = $(this).val();
            if (value) {
                syncToB2ThemeFields('keywords', value);
            }
        });
    }

    // 生成摘要
    function generateSummary() {
        const content = getPostContent();
        if (!content) {
            alert('请先编写文章内容');
            return;
        }

        const button = $('#generate-summary');
        const originalText = button.text();
        button.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> 生成中...');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            timeout: 30000, // 30秒超时
            dataType: 'json', // 明确指定JSON格式
            data: {
                action: ai_summary.ajax_name,
                fun: 'getSummary',
                content: content,
                title: getPostTitle()
            },
            success: function(response) {
                console.log('摘要生成完整响应:', response);
                console.log('response类型:', typeof response);
                console.log('response.code类型:', typeof response.code, 'value:', response.code);
                
                // 如果响应是字符串，尝试解析JSON
                if (typeof response === 'string') {
                    try {
                        response = JSON.parse(response);
                        console.log('摘要JSON解析后:', response);
                    } catch (e) {
                        console.log('摘要JSON解析失败:', e);
                        showNotice(`响应解析错误：${response.substring(0, 200)}`, 'error');
                        return;
                    }
                }
                
                if (response.code == 200 || response.code === '200') {
                    // 调试输出
                    console.log('摘要生成结果:', response.data);
                    
                    // 摘要数据是字符串格式，直接填充
                    if (response.data && typeof response.data === 'string') {
                        $('#ai-summary-content').val(response.data);
                        
                        // 自动保存AI数据
                        autoSaveAIData();
                        
                        showNotice('摘要生成成功并已自动保存！', 'success');
                    } else {
                        console.log('摘要数据格式错误:', typeof response.data, response.data);
                        showNotice('摘要数据格式错误', 'error');
                    }
                } else {
                    console.log('摘要code不等于200:', response.code);
                    showNotice('摘要生成失败：' + (response.msg || '未知错误'), 'error');
                }
            },
            error: function(xhr, status, error) {
                console.log('摘要AJAX错误:', xhr, status, error);
                console.log('响应文本:', xhr.responseText);
                if (status === 'timeout') {
                    showNotice('请求超时，请重试', 'error');
                } else if (xhr.responseText) {
                    showNotice('服务器错误：' + xhr.responseText.substring(0, 100), 'error');
                } else {
                    showNotice('网络错误，请重试', 'error');
                }
            },
            complete: function() {
                button.prop('disabled', false).html(originalText);
            }
        });
    }

    // 生成SEO
    function generateSEO() {
        const content = getPostContent();
        if (!content) {
            alert('请先编写文章内容');
            return;
        }

        const button = $('#generate-seo');
        const originalText = button.text();
        button.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> 生成中...');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            timeout: 30000, // 30秒超时
            dataType: 'json', // 明确指定JSON格式
            data: {
                action: ai_summary.ajax_name,
                fun: 'getSeo',
                content: content,
                title: getPostTitle()
            },
            success: function(response) {
                console.log('SEO生成完整响应:', response);
                console.log('response类型:', typeof response);
                console.log('response.code类型:', typeof response.code, 'value:', response.code);
                
                // 如果响应是字符串，先显示原始内容再尝试解析
                if (typeof response === 'string') {
                    console.log('收到字符串响应，长度:', response.length);
                    console.log('原始响应内容:', response);
                    
                    // 如果是空字符串
                    if (!response || response.trim() === '') {
                        showNotice('服务器返回空响应', 'error');
                        return;
                    }
                    
                    try {
                        response = JSON.parse(response);
                        console.log('JSON解析后:', response);
                    } catch (e) {
                        console.log('JSON解析失败:', e);
                        console.log('尝试解析的内容:', response.substring(0, 500));
                        showNotice(`JSON解析失败，服务器返回：${response.substring(0, 200)}`, 'error');
                        return;
                    }
                }
                
                if (response.code == 200 || response.code === '200') {
                    const data = response.data;
                    
                    // 调试输出
                    console.log('SEO生成结果:', data);
                    
                    // 检查数据结构
                    if (typeof data === 'object' && data.description && data.keywords) {
                        // 填充插件自己的SEO字段
                        $('#ai-seo-description').val(data.description);
                        $('#ai-seo-keywords').val(Array.isArray(data.keywords) ? data.keywords.join(', ') : data.keywords);
                        
                        // 自动填充7b2主题的SEO字段
                        fillB2ThemeSEO(data);
                        
                        // 自动保存AI数据
                        autoSaveAIData();
                        
                        showNotice('SEO信息生成成功并已自动保存！', 'success');
                    } else {
                        // 如果返回的是字符串，尝试解析
                        console.log('收到的数据格式不正确:', typeof data, data);
                        showNotice('SEO数据格式错误，请检查AI返回格式', 'error');
                    }
                } else {
                    showNotice('SEO生成失败：' + (response.msg || '未知错误'), 'error');
                }
            },
            error: function(xhr, status, error) {
                console.log('SEO AJAX错误:', xhr, status, error);
                console.log('响应文本:', xhr.responseText);
                if (status === 'timeout') {
                    showNotice('请求超时，请重试', 'error');
                } else if (xhr.responseText) {
                    showNotice('服务器错误：' + xhr.responseText.substring(0, 100), 'error');
                } else {
                    showNotice('网络错误，请重试', 'error');
                }
            },
            complete: function() {
                button.prop('disabled', false).html(originalText);
            }
        });
    }

    // 获取文章内容
    function getPostContent() {
        let content = '';
        
        // 尝试获取经典编辑器内容
        if (typeof tinymce !== 'undefined' && tinymce.get('content')) {
            content = tinymce.get('content').getContent({format: 'text'});
        }
        // 尝试获取文本编辑器内容
        else if ($('#content').length > 0) {
            content = $('#content').val();
        }
        // 尝试获取Gutenberg编辑器内容
        else if (typeof wp !== 'undefined' && wp.data && wp.data.select('core/editor')) {
            const blocks = wp.data.select('core/editor').getBlocks();
            content = blocks.map(block => block.attributes.content || '').join('\n');
        }

        return content.trim();
    }

    // 获取文章标题
    function getPostTitle() {
        let title = '';
        
        // 尝试获取经典编辑器标题
        if ($('#title').length > 0) {
            title = $('#title').val();
        }
        // 尝试获取Gutenberg编辑器标题
        else if (typeof wp !== 'undefined' && wp.data && wp.data.select('core/editor')) {
            title = wp.data.select('core/editor').getEditedPostAttribute('title');
        }

        return title ? title.trim() : '';
    }

    // 自动保存AI数据（生成成功后调用）
    function autoSaveAIData() {
        console.log('自动保存AI数据开始');
        
        try {
            const data = {
                summary: $('#ai-summary-content').val() || '',
                post_description: $('#ai-seo-description').val() || '',
                post_keywords: $('#ai-seo-keywords').val() || '',
                last_update: new Date().toISOString()
            };
            
            console.log('自动保存数据:', data);

            // 更新隐藏字段
            updateHiddenField(data);
            console.log('AI数据已自动保存到隐藏字段');
            
        } catch (error) {
            console.error('自动保存AI数据出错:', error);
        }
    }

    // 手动保存AI数据（保留给可能的手动保存按钮）
    function saveAIData() {
        autoSaveAIData();
        showNotice('AI数据已保存，请点击"更新文章"完成保存', 'success');
    }
    
    // 测试保存功能
    function testSaveFunction() {
        console.log('=== 测试保存功能开始 ===');
        
        // 设置一些测试数据
        $('#ai-summary-content').val('这是测试摘要内容 - ' + new Date().toLocaleTimeString());
        $('#ai-seo-description').val('这是测试SEO描述 - ' + new Date().toLocaleTimeString());
        $('#ai-seo-keywords').val('测试关键词1, 测试关键词2, 测试关键词3');
        
        // 执行保存
        autoSaveAIData();
        
        // 检查隐藏字段
        const hiddenField = $('#ai-summary-meta-box-data');
        console.log('隐藏字段存在:', hiddenField.length > 0);
        console.log('隐藏字段name属性:', hiddenField.attr('name'));
        console.log('隐藏字段值长度:', hiddenField.val().length);
        
        if (hiddenField.val()) {
            try {
                // 使用Unicode安全的解码方法
                const decoded = JSON.parse(decodeURIComponent(escape(atob(hiddenField.val()))));
                console.log('解码后的数据:', decoded);
                showNotice('测试数据已设置并保存到隐藏字段！现在点击"更新文章"测试完整保存流程', 'success');
            } catch (e) {
                console.error('解码隐藏字段数据失败:', e);
                showNotice('隐藏字段数据格式有误', 'error');
            }
        } else {
            showNotice('隐藏字段为空，保存可能失败', 'error');
        }
        
        console.log('=== 测试保存功能结束 ===');
    }

    // 更新隐藏字段
    function updateHiddenField(data) {
        try {
            console.log('更新隐藏字段开始');
            // 使用Unicode安全的编码方法
            const jsonString = JSON.stringify(data);
            const encodedData = btoa(unescape(encodeURIComponent(jsonString)));
            console.log('编码后的数据长度:', encodedData.length);
            
            let hiddenField = $('#ai-summary-meta-box-data');
            console.log('现有隐藏字段数量:', hiddenField.length);
            
            const metaBox = $('#ai-summary-meta-box');
            console.log('Meta box存在:', metaBox.length > 0);
            
            if (hiddenField.length === 0) {
                console.log('创建新的隐藏字段');
                hiddenField = $('<input type="hidden" id="ai-summary-meta-box-data" name="ai-summary-meta-box" />');
                metaBox.append(hiddenField);
                console.log('隐藏字段已创建，name属性:', hiddenField.attr('name'));
            }
            
            hiddenField.val(encodedData);
            console.log('隐藏字段值已设置，验证:', hiddenField.val().length > 0);
            
        } catch (error) {
            console.error('更新隐藏字段出错:', error);
            throw error;
        }
    }

    // 加载现有数据
    function loadExistingData() {
        console.log('开始加载现有数据');
        
        // 1. 优先加载AI插件自己保存的数据
        if (typeof ai_summary_post_meta !== 'undefined' && ai_summary_post_meta.set) {
            const data = ai_summary_post_meta.set;
            console.log('找到AI插件数据:', data);
            
            if (data.summary) {
                $('#ai-summary-content').val(data.summary);
                console.log('加载摘要数据:', data.summary.substring(0, 50) + '...');
            }
            if (data.post_description) {
                $('#ai-seo-description').val(data.post_description);
                console.log('加载SEO描述:', data.post_description.substring(0, 50) + '...');
            }
            if (data.post_keywords) {
                $('#ai-seo-keywords').val(data.post_keywords);
                console.log('加载SEO关键词:', data.post_keywords);
            }
            return; // 如果有AI数据，就不需要从B2主题加载了
        }
        
        // 2. 如果没有AI数据，尝试从B2主题SEO字段加载
        console.log('没有AI插件数据，尝试从B2主题加载');
        loadFromB2ThemeSEO();
    }
    
    // 从B2主题SEO字段反向加载数据
    function loadFromB2ThemeSEO() {
        const seoFields = {
            description: ['b2_seo_description', 'seo_description', 'post_seo_description', 'seo-description', '_seo_description'],
            keywords: ['b2_seo_keywords', 'seo_keywords', 'post_seo_keywords', 'seo-keywords', '_seo_keywords']
        };
        
        // 加载SEO描述
        let description = '';
        for (const fieldName of seoFields.description) {
            const field = $(`[name="${fieldName}"], #${fieldName}`);
            if (field.length > 0 && field.val()) {
                description = field.val();
                console.log(`从${fieldName}加载描述:`, description.substring(0, 50) + '...');
                break;
            }
        }
        if (description && !$('#ai-seo-description').val()) {
            $('#ai-seo-description').val(description);
        }
        
        // 加载SEO关键词
        let keywords = '';
        for (const fieldName of seoFields.keywords) {
            const field = $(`[name="${fieldName}"], #${fieldName}`);
            if (field.length > 0 && field.val()) {
                keywords = field.val();
                console.log(`从${fieldName}加载关键词:`, keywords);
                break;
            }
        }
        if (keywords && !$('#ai-seo-keywords').val()) {
            $('#ai-seo-keywords').val(keywords);
        }
        
        // 尝试从自定义字段加载
        loadFromCustomFields();
    }
    
    // 从自定义字段加载
    function loadFromCustomFields() {
        const metaFields = [
            '_aioseop_description', '_yoast_wpseo_metadesc', '_seo_description',
            '_aioseop_keywords', '_yoast_wpseo_focuskw', '_seo_keywords'
        ];
        
        metaFields.forEach(fieldName => {
            const field = $(`input[name*="${fieldName}"], textarea[name*="${fieldName}"]`);
            if (field.length > 0 && field.val()) {
                if (fieldName.includes('description') && !$('#ai-seo-description').val()) {
                    $('#ai-seo-description').val(field.val());
                    console.log(`从自定义字段${fieldName}加载描述`);
                } else if (fieldName.includes('keywords') && !$('#ai-seo-keywords').val()) {
                    $('#ai-seo-keywords').val(field.val());
                    console.log(`从自定义字段${fieldName}加载关键词`);
                }
            }
        });
    }

    // 自动填充7b2主题的SEO字段
    function fillB2ThemeSEO(data) {
        try {
            // 7b2主题的SEO字段名称
            const seoFields = {
                title: ['b2_seo_title', 'seo_title', 'post_seo_title', 'seo-title'],
                description: ['b2_seo_description', 'seo_description', 'post_seo_description', 'seo-description', '_seo_description'],
                keywords: ['b2_seo_keywords', 'seo_keywords', 'post_seo_keywords', 'seo-keywords', '_seo_keywords']
            };
            
            // 获取文章标题作为SEO标题
            const postTitle = getPostTitle();
            
            // 尝试填充SEO标题（优先使用文章标题）
            if (postTitle) {
                seoFields.title.forEach(fieldName => {
                    const field = $(`[name="${fieldName}"], #${fieldName}`);
                    if (field.length > 0 && !field.val()) {
                        field.val(postTitle);
                    }
                });
            }
            
            // 填充SEO描述
            if (data.description) {
                seoFields.description.forEach(fieldName => {
                    const field = $(`[name="${fieldName}"], #${fieldName}`);
                    if (field.length > 0) {
                        field.val(data.description);
                    }
                });
            }
            
            // 填充SEO关键词
            if (data.keywords && data.keywords.length > 0) {
                const keywordsStr = data.keywords.join(', ');
                seoFields.keywords.forEach(fieldName => {
                    const field = $(`[name="${fieldName}"], #${fieldName}`);
                    if (field.length > 0) {
                        field.val(keywordsStr);
                    }
                });
            }
            
            // 特殊处理：如果是自定义字段形式
            fillCustomSEOFields(data, postTitle);
            
        } catch (error) {
            console.log('7b2主题SEO字段填充出错:', error);
        }
    }
    
    // 填充自定义SEO字段（适用于使用WordPress自定义字段的主题）
    function fillCustomSEOFields(data, postTitle) {
        // 检查是否存在自定义字段区域
        const customFieldsArea = $('#postcustom, .custom-fields-area, .postbox .inside');
        
        if (customFieldsArea.length > 0) {
            // 常见的SEO自定义字段名
            const metaFields = [
                '_aioseop_title', '_yoast_wpseo_title', '_seo_title',
                '_aioseop_description', '_yoast_wpseo_metadesc', '_seo_description',
                '_aioseop_keywords', '_yoast_wpseo_focuskw', '_seo_keywords'
            ];
            
            // 尝试找到并填充自定义字段
            metaFields.forEach(fieldName => {
                const field = $(`input[name*="${fieldName}"], textarea[name*="${fieldName}"]`);
                if (field.length > 0) {
                    if (fieldName.includes('title') && postTitle) {
                        field.val(postTitle);
                    } else if (fieldName.includes('description') && data.description) {
                        field.val(data.description);
                    } else if (fieldName.includes('keywords') && data.keywords) {
                        field.val(data.keywords.join(', '));
                    }
                }
            });
        }
    }
    
    // 实时同步AI字段到B2主题字段
    function syncToB2ThemeFields(type, value) {
        if (!value) return;
        
        const seoFields = {
            description: ['b2_seo_description', 'seo_description', 'post_seo_description', 'seo-description', '_seo_description'],
            keywords: ['b2_seo_keywords', 'seo_keywords', 'post_seo_keywords', 'seo-keywords', '_seo_keywords']
        };
        
        if (seoFields[type]) {
            seoFields[type].forEach(fieldName => {
                const field = $(`[name="${fieldName}"], #${fieldName}`);
                if (field.length > 0) {
                    field.val(value);
                    // 触发change事件，确保其他脚本能感知到变化
                    field.trigger('change');
                    console.log(`实时同步${type}到${fieldName}:`, value.substring(0, 50) + '...');
                }
            });
            
            // 同步到自定义字段
            const metaFields = type === 'description' 
                ? ['_aioseop_description', '_yoast_wpseo_metadesc', '_seo_description']
                : ['_aioseop_keywords', '_yoast_wpseo_focuskw', '_seo_keywords'];
            
            metaFields.forEach(fieldName => {
                const field = $(`input[name*="${fieldName}"], textarea[name*="${fieldName}"]`);
                if (field.length > 0) {
                    field.val(value);
                    field.trigger('change');
                    console.log(`实时同步${type}到自定义字段${fieldName}`);
                }
            });
        }
    }

    // 显示通知
    function showNotice(message, type = 'info') {
        const noticeClass = type === 'error' ? 'notice-error' : 'notice-success';
        const notice = $(`
            <div class="notice ${noticeClass} is-dismissible ai-notice">
                <p>${message}</p>
                <button type="button" class="notice-dismiss"></button>
            </div>
        `);
        
        $('.ai-notice').remove();
        $('#ai-summary-meta-box').prepend(notice);
        
        // 自动消失
        setTimeout(() => {
            notice.fadeOut();
        }, 3000);
        
        // 手动关闭
        notice.find('.notice-dismiss').on('click', function() {
            notice.remove();
        });
    }
}); 