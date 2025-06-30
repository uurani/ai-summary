<div class="ai-summary-admin-container">
    <div class="ai-summary-header">
        <div class="ai-summary-title">
            <h1>AI Summary - 智能摘要生成</h1>
            <p>为您的WordPress网站提供多种AI服务支持，轻松生成高质量内容摘要</p>
        </div>
        <div class="ai-summary-version">
            <span class="version-badge" id="header-version">加载中...</span>
        </div>
    </div>

    <div class="ai-summary-tabs">
        <nav class="tab-nav">
            <button class="tab-button active" data-tab="basic">基础设置</button>
            <button class="tab-button" data-tab="ai-config">AI配置</button>
            <button class="tab-button" data-tab="summary">摘要设置</button>
            <button class="tab-button" data-tab="seo">SEO设置</button>
            <button class="tab-button" data-tab="posts">文章管理</button>
            <button class="tab-button" data-tab="update">插件更新</button>
            <button class="tab-button" data-tab="about">关于</button>
        </nav>

        <!-- 基础设置 -->
        <div class="tab-content active" id="basic-tab">
            <div class="settings-grid">
                <div class="setting-card">
                    <div class="card-header">
                        <h3>启用AI摘要</h3>
                        <label class="toggle-switch">
                            <input type="checkbox" id="open_ai_summary">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <p class="card-desc">启用后将在文章页面自动显示AI生成的摘要</p>
                </div>

                <div class="setting-card">
                    <div class="card-header">
                        <h3>摘要动画效果</h3>
                        <label class="toggle-switch">
                            <input type="checkbox" id="ai_summary_animation">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <p class="card-desc">为摘要添加打字机动画效果</p>
                </div>
            </div>
        </div>

        <!-- AI配置 -->
        <div class="tab-content" id="ai-config-tab">
            <div class="ai-providers">
                <!-- 文心一言配置 -->
                <div class="provider-card collapsed">
                    <div class="provider-header">
                        <img src="<?php echo ai_summary\Config::$img_url; ?>/wenxin.svg" alt="文心一言" class="provider-icon">
                        <h3>文心一言</h3>
                        <span class="provider-status" id="wenxin-status">未配置</span>
                        <div class="provider-toggle">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M7 10l5 5 5-5z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="provider-config">
                        <div class="input-group">
                            <label>API Key</label>
                            <input type="text" id="wenxin_api_key" placeholder="请输入百度千帆API Key">
                            <p class="input-desc">在百度智能云千帆大模型平台获取</p>
                        </div>
                        <div class="input-group">
                            <label>模型选择</label>
                            <select id="wenxin_model">
                                <option value="ernie-3.5-8k" selected>ERNIE-3.5-8K - 默认推荐</option>
                                <option value="ernie-4.0-8k">ERNIE-4.0-8K - 最新4.0版本</option>
                                <option value="ernie-4.0-turbo-8k">ERNIE-4.0-Turbo-8K - 4.0涡轮版</option>
                                <option value="ernie-3.5-128k">ERNIE-3.5-128K - 长上下文</option>
                                <option value="ernie-speed-8k">ERNIE-Speed-8K - 速度优化</option>
                                <option value="ernie-speed-128k">ERNIE-Speed-128K - 速度+长上下文</option>
                            </select>
                            <p class="input-desc">使用最新千帆v2统一API，兼容OpenAI格式</p>
                        </div>
                        
                        <button class="btn-test">测试连接</button>
                    </div>
                </div>

                                <!-- ChatGPT配置 -->
                <div class="provider-card collapsed">
                    <div class="provider-header">
                        <img src="<?php echo ai_summary\Config::$img_url; ?>/openai.svg" alt="ChatGPT" class="provider-icon">
                        <h3>ChatGPT</h3>
                        <span class="provider-status" id="chatgpt-status">未配置</span>
                        <div class="provider-toggle">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M7 10l5 5 5-5z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="provider-config">
                        <div class="input-group">
                            <label>API Key</label>
                            <input type="text" id="chatgpt_api_key" placeholder="请输入OpenAI API Key">
                        </div>
                        <div class="input-group">
                            <label>API地址 (可选)</label>
                            <input type="text" id="chatgpt_base_url" placeholder="默认: https://api.openai.com">
                        </div>
                        <div class="input-group">
                            <label>模型选择</label>
                            <select id="chatgpt_model">
                                <option value="gpt-3.5-turbo">GPT-3.5 Turbo - 速度快，成本低</option>
                                <option value="gpt-3.5-turbo-16k">GPT-3.5 Turbo 16K - 支持更长上下文</option>
                                <option value="gpt-4">GPT-4 - 最高质量</option>
                                <option value="gpt-4-turbo-preview">GPT-4 Turbo - 最新模型</option>
                            </select>
                        </div>
                        <button class="btn-test">测试连接</button>
                    </div>
                </div>

                <!-- Gemini配置 -->
                <div class="provider-card collapsed">
                    <div class="provider-header">
                    <img src="<?php echo ai_summary\Config::$img_url; ?>/gemini.svg" alt="Gemini" class="provider-icon">
                        <h3>Google Gemini</h3>
                        <span class="provider-status" id="gemini-status">未配置</span>
                        <div class="provider-toggle">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M7 10l5 5 5-5z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="provider-config">
                        <div class="input-group">
                            <label>API Key</label>
                            <input type="text" id="gemini_api_key" placeholder="请输入Google Gemini API Key">
                        </div>
                        <div class="input-group">
                            <label>模型选择</label>
                            <select id="gemini_model">
                                <option value="gemini-2.0-flash">Gemini 2.0 Flash - 最新2.0版本，速度最快</option>
                                <option value="gemini-1.5-pro">Gemini 1.5 Pro - 1.5版本，功能全面</option>
                                <option value="gemini-pro">Gemini Pro - 经典版本</option>
                                <option value="gemini-pro-vision">Gemini Pro Vision - 支持图像输入</option>
                            </select>
                        </div>
                        <button class="btn-test">测试连接</button>
                    </div>
                </div>

                <!-- 豆包配置 -->
                <div class="provider-card collapsed">
                    <div class="provider-header">
                        <img src="<?php echo ai_summary\Config::$img_url; ?>/doubao.svg" alt="豆包" class="provider-icon">
                        <h3>豆包</h3>
                        <span class="provider-status" id="doubao-status">未配置</span>
                        <div class="provider-toggle">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M7 10l5 5 5-5z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="provider-config">
                        <div class="input-group">
                            <label>API Key</label>
                            <input type="text" id="doubao_api_key" placeholder="请输入豆包API Key">
                            <p class="input-desc">在火山方舟平台获取</p>
                        </div>
                        <div class="input-group">
                            <label>API地址 (可选)</label>
                            <input type="text" id="doubao_base_url" placeholder="默认: https://ark.cn-beijing.volces.com/api/v3">
                        </div>
                        <div class="input-group">
                            <label>模型选择</label>
                            <select id="doubao_model">
                                <option value="doubao-lite-4k-character-240828">doubao-lite-4k - 轻量版，4K上下文，角色版</option>
                                <option value="doubao-lite-32k-240828" selected>doubao-lite-32k - 轻量版，32K上下文</option>
                                <option value="doubao-pro-4k-240515">doubao-pro-4k - 专业版，4K上下文</option>
                                <option value="doubao-pro-32k-241215">doubao-pro-32k - 专业版，32K上下文</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label>自定义模型名称 (可选)</label>
                            <input type="text" id="doubao_custom_model" placeholder="输入自定义模型名称，优先于上方选择">
                            <p class="input-desc">如果填写自定义模型名称，将优先使用此名称而非上方选择的模型</p>
                        </div>
                        <button class="btn-test">测试连接</button>
                    </div>
                </div>

                <!-- 通义千问配置 -->
                <div class="provider-card collapsed">
                    <div class="provider-header">
                        <img src="<?php echo ai_summary\Config::$img_url; ?>/qwen.svg" alt="通义千问" class="provider-icon">
                        <h3>通义千问</h3>
                        <span class="provider-status" id="tongyi-status">未配置</span>
                        <div class="provider-toggle">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M7 10l5 5 5-5z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="provider-config">
                        <div class="input-group">
                            <label>API Key</label>
                            <input type="text" id="tongyi_api_key" placeholder="请输入阿里云百炼API Key">
                            <p class="input-desc">在阿里云百炼平台获取</p>
                        </div>
                        <div class="input-group">
                            <label>模型选择</label>
                            <select id="tongyi_model">
                                <option value="qwen-long" selected>qwen-long - 长文本版</option>
                                <option value="qwen-turbo">qwen-turbo - 极速版</option>
                                <option value="qwen-plus">qwen-plus - 通用增强版</option>
                                <option value="qwen-max">qwen-max - 旗舰版</option>
                            </select>
                        </div>
                        <button class="btn-test">测试连接</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 摘要设置 -->
        <div class="tab-content" id="summary-tab">
            <div class="settings-grid">
                <div class="setting-card">
                    <div class="card-header">
                        <h3>AI服务选择</h3>
                    </div>
                    <div class="radio-group">
                        <label class="radio-option">
                            <input type="radio" name="ai_summary_path" value="wenxin">
                            <span class="radio-custom"></span>
                            文心一言
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="ai_summary_path" value="chatgpt">
                            <span class="radio-custom"></span>
                            ChatGPT
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="ai_summary_path" value="gemini">
                            <span class="radio-custom"></span>
                            Gemini
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="ai_summary_path" value="doubao">
                            <span class="radio-custom"></span>
                            豆包
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="ai_summary_path" value="tongyi">
                            <span class="radio-custom"></span>
                            通义千问
                        </label>
                    </div>
                </div>

                <div class="setting-card">
                    <div class="card-header">
                        <h3>摘要字数限制</h3>
                    </div>
                    <div class="input-group">
                        <input type="number" id="ai_summary_word_number" min="50" max="500" placeholder="200">
                        <span class="input-suffix">字</span>
                    </div>
                    <p class="card-desc">建议设置在100-300字之间，过短可能信息不全，过长影响阅读体验</p>
                </div>
            </div>
        </div>

        <!-- SEO设置 -->
        <div class="tab-content" id="seo-tab">
            <div class="settings-grid">
                <div class="setting-card">
                    <div class="card-header">
                        <h3>启用AI SEO</h3>
                        <label class="toggle-switch">
                            <input type="checkbox" id="ai_seo_open">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <p class="card-desc">自动为文章生成SEO标题、描述和关键词</p>
                </div>

                <div class="setting-card">
                    <div class="card-header">
                        <h3>SEO AI服务</h3>
                    </div>
                    <div class="radio-group">
                        <label class="radio-option">
                            <input type="radio" name="ai_seo_path" value="wenxin">
                            <span class="radio-custom"></span>
                            文心一言
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="ai_seo_path" value="chatgpt">
                            <span class="radio-custom"></span>
                            ChatGPT
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="ai_seo_path" value="gemini">
                            <span class="radio-custom"></span>
                            Gemini
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="ai_seo_path" value="doubao">
                            <span class="radio-custom"></span>
                            豆包
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="ai_seo_path" value="tongyi">
                            <span class="radio-custom"></span>
                            通义千问
                        </label>
                    </div>
                </div>

                <div class="setting-card">
                    <div class="card-header">
                        <h3>描述长度限制</h3>
                    </div>
                    <div class="input-group">
                        <input type="number" id="ai_seo_description_length" min="120" max="200" placeholder="150">
                        <span class="input-suffix">字符</span>
                    </div>
                </div>

                <div class="setting-card">
                    <div class="card-header">
                        <h3>关键词数量</h3>
                    </div>
                    <div class="input-group">
                        <input type="number" id="ai_seo_keywords_length" min="5" max="15" placeholder="10">
                        <span class="input-suffix">个</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 插件更新 -->
        <div class="tab-content" id="update-tab">
            <div class="update-management">
                <div class="update-header">
                    <h3>插件更新管理</h3>
                    <p>检查并更新到最新版本，获得更多功能和修复。</p>
                </div>
                
                <div class="settings-grid">
                    <!-- 更新检查 -->
                    <div class="setting-card">
                        <div class="card-header">
                            <h3>版本检查</h3>
                        </div>
                        <div class="update-info" id="update-info">
                            <div class="update-status">
                                <div class="version-info">
                                    <span class="current-version">当前版本：<strong id="current-version">加载中...</strong></span>
                                    <span class="latest-version" id="latest-version-container" style="display: none;">
                                        最新版本：<strong id="latest-version">-</strong>
                                    </span>
                                </div>
                                <div class="update-status-text" id="update-status-text">
                                    点击下方按钮检查更新
                                </div>
                            </div>
                            
                            <div class="update-actions">
                                <button class="btn-update-check" id="check-update-btn">
                                    <span class="btn-text">检查更新</span>
                                    <span class="btn-loading" style="display: none;">检查中...</span>
                                </button>
                                <button class="btn-update-start" id="start-update-btn" style="display: none;">
                                    <span class="btn-text">立即更新</span>
                                    <span class="btn-loading" style="display: none;">更新中...</span>
                                </button>
                            </div>
                            
                            <div class="update-progress" id="update-progress" style="display: none;">
                                <div class="progress-bar">
                                    <div class="progress-fill" id="progress-fill"></div>
                                </div>
                                <div class="progress-text" id="progress-text">准备中...</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 自动更新设置 -->
                    <div class="setting-card">
                        <div class="card-header">
                            <h3>自动更新设置</h3>
                        </div>
                        <div class="setting-row">
                            <label class="toggle-switch">
                                <input type="checkbox" id="auto_check_update">
                                <span class="toggle-slider"></span>
                            </label>
                            <div class="setting-desc">
                                <h4>启用自动检查更新</h4>
                                <p>每12小时自动检查一次更新，有新版本时在管理页面显示提醒</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 更新说明 -->
                    <div class="setting-card update-notes">
                        <div class="card-header">
                            <h3>更新说明</h3>
                        </div>
                        <div class="update-features">
                            <div class="feature-item">
                                <span class="feature-icon">♻️</span>
                                <div>
                                    <h4>自动更新</h4>
                                    <p>一键更新到最新版本，无需手动下载</p>
                                </div>
                            </div>
                            <div class="feature-item">
                                <span class="feature-icon">🔒</span>
                                <div>
                                    <h4>安全检查</h4>
                                    <p>文件完整性验证，确保更新安全可靠</p>
                                </div>
                            </div>
                            <div class="feature-item">
                                <span class="feature-icon">🕧</span>
                                <div>
                                    <h4>定时检查</h4>
                                    <p>自动检查新版本，及时获得最新功能</p>
                                </div>
                            </div>
                        </div>
                        <div class="update-warning">
                            <p><strong>注意事项：</strong></p>
                            <ul>
                                <li>更新前请确保网络连接稳定</li>
                                <li>更新过程中请勿关闭页面</li>
                                <li>建议在网站维护时间进行更新</li>
                                <li>如遇问题可访问官网获取帮助</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 文章管理 -->
        <div class="tab-content" id="posts-tab">
            <div class="posts-management">
                <div class="posts-header">
                    <h3>文章管理</h3>
                    <p>为您的文章生成AI摘要和SEO信息</p>
                    <div class="posts-actions">
                        <button class="btn-batch" id="batch-summary">批量生成摘要</button>
                        <button class="btn-batch" id="batch-seo">批量生成SEO</button>
                        <button class="btn-refresh" id="refresh-posts">刷新列表</button>
                    </div>
                </div>
                
                <div class="posts-table-container">
                    <table class="posts-table">
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" id="select-all-posts">
                                </th>
                                <th>序号</th>
                                <th>文章标题</th>
                                <th>发布日期</th>
                                <th>AI摘要</th>
                                <th>AI SEO</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody id="posts-list">
                            <tr>
                                <td colspan="7" class="loading-row">加载中...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="posts-pagination">
                    <div class="pagination-info">
                        <span id="posts-info">显示 0 条记录</span>
                    </div>
                    <div class="pagination-controls">
                        <button class="pagination-btn" id="prev-page" disabled>上一页</button>
                        <span class="pagination-current">
                            第 <span id="current-page">1</span> 页，共 <span id="total-pages">1</span> 页
                        </span>
                        <button class="pagination-btn" id="next-page" disabled>下一页</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 关于 -->
        <div class="tab-content" id="about-tab">
            <div class="about-content">
                <div class="about-card">
                    <h3>AI Summary</h3>
                    <p>一个功能强大的WordPress AI摘要插件，支持多种主流AI服务。</p>
                    
                    <div class="feature-list">
                        <div class="feature-item">
                            <span class="feature-icon">🤖</span>
                            <div>
                                <h4>多AI服务支持</h4>
                                <p>支持文心一言、ChatGPT、Google Gemini</p>
                            </div>
                        </div>
                        <div class="feature-item">
                            <span class="feature-icon">✨</span>
                            <div>
                                <h4>智能摘要生成</h4>
                                <p>自动为文章生成高质量摘要</p>
                            </div>
                        </div>
                        <div class="feature-item">
                            <span class="feature-icon">🎨</span>
                            <div>
                                <h4>现代化界面</h4>
                                <p>简洁美观的管理界面，易于操作</p>
                            </div>
                        </div>
                        <div class="feature-item">
                            <span class="feature-icon">🚀</span>
                            <div>
                                <h4>SEO优化</h4>
                                <p>自动生成SEO友好的标题和描述</p>
                            </div>
                        </div>
                    </div>



                    <div class="plugin-info">
                        <p><strong>版本:</strong> <span id="about-current-version">加载中...</span></p>
                        <p><strong>作者:</strong> muxui</p>
                        <p><strong>网站:</strong> <a href="https://muxui.com" target="_blank">muxui.com</a></p>
                        <p><strong>更新:</strong> 请前往"插件更新"页面检查最新版本</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="ai-summary-footer">
        <button class="btn-primary">保存设置</button>
        <button class="btn-secondary">重置设置</button>
    </div>
</div>