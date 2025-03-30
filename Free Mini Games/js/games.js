/**
 * Free Mini Games - 游戏分类页面JavaScript文件
 * 处理游戏筛选和搜索功能
 */

// 等待DOM加载完成
document.addEventListener('DOMContentLoaded', function() {
    // 游戏iframe处理
    handleGameIframe();
    
    // 分类筛选功能
    handleCategoryFilters();
    
    // 游戏搜索功能
    handleGameSearch();
});

/**
 * 处理游戏iframe加载和错误情况
 */
function handleGameIframe() {
    // 查找游戏iframe
    const gameIframe = document.querySelector('.game-iframe, iframe');
    if (!gameIframe) return;
    
    // 创建加载指示器
    const loadingIndicator = document.createElement('div');
    loadingIndicator.className = 'game-loading';
    loadingIndicator.innerHTML = `
        <div class="loading-spinner"></div>
        <p>游戏加载中...</p>
    `;
    
    // 创建错误提示
    const errorElement = document.createElement('div');
    errorElement.className = 'game-error';
    errorElement.innerHTML = `
        <div class="error-message">
            <h3>游戏加载失败</h3>
            <p>可能是由于以下原因：</p>
            <ul>
                <li>游戏源不允许在iframe中嵌入</li>
                <li>网络连接问题</li>
                <li>游戏源已更改或不可用</li>
            </ul>
            <p>你可以尝试：</p>
            <button class="reload-btn">重新加载</button>
            <p>或 <a href="${gameIframe.src}" target="_blank">直接访问游戏</a></p>
        </div>
    `;
    
    // 获取iframe的父容器
    const iframeContainer = gameIframe.parentElement;
    if (!iframeContainer) return;
    
    // 设置父容器样式
    if (window.getComputedStyle(iframeContainer).position === 'static') {
        iframeContainer.style.position = 'relative';
    }
    
    // 添加加载指示器和错误元素到父容器
    iframeContainer.appendChild(loadingIndicator);
    iframeContainer.appendChild(errorElement);
    
    // 设置元素样式
    const style = document.createElement('style');
    style.textContent = `
        .game-loading, .game-error {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: rgba(0, 0, 0, 0.8);
            color: white;
            z-index: 10;
        }
        
        .game-error {
            display: none;
            text-align: center;
        }
        
        .loading-spinner {
            border: 5px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top: 5px solid #fff;
            width: 40px;
            height: 40px;
            margin-bottom: 15px;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .error-message {
            max-width: 80%;
            padding: 20px;
            background-color: #34495e;
            border-radius: 8px;
        }
        
        .error-message h3 {
            color: #e74c3c;
            margin-bottom: 15px;
        }
        
        .error-message ul {
            text-align: left;
            margin: 15px 0;
            padding-left: 20px;
        }
        
        .error-message a {
            color: #3498db;
            text-decoration: underline;
        }
        
        .reload-btn {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            margin: 10px 0;
        }
        
        .reload-btn:hover {
            background-color: #2980b9;
        }
    `;
    document.head.appendChild(style);
    
    // 设置加载超时
    let isIframeLoaded = false;
    const loadTimeout = setTimeout(function() {
        if (!isIframeLoaded) {
            // 隐藏加载指示器，显示错误信息
            loadingIndicator.style.display = 'none';
            errorElement.style.display = 'flex';
        }
    }, 15000); // 15秒超时
    
    // 监听iframe加载事件
    gameIframe.addEventListener('load', function() {
        isIframeLoaded = true;
        clearTimeout(loadTimeout);
        loadingIndicator.style.display = 'none';
        
        // 尝试访问iframe内容来检查是否真正加载成功
        try {
            // 尝试访问iframe内容，这可能会因跨域限制而失败
            const iframeContent = gameIframe.contentWindow.document;
            // 如果没有抛出错误，则加载成功
        } catch (e) {
            // 如果无法访问iframe内容，可能是跨域问题，但不一定意味着加载失败
            // 我们仍然认为加载成功，因为某些游戏源正常工作但不允许JS访问其内容
        }
    });
    
    // 监听iframe错误事件
    gameIframe.addEventListener('error', function() {
        isIframeLoaded = false;
        clearTimeout(loadTimeout);
        loadingIndicator.style.display = 'none';
        errorElement.style.display = 'flex';
    });
    
    // 重新加载按钮功能
    const reloadBtn = errorElement.querySelector('.reload-btn');
    if (reloadBtn) {
        reloadBtn.addEventListener('click', function() {
            // 重新加载iframe
            isIframeLoaded = false;
            errorElement.style.display = 'none';
            loadingIndicator.style.display = 'flex';
            
            // 重新设置src以刷新iframe
            const currentSrc = gameIframe.src;
            gameIframe.src = 'about:blank';
            setTimeout(function() {
                gameIframe.src = currentSrc;
            }, 100);
            
            // 设置新的超时
            clearTimeout(loadTimeout);
            const newLoadTimeout = setTimeout(function() {
                if (!isIframeLoaded) {
                    loadingIndicator.style.display = 'none';
                    errorElement.style.display = 'flex';
                }
            }, 15000);
        });
    }
}

/**
 * 处理游戏分类筛选功能
 */
function handleCategoryFilters() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    const gameCards = document.querySelectorAll('.game-card');
    const categoryTitle = document.querySelector('.category-title');
    
    if (!filterButtons.length || !gameCards.length) return;
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // 移除所有按钮的active类
            filterButtons.forEach(btn => btn.classList.remove('active'));
            // 给当前按钮添加active类
            this.classList.add('active');
            
            const category = this.getAttribute('data-category');
            
            // 更新标题
            if (categoryTitle) {
                if (category === 'all') {
                    categoryTitle.textContent = '所有游戏';
                } else {
                    // 获取按钮文本作为分类名称
                    categoryTitle.textContent = this.textContent;
                }
            }
            
            // 筛选游戏卡片
            gameCards.forEach(card => {
                if (category === 'all' || card.getAttribute('data-category') === category) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
    
    // 从URL获取分类参数并自动应用筛选
    const urlParams = new URLSearchParams(window.location.search);
    const categoryParam = urlParams.get('category');
    
    if (categoryParam) {
        const matchingButton = document.querySelector(`.filter-btn[data-category="${categoryParam}"]`);
        if (matchingButton) {
            matchingButton.click();
        }
    }
}

/**
 * 处理游戏搜索功能
 */
function handleGameSearch() {
    const searchInput = document.getElementById('game-search');
    const searchBtn = document.getElementById('search-btn');
    const gameCards = document.querySelectorAll('.game-card');
    
    if (!searchInput || !gameCards.length) return;
    
    const performSearch = () => {
        const searchTerm = searchInput.value.toLowerCase().trim();
        
        gameCards.forEach(card => {
            const title = card.querySelector('h3').textContent.toLowerCase();
            const description = card.querySelector('p') ? card.querySelector('p').textContent.toLowerCase() : '';
            
            if (title.includes(searchTerm) || description.includes(searchTerm)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    };
    
    // 搜索按钮点击事件
    if (searchBtn) {
        searchBtn.addEventListener('click', performSearch);
    }
    
    // 输入框Enter键事件
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            performSearch();
        }
    });
}

// 添加CSS动画样式
const style = document.createElement('style');
style.textContent = `
    .game-card {
        opacity: 0;
        transform: translateY(20px);
        transition: opacity 0.3s ease, transform 0.3s ease;
    }
    
    .game-card.visible {
        opacity: 1;
        transform: translateY(0);
    }
`;
document.head.appendChild(style);

// 初始化时显示所有游戏卡片
gameCards.forEach(card => {
    card.classList.add('visible');
}); 