// AI Summary 管理界面脚本

// 全局变量
let postsData = {
    posts: [],
    currentPage: 1,
    pageSize: 20,
    total: 0,
    totalPages: 1
};

jQuery(document).ready(function($) {
    'use strict';

    // 设置jQuery AJAX默认配置
    $.ajaxSetup({
        dataType: 'json',
        timeout: 60000
    });

    // 初始化
    init();

    function init() {
        initTabs();
        loadSettings();
        bindEvents();
        checkAIStatus();
        initPostsManagement();
    }

    // 标签页切换
    function initTabs() {
        $('.tab-button').on('click', function() {
            const tabId = $(this).data('tab');
            
            // 更新按钮状态
            $('.tab-button').removeClass('active');
            $(this).addClass('active');
            
            // 更新内容显示
            $('.tab-content').removeClass('active');
            $(`#${tabId}-tab`).addClass('active');
            
            // 如果切换到文章管理标签，自动加载列表
            if (tabId === 'posts' && postsData.posts.length === 0) {
                setTimeout(() => {
                    loadPostsList();
                }, 100);
            }
        });
    }

    // 加载设置
    function loadSettings() {
        if (typeof ai_summary !== 'undefined' && ai_summary.set) {
            const settings = ai_summary.set;
            
            // 基础设置
            $('#open_ai_summary').prop('checked', settings.open_ai_summary);
            $('#ai_summary_animation').prop('checked', settings.ai_summary_animation);
            $('#ai_summary_see_other_btn').prop('checked', settings.ai_summary_see_other_btn);
            
            // 文心一言配置
            $('#wenxin_api_key').val(settings.wenxin_api_key);
            $('#wenxin_model').val(settings.wenxin_model);
            
            // ChatGPT配置
            $('#chatgpt_api_key').val(settings.chatgpt_api_key);
            $('#chatgpt_model').val(settings.chatgpt_model);
            $('#chatgpt_base_url').val(settings.chatgpt_base_url);
            
            // Gemini配置
            $('#gemini_api_key').val(settings.gemini_api_key);
            $('#gemini_model').val(settings.gemini_model);
            
            // 摘要设置
            $(`input[name="ai_summary_path"][value="${settings.ai_summary_path}"]`).prop('checked', true);
            $('#ai_summary_word_number').val(settings.ai_summary_word_number);
            $('#ai_summary_feedback_url').val(settings.ai_summary_feedback_url);
            
            // SEO设置
            $('#ai_seo_open').prop('checked', settings.ai_seo_open);
            $(`input[name="ai_seo_path"][value="${settings.ai_seo_path}"]`).prop('checked', true);
            $('#ai_seo_description_length').val(settings.ai_seo_description_length);
            $('#ai_seo_keywords_length').val(settings.ai_seo_keywords_length);
        }
    }

    // 绑定事件
    function bindEvents() {
        // 测试连接按钮 - 添加防抖机制
        $('.btn-test').off('click').on('click', function() {
            const button = $(this);
            
            // 防止重复点击
            if (button.hasClass('loading')) {
                return false;
            }
            
            const provider = button.closest('.provider-card').find('.provider-header h3').text().trim();
            
            switch(provider) {
                case '文心一言':
                    testWenxin(button);
                    break;
                case 'ChatGPT':
                    testChatGPT(button);
                    break;
                case 'Google Gemini':
                    testGemini(button);
                    break;
            }
        });

        // 保存设置 - 移除重复选择器
        $('.btn-primary').off('click').on('click', function() {
            // 防止重复点击
            if ($(this).hasClass('loading')) {
                return false;
            }
            saveSettings();
        });
        
        // 重置设置
        $('.btn-secondary').off('click').on('click', resetSettings);

        // API密钥输入时检查状态
        $('#wenxin_api_key').on('input', function() {
            updateProviderStatus('wenxin');
        });
        
        $('#chatgpt_api_key').on('input', function() {
            updateProviderStatus('chatgpt');
        });
        
        $('#gemini_api_key').on('input', function() {
            updateProviderStatus('gemini');
        });
    }

    // 检查AI状态
    function checkAIStatus() {
        updateProviderStatus('wenxin');
        updateProviderStatus('chatgpt');
        updateProviderStatus('gemini');
    }

    // 更新提供商状态
    function updateProviderStatus(provider) {
        let hasConfig = false;
        let statusElement = $(`#${provider}-status`);
        
        switch(provider) {
            case 'wenxin':
                hasConfig = $('#wenxin_api_key').val();
                break;
            case 'chatgpt':
                hasConfig = $('#chatgpt_api_key').val();
                break;
            case 'gemini':
                hasConfig = $('#gemini_api_key').val();
                break;
        }
        
        if (hasConfig) {
            statusElement.text('已配置').removeClass('error').addClass('configured');
        } else {
            statusElement.text('未配置').removeClass('configured').addClass('error');
        }
    }

    // 测试文心一言
    function testWenxin(button) {
        const apiKey = $('#wenxin_api_key').val();
        const model = $('#wenxin_model').val();
        
        if (!apiKey) {
            showNotice('请填写文心一言API Key', 'error');
            return;
        }
        
        testAI(button, 'testWenXin', {
            api_key: apiKey,
            model: model
        });
    }

    // 测试ChatGPT
    function testChatGPT(button) {
        const apiKey = $('#chatgpt_api_key').val();
        const model = $('#chatgpt_model').val();
        const baseUrl = $('#chatgpt_base_url').val() || 'https://api.openai.com';
        
        if (!apiKey) {
            showNotice('请填写ChatGPT API Key', 'error');
            return;
        }
        
        testAI(button, 'testChatGPT', {
            api_key: apiKey,
            model: model,
            base_url: baseUrl
        });
    }

    // 测试Gemini
    function testGemini(button) {
        const apiKey = $('#gemini_api_key').val();
        const model = $('#gemini_model').val();
        
        if (!apiKey) {
            showNotice('请填写Gemini API Key', 'error');
            return;
        }
        
        testAI(button, 'testGemini', {
            api_key: apiKey,
            model: model
        });
    }

    // 通用AI测试函数
    function testAI(button, action, data) {
        const originalText = button.text();
        button.addClass('loading').text('测试中...');
        
        $.ajax({
            url: ai_summary.ajax_url,
            type: 'POST',
            data: {
                action: ai_summary.ajax_name,
                fun: action,
                ...data
            },
            success: function(response) {
                // 检查响应是否为字符串，需要解析JSON
                if (typeof response === 'string') {
                    try {
                        response = JSON.parse(response);
                    } catch (e) {
                        showNotice(`响应解析错误：${response.substring(0, 200)}`, 'error');
                        return;
                    }
                }
                
                if (response.code === 200) {
                    showNotice(`连接成功！AI回复：${response.data}`, 'success');
                    button.closest('.provider-card').find('.provider-status')
                        .text('连接正常').removeClass('error').addClass('connected');
                } else {
                    showNotice(`连接失败：${response.msg || '未知错误'}`, 'error');
                }
            },
            error: function(xhr, status, error) {
                let errorMsg = '网络错误：' + error;
                if (xhr.responseText) {
                    errorMsg += '，服务器响应：' + xhr.responseText.substring(0, 200);
                }
                if (status === 'timeout') {
                    errorMsg = '请求超时，请检查网络连接和API服务是否正常';
                }
                showNotice(errorMsg, 'error');
            },
            complete: function() {
                button.removeClass('loading').text(originalText);
            }
        });
    }

    // 保存设置
    function saveSettings() {
        const settings = {
            // 基础设置
            open_ai_summary: $('#open_ai_summary').is(':checked'),
            ai_summary_animation: $('#ai_summary_animation').is(':checked'),
            ai_summary_see_other_btn: $('#ai_summary_see_other_btn').is(':checked'),
            
            // 文心一言配置
            wenxin_api_key: $('#wenxin_api_key').val(),
            wenxin_model: $('#wenxin_model').val(),
            
            // ChatGPT配置
            chatgpt_api_key: $('#chatgpt_api_key').val(),
            chatgpt_model: $('#chatgpt_model').val(),
            chatgpt_base_url: $('#chatgpt_base_url').val() || 'https://api.openai.com',
            
            // Gemini配置
            gemini_api_key: $('#gemini_api_key').val(),
            gemini_model: $('#gemini_model').val(),
            
            // 摘要设置
            ai_summary_path: $('input[name="ai_summary_path"]:checked').val(),
            ai_summary_word_number: parseInt($('#ai_summary_word_number').val()) || 200,
            ai_summary_feedback_url: $('#ai_summary_feedback_url').val(),
            
            // SEO设置
            ai_seo_open: $('#ai_seo_open').is(':checked'),
            ai_seo_path: $('input[name="ai_seo_path"]:checked').val(),
            ai_seo_description_length: parseInt($('#ai_seo_description_length').val()) || 150,
            ai_seo_keywords_length: parseInt($('#ai_seo_keywords_length').val()) || 10,
            
            // 保留原有字段
            need_update: false,
            last_check_time: Date.now(),
            wenxin_access_token: '',
            wenxin_access_time: new Date().toISOString().split('T')[0]
        };
        
        const button = $('.btn-primary');
        const originalText = button.text();
        button.addClass('loading').text('保存中...');
        
        $.ajax({
            url: ai_summary.ajax_url,
            type: 'POST',
            data: {
                action: ai_summary.ajax_name,
                fun: 'saveSet',
                set: btoa(JSON.stringify(settings))
            },
            success: function(response) {
                // 检查响应是否为字符串，需要解析JSON
                if (typeof response === 'string') {
                    try {
                        response = JSON.parse(response);
                    } catch (e) {
                        showNotice(`响应解析错误：${response.substring(0, 200)}`, 'error');
                        return;
                    }
                }
                
                if (response.code === 200) {
                    showNotice('设置保存成功！', 'success');
                    // 更新全局设置对象
                    if (typeof ai_summary !== 'undefined') {
                        ai_summary.set = settings;
                    }
                } else {
                    showNotice(`保存失败：${response.msg || '未知错误'}`, 'error');
                }
            },
            error: function(xhr, status, error) {
                showNotice(`保存失败：${error}`, 'error');
            },
            complete: function() {
                button.removeClass('loading').text(originalText);
            }
        });
    }

    // 重置设置
    function resetSettings() {
        if (confirm('确定要重置所有设置吗？此操作不可恢复。')) {
                    // 重置表单
        $('#open_ai_summary, #ai_summary_animation, #ai_summary_see_other_btn, #ai_seo_open').prop('checked', false);
        $('#wenxin_client_id, #wenxin_client_secret, #wenxin_api_key, #chatgpt_api_key, #gemini_api_key, #ai_summary_feedback_url, #chatgpt_base_url').val('');
        $('#wenxin_auth_type').val('api_key');
        $('#wenxin_gpt_name').val('ernie-speed-128k');
        $('#wenxin_model').val('ERNIE-3.5-8K');
        $('#chatgpt_model').val('gpt-3.5-turbo');
        $('#gemini_model').val('gemini-2.0-flash');
        
        // 重置鉴权方式显示
        $('#wenxin-api-key-group').show();
        $('#wenxin-client-group').hide();
            $('input[name="ai_summary_path"][value="wenxin"]').prop('checked', true);
            $('input[name="ai_seo_path"][value="wenxin"]').prop('checked', true);
            $('#ai_summary_word_number').val(200);
            $('#ai_seo_description_length').val(150);
            $('#ai_seo_keywords_length').val(10);
            
            showNotice('设置已重置，请点击保存按钮应用更改', 'warning');
            checkAIStatus();
        }
    }

    // 显示通知
    function showNotice(message, type = 'info') {
        // 移除现有通知
        $('.notice').remove();
        
        const notice = $(`
            <div class="notice ${type}">
                ${message}
            </div>
        `);
        
        $('.ai-summary-admin-container').prepend(notice);
        
        // 自动消失
        setTimeout(() => {
            notice.fadeOut(() => notice.remove());
        }, 5000);
    }

    // 文章管理相关变量（已在全局定义）

    // 初始化文章管理
    function initPostsManagement() {
        // 绑定事件
        $('#refresh-posts').on('click', loadPostsList);
        $('#prev-page').on('click', () => changePage(-1));
        $('#next-page').on('click', () => changePage(1));
        $('#select-all-posts').on('change', toggleSelectAll);
        $('#batch-summary').on('click', () => batchGenerate('summary'));
        $('#batch-seo').on('click', () => batchGenerate('seo'));
    }

    // 加载文章列表
    function loadPostsList() {
        $('#posts-list').html('<tr><td colspan="7" class="loading-row">加载中...</td></tr>');
        
        $.ajax({
            url: ai_summary.ajax_url,
            type: 'POST',
            data: {
                action: ai_summary.ajax_name,
                fun: 'getPostList',
                page: postsData.currentPage,
                page_size: postsData.pageSize
            },
            success: function(response) {
                if (typeof response === 'string') {
                    try {
                        response = JSON.parse(response);
                    } catch (e) {
                        showNotice('文章列表加载失败：响应解析错误', 'error');
                        return;
                    }
                }
                
                if (response.code === 200) {
                    postsData.posts = response.data.list;
                    postsData.total = response.data.total;
                    postsData.totalPages = Math.ceil(postsData.total / postsData.pageSize);
                    
                    renderPostsList();
                    updatePagination();
                } else {
                    showNotice('文章列表加载失败：' + (response.msg || '未知错误'), 'error');
                }
            },
            error: function(xhr, status, error) {
                showNotice('文章列表加载失败：' + error, 'error');
            }
        });
    }

    // 渲染文章列表
    function renderPostsList() {
        const tbody = $('#posts-list');
        if (postsData.posts.length === 0) {
            tbody.html('<tr><td colspan="7" class="empty-row">暂无文章</td></tr>');
            return;
        }
        
        let html = '';
        postsData.posts.forEach((post, index) => {
            const summaryStatus = post.ai_meta && post.ai_meta.summary ? '已生成' : '未生成';
            const seoStatus = post.ai_meta && (post.ai_meta.post_description || post.ai_meta.post_keywords) ? '已生成' : '未生成';
            const summaryClass = post.ai_meta && post.ai_meta.summary ? 'status-success' : 'status-pending';
            const seoClass = post.ai_meta && (post.ai_meta.post_description || post.ai_meta.post_keywords) ? 'status-success' : 'status-pending';
            
            // 计算序号：(当前页-1) * 每页条数 + 当前索引 + 1
            const serialNumber = (postsData.currentPage - 1) * postsData.pageSize + index + 1;
            
            html += `
                <tr data-post-id="${post.id}">
                    <td><input type="checkbox" class="post-checkbox" value="${post.id}"></td>
                    <td class="serial-number">${serialNumber}</td>
                    <td>
                        <a href="${post.post_url}" target="_blank" class="post-title">${post.post_title}</a>
                    </td>
                    <td>${post.date}</td>
                    <td>
                        <span class="status-badge ${summaryClass}">${summaryStatus}</span>
                    </td>
                    <td>
                        <span class="status-badge ${seoClass}">${seoStatus}</span>
                    </td>
                    <td class="post-actions">
                        <button class="btn-small" onclick="generatePostSummary(${post.id})">生成摘要</button>
                        <button class="btn-small" onclick="generatePostSeo(${post.id})">生成SEO</button>
                        <a href="${post.edit}" target="_blank" class="btn-small">编辑</a>
                    </td>
                </tr>
            `;
        });
        tbody.html(html);
        
        // 绑定单个checkbox的变化事件
        $('.post-checkbox').on('change', function() {
            updateSelectAllStatus();
        });
    }

    // 更新分页信息
    function updatePagination() {
        $('#current-page').text(postsData.currentPage);
        $('#total-pages').text(postsData.totalPages);
        $('#posts-info').text(`显示 ${postsData.posts.length} 条记录，共 ${postsData.total} 条`);
        
        $('#prev-page').prop('disabled', postsData.currentPage <= 1);
        $('#next-page').prop('disabled', postsData.currentPage >= postsData.totalPages);
    }

    // 切换页面
    function changePage(direction) {
        const newPage = postsData.currentPage + direction;
        if (newPage >= 1 && newPage <= postsData.totalPages) {
            postsData.currentPage = newPage;
            loadPostsList();
        }
    }

    // 全选/取消全选
    function toggleSelectAll() {
        const isChecked = $('#select-all-posts').is(':checked');
        $('.post-checkbox').prop('checked', isChecked);
    }

    // 更新全选状态
    function updateSelectAllStatus() {
        const totalCheckboxes = $('.post-checkbox').length;
        const checkedCheckboxes = $('.post-checkbox:checked').length;
        
        if (checkedCheckboxes === 0) {
            // 没有选中任何项
            $('#select-all-posts').prop('checked', false).prop('indeterminate', false);
        } else if (checkedCheckboxes === totalCheckboxes) {
            // 全部选中
            $('#select-all-posts').prop('checked', true).prop('indeterminate', false);
        } else {
            // 部分选中，显示中间状态
            $('#select-all-posts').prop('checked', false).prop('indeterminate', true);
        }
    }

    // 批量生成
    function batchGenerate(type) {
        const selectedPosts = $('.post-checkbox:checked').map(function() {
            return $(this).val();
        }).get();
        
        if (selectedPosts.length === 0) {
            showNotice('请先选择要操作的文章', 'warning');
            return;
        }
        
        const typeName = type === 'summary' ? '摘要' : 'SEO';
        if (!confirm(`确定要为选中的 ${selectedPosts.length} 篇文章生成${typeName}吗？`)) {
            return;
        }
        
        // 批量生成逻辑（逐个处理，避免并发过多）
        batchProcessPosts(selectedPosts, type, 0);
    }

    // 批量处理文章
    function batchProcessPosts(postIds, type, index) {
        if (index >= postIds.length) {
            showNotice('批量生成完成！', 'success');
            loadPostsList(); // 刷新列表
            return;
        }
        
        const postId = postIds[index];
        const post = postsData.posts.find(p => p.id == postId);
        const typeName = type === 'summary' ? '摘要' : 'SEO';
        
        // 检查是否已生成，如果已生成则跳过
        let shouldSkip = false;
        if (type === 'summary' && post.ai_meta && post.ai_meta.summary) {
            shouldSkip = true;
        } else if (type === 'seo' && post.ai_meta && (post.ai_meta.post_description || post.ai_meta.post_keywords)) {
            shouldSkip = true;
        }
        
        if (shouldSkip) {
            showNotice(`文章《${post.post_title}》已有${typeName}，跳过 (${index + 1}/${postIds.length})`, 'info');
            setTimeout(() => batchProcessPosts(postIds, type, index + 1), 500);
            return;
        }
        
        showNotice(`正在为文章《${post.post_title}》生成${typeName}... (${index + 1}/${postIds.length})`, 'info');
        
        if (type === 'summary') {
            // 直接调用AJAX，不使用全局函数避免确认弹窗
            $.ajax({
                url: ai_summary.ajax_url,
                type: 'POST',
                data: {
                    action: ai_summary.ajax_name,
                    fun: 'generatePostSummary',
                    post_id: postId
                },
                success: function(response) {
                    if (response.code === 200) {
                        // 更新本地数据
                        if (post.ai_meta) {
                            post.ai_meta.summary = response.data;
                        } else {
                            post.ai_meta = { summary: response.data };
                        }
                        showNotice(`文章《${post.post_title}》摘要生成成功`, 'success');
                    } else {
                        showNotice(`文章《${post.post_title}》摘要生成失败：${response.msg || '未知错误'}`, 'error');
                    }
                    setTimeout(() => batchProcessPosts(postIds, type, index + 1), 1000);
                },
                error: function() {
                    showNotice(`文章《${post.post_title}》摘要生成失败`, 'error');
                    setTimeout(() => batchProcessPosts(postIds, type, index + 1), 1000);
                }
            });
        } else {
            // 直接调用AJAX，不使用全局函数避免确认弹窗
            $.ajax({
                url: ai_summary.ajax_url,
                type: 'POST',
                data: {
                    action: ai_summary.ajax_name,
                    fun: 'generatePostSeo',
                    post_id: postId
                },
                success: function(response) {
                    if (response.code === 200) {
                        // 更新本地数据
                        if (post.ai_meta) {
                            post.ai_meta.post_description = response.data.description;
                            post.ai_meta.post_keywords = Array.isArray(response.data.keywords) 
                                ? response.data.keywords.join(', ') 
                                : response.data.keywords;
                        } else {
                            post.ai_meta = { 
                                post_description: response.data.description,
                                post_keywords: Array.isArray(response.data.keywords) 
                                    ? response.data.keywords.join(', ') 
                                    : response.data.keywords
                            };
                        }
                        showNotice(`文章《${post.post_title}》SEO生成成功`, 'success');
                    } else {
                        showNotice(`文章《${post.post_title}》SEO生成失败：${response.msg || '未知错误'}`, 'error');
                    }
                    setTimeout(() => batchProcessPosts(postIds, type, index + 1), 1000);
                },
                error: function() {
                    showNotice(`文章《${post.post_title}》SEO生成失败`, 'error');
                    setTimeout(() => batchProcessPosts(postIds, type, index + 1), 1000);
                }
            });
        }
    }

    // 添加加载样式
    const style = `
        <style>
        .loading {
            position: relative;
            pointer-events: none;
            opacity: 0.6;
        }
        .provider-status.configured {
            background: #00b894;
            color: white;
        }
        .provider-status.connected {
            background: #00b894;
            color: white;
        }
        .provider-status.error {
            background: #ff7675;
            color: white;
        }
        
        /* 文章管理样式 */
        .posts-management {
            padding: 20px;
        }
        .posts-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .posts-header h3 {
            margin: 0;
            color: #2c3e50;
        }
        .posts-header p {
            margin: 5px 0 0 0;
            color: #7f8c8d;
            font-size: 14px;
        }
        .posts-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .btn-batch, .btn-refresh {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
        }
        .btn-batch {
            background: #3498db;
            color: white;
        }
        .btn-batch:hover {
            background: #2980b9;
        }
        .btn-refresh {
            background: #95a5a6;
            color: white;
        }
        .btn-refresh:hover {
            background: #7f8c8d;
        }
        .posts-table-container {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .posts-table {
            width: 100%;
            border-collapse: collapse;
        }
        .posts-table th {
            background: #f8f9fa;
            padding: 12px 8px;
            text-align: left;
            font-weight: 600;
            color: #2c3e50;
            border-bottom: 1px solid #dee2e6;
        }
        .posts-table td {
            padding: 12px 8px;
            border-bottom: 1px solid #f1f3f4;
            vertical-align: middle;
        }
        .posts-table tr:hover {
            background: #f8f9fa;
        }
        .post-title {
            color: #3498db;
            text-decoration: none;
            font-weight: 500;
        }
        .post-title:hover {
            text-decoration: underline;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }
        .status-success {
            background: #d4edda;
            color: #155724;
        }
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        .post-actions {
            white-space: nowrap;
        }
        .btn-small {
            padding: 4px 8px;
            margin: 0 2px;
            font-size: 12px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            background: #6c757d;
            color: white;
            text-decoration: none;
            display: inline-block;
        }
        .btn-small:hover {
            background: #5a6268;
            color: white;
            text-decoration: none;
        }
        .posts-pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        .pagination-controls {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .pagination-btn {
            padding: 8px 16px;
            border: 1px solid #dee2e6;
            background: white;
            border-radius: 4px;
            cursor: pointer;
        }
        .pagination-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .pagination-btn:not(:disabled):hover {
            background: #f8f9fa;
        }
                 .loading-row, .empty-row {
             text-align: center;
             color: #7f8c8d;
             font-style: italic;
         }
         .serial-number {
             text-align: center;
             font-weight: 500;
             color: #6c757d;
             width: 60px;
         }
         
         /* Checkbox样式优化 */
         .posts-table th:first-child,
         .posts-table td:first-child {
             width: 40px;
             text-align: center;
         }
         
         .posts-table input[type="checkbox"] {
             cursor: pointer;
             transform: scale(1.1);
         }
         
         .posts-table input[type="checkbox"]:indeterminate {
             background-color: #3498db;
             position: relative;
         }
         
         .posts-table input[type="checkbox"]:indeterminate::after {
             content: '';
             position: absolute;
             top: 50%;
             left: 50%;
             transform: translate(-50%, -50%);
             width: 8px;
             height: 2px;
             background: white;
         }
        </style>
    `;
    $('head').append(style);
    // 将生成函数绑定到window对象供HTML调用
    window.generatePostSummary = function(postId, callback) {
    const post = postsData.posts.find(p => p.id == postId);
    if (!post) {
        showNotice('文章信息不存在', 'error');
        return;
    }
    
    // 检查是否已生成
    if (post.ai_meta && post.ai_meta.summary) {
        if (!confirm(`文章《${post.post_title}》已生成摘要，确定要重新生成吗？`)) {
            return;
        }
    }
    
    const button = $(`button[onclick="generatePostSummary(${postId})"]`);
    const originalText = button.text();
    button.prop('disabled', true).text('生成中...');
    
    $.ajax({
        url: ai_summary.ajax_url,
        type: 'POST',
        data: {
            action: ai_summary.ajax_name,
            fun: 'generatePostSummary',
            post_id: postId
        },
        success: function(response) {
            if (typeof response === 'string') {
                try {
                    response = JSON.parse(response);
                } catch (e) {
                    showNotice('摘要生成失败：响应解析错误', 'error');
                    return;
                }
            }
            
            if (response.code === 200) {
                showNotice('摘要生成成功！', 'success');
                // 更新本地数据
                if (post.ai_meta) {
                    post.ai_meta.summary = response.data;
                } else {
                    post.ai_meta = { summary: response.data };
                }
                // 重新渲染列表
                renderPostsList();
                if (callback) callback();
            } else {
                showNotice('摘要生成失败：' + (response.msg || '未知错误'), 'error');
            }
        },
        error: function(xhr, status, error) {
            showNotice('摘要生成失败：' + error, 'error');
        },
        complete: function() {
            button.prop('disabled', false).text(originalText);
        }
    });
    };

    window.generatePostSeo = function(postId, callback) {
    const post = postsData.posts.find(p => p.id == postId);
    if (!post) {
        showNotice('文章信息不存在', 'error');
        return;
    }
    
    // 检查是否已生成
    if (post.ai_meta && (post.ai_meta.post_description || post.ai_meta.post_keywords)) {
        if (!confirm(`文章《${post.post_title}》已生成SEO信息，确定要重新生成吗？`)) {
            return;
        }
    }
    
    const button = $(`button[onclick="generatePostSeo(${postId})"]`);
    const originalText = button.text();
    button.prop('disabled', true).text('生成中...');
    
    $.ajax({
        url: ai_summary.ajax_url,
        type: 'POST',
        data: {
            action: ai_summary.ajax_name,
            fun: 'generatePostSeo',
            post_id: postId
        },
        success: function(response) {
            if (typeof response === 'string') {
                try {
                    response = JSON.parse(response);
                } catch (e) {
                    showNotice('SEO生成失败：响应解析错误', 'error');
                    return;
                }
            }
            
            if (response.code === 200) {
                showNotice('SEO生成成功！', 'success');
                // 更新本地数据
                if (post.ai_meta) {
                    post.ai_meta.post_description = response.data.description;
                    post.ai_meta.post_keywords = Array.isArray(response.data.keywords) 
                        ? response.data.keywords.join(', ') 
                        : response.data.keywords;
                } else {
                    post.ai_meta = { 
                        post_description: response.data.description,
                        post_keywords: Array.isArray(response.data.keywords) 
                            ? response.data.keywords.join(', ') 
                            : response.data.keywords
                    };
                }
                // 重新渲染列表
                renderPostsList();
                if (callback) callback();
            } else {
                showNotice('SEO生成失败：' + (response.msg || '未知错误'), 'error');
            }
        },
        error: function(xhr, status, error) {
            showNotice('SEO生成失败：' + error, 'error');
        },
        complete: function() {
            button.prop('disabled', false).text(originalText);
        }
    });
    };

    // 注意：文章管理标签的自动加载逻辑已移到initTabs函数中 

});