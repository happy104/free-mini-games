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

/**
 * Free Mini Games - Game Page JavaScript
 * Functionality for individual game pages
 * 2024-04-02
 */

// 等待页面加载完成
document.addEventListener('DOMContentLoaded', function() {
    initGamePage();
});

// 游戏页面初始化
function initGamePage() {
    setupFullscreen();
    loadRelatedGames();
    setupGameLoading();
    trackGameplay();
}

// 设置全屏功能
function setupFullscreen() {
    const fullscreenBtn = document.getElementById('fullscreen-btn');
    if (!fullscreenBtn) return;
    
    fullscreenBtn.addEventListener('click', function() {
        const iframe = document.querySelector('.game-frame iframe');
        if (!iframe) return;
        
        if (iframe.requestFullscreen) {
            iframe.requestFullscreen();
        } else if (iframe.mozRequestFullScreen) { // Firefox
            iframe.mozRequestFullScreen();
        } else if (iframe.webkitRequestFullscreen) { // Chrome, Safari, Opera
            iframe.webkitRequestFullscreen();
        } else if (iframe.msRequestFullscreen) { // IE/Edge
            iframe.msRequestFullscreen();
        }
    });
}

// 加载相关游戏
function loadRelatedGames() {
    const similarGamesContainer = document.querySelector('.similar-games .games-grid');
    if (!similarGamesContainer) return;
    
    // 获取当前游戏类别
    const currentCategory = document.querySelector('.game-category').textContent.trim();
    
    // 这里应该有一个AJAX调用来获取相关游戏
    // 为了简单起见，我们模拟这个过程
    
    // 清除加载状态
    similarGamesContainer.innerHTML = '<div class="loading-message">Loading similar games...</div>';
    
    // 模拟加载延迟
    setTimeout(function() {
        // 实际项目中这里应该是从服务器获取数据
        similarGamesContainer.innerHTML = `
            <div class="game-card">
                <a href="../games/sandbox-city.html">
                    <div class="game-thumbnail">
                        <img src="../images/games/1743317823377.jpg" alt="Sandbox City">
                        <div class="game-overlay">
                            <div class="play-button">
                                <i class="fas fa-play"></i>
                            </div>
                        </div>
                    </div>
                    <div class="game-info">
                        <h3>Sandbox City</h3>
                        <div class="game-meta">
                            <span class="game-category">Action</span>
                            <span class="game-rating"><i class="fas fa-star"></i> 4.8</span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="game-card">
                <a href="../games/squid-game-online-new.html">
                    <div class="game-thumbnail">
                        <img src="../images/games/1743317727556.jpg" alt="Squid Game Online">
                        <div class="game-overlay">
                            <div class="play-button">
                                <i class="fas fa-play"></i>
                            </div>
                        </div>
                    </div>
                    <div class="game-info">
                        <h3>Squid Game Online</h3>
                        <div class="game-meta">
                            <span class="game-category">Action</span>
                            <span class="game-rating"><i class="fas fa-star"></i> 5</span>
                        </div>
                    </div>
                </a>
            </div>
        `;
    }, 1000);
    
    // 加载侧边栏热门游戏
    loadTopGames(currentCategory);
}

// 加载该类别热门游戏
function loadTopGames(category) {
    const topGamesContainer = document.querySelector('.top-games ul');
    if (!topGamesContainer) return;
    
    // 清除加载状态
    topGamesContainer.innerHTML = '<li>Loading top games...</li>';
    
    // 模拟加载延迟
    setTimeout(function() {
        // 实际项目中这里应该是从服务器获取数据
        topGamesContainer.innerHTML = `
            <li>
                <a href="../games/sandbox-city.html">
                    <img src="../images/games/1743317823377.jpg" alt="Sandbox City">
                    <div>
                        <h4>Sandbox City</h4>
                        <span class="game-rating"><i class="fas fa-star"></i> 4.8</span>
                    </div>
                </a>
            </li>
            <li>
                <a href="../games/squid-game-online-new.html">
                    <img src="../images/games/1743317727556.jpg" alt="Squid Game Online">
                    <div>
                        <h4>Squid Game Online</h4>
                        <span class="game-rating"><i class="fas fa-star"></i> 5</span>
                    </div>
                </a>
            </li>
        `;
    }, 800);
}

// 设置游戏加载
function setupGameLoading() {
    const iframe = document.querySelector('.game-frame iframe');
    const loadingIndicator = document.querySelector('.loading-indicator');
    
    if (!iframe || !loadingIndicator) return;
    
    // 强制隐藏加载指示器
    function hideLoader() {
        // 使用多种方式确保加载指示器被隐藏
        loadingIndicator.style.display = 'none';
        loadingIndicator.style.opacity = '0';
        loadingIndicator.style.visibility = 'hidden';
        loadingIndicator.style.zIndex = '-1';
        
        // 为游戏iframe添加可见类
        iframe.classList.add('game-loaded');
        
        // 添加自定义事件表示游戏已加载
        document.dispatchEvent(new CustomEvent('gameLoaded'));
    }
    
    // 监听iframe加载完成
    iframe.addEventListener('load', function() {
        // 延迟1秒后隐藏加载器，确保游戏内容已渲染
        setTimeout(hideLoader, 1000);
    });
    
    // 监听iframe错误
    iframe.addEventListener('error', function() {
        console.log('游戏iframe加载失败');
        // 仍然隐藏加载器，但显示一个错误消息
        hideLoader();
        
        // 创建错误消息元素
        const errorMsg = document.createElement('div');
        errorMsg.className = 'game-load-error';
        errorMsg.innerHTML = `
            <div class="error-content">
                <i class="fas fa-exclamation-triangle"></i>
                <h3>游戏加载出现问题</h3>
                <p>请尝试刷新页面或稍后再试</p>
                <button onclick="location.reload()">刷新页面</button>
            </div>
        `;
        
        // 添加到游戏框架
        iframe.parentNode.appendChild(errorMsg);
    });
    
    // 确保加载指示器最终会被隐藏，无论什么情况
    // 减少超时时间到5秒
    setTimeout(hideLoader, 5000);
    
    // 监听window加载完成事件
    window.addEventListener('load', function() {
        // 延迟3秒后再次检查并隐藏加载器
        setTimeout(hideLoader, 3000);
    });
    
    // 确保页面可见时加载器被隐藏
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            setTimeout(hideLoader, 1000);
        }
    });
}

// 游戏游玩次数统计
function trackGameplay() {
    // 实际项目中这里应该发送请求到服务器记录游戏播放次数
    console.log('Game play tracked');
}

// 添加css-loaded class到body
window.addEventListener('load', function() {
    document.body.classList.add('css-loaded');
});

// 游戏数据
const games = [
    {
        title: "Defender Idle 2",
        slug: "defender-idle-2",
        categories: ["Action", "Strategy", "Idle"],
        description: "An endless idle defense game where you place turrets and upgrade your defenses."
    },
    {
        title: "Traffic Rider",
        slug: "traffic-rider",
        categories: ["Racing", "Action"],
        description: "Race through traffic in this fast-paced motorcycle racing game."
    },
    {
        title: "Tetris Classic",
        slug: "tetris-classic",
        categories: ["Puzzle", "Classic"],
        description: "The classic block-stacking puzzle game that never gets old."
    },
    {
        title: "Sandbox City",
        slug: "sandbox-city",
        categories: ["Action", "Adventure", "Simulation"],
        description: "Build and explore your own city in this open-world sandbox game."
    },
    {
        title: "Squid Game Challenge",
        slug: "squid-game-challenge",
        categories: ["Action", "Adventure"],
        description: "Based on the popular TV show, complete the challenges to win the prize."
    },
    {
        title: "Zombie Survival",
        slug: "zombie-survival",
        categories: ["Action", "Horror", "Survival"],
        description: "Survive the zombie apocalypse in this intense survival game."
    },
    {
        title: "Bubble Shooter",
        slug: "bubble-shooter",
        categories: ["Puzzle", "Casual"],
        description: "Match and pop colorful bubbles in this addictive puzzle game."
    },
    {
        title: "Basketball Pro",
        slug: "basketball-pro",
        categories: ["Sports", "Action"],
        description: "Show your basketball skills and become the champion."
    },
    {
        title: "Tank Battle",
        slug: "tank-battle",
        categories: ["Action", "Strategy"],
        description: "Command your tank and destroy enemy forces in this tactical battle game."
    },
    {
        title: "Fishing Master",
        slug: "fishing-master",
        categories: ["Casual", "Simulation"],
        description: "Relax and catch various fish species in beautiful locations."
    }
];

// 在详情页加载相似游戏
document.addEventListener('DOMContentLoaded', function() {
    // 检查是否在游戏详情页
    const similarGamesContainer = document.querySelector('.similar-games .games-grid');
    if (!similarGamesContainer) return;
    
    // 获取当前游戏标题
    const currentGameTitle = document.querySelector('.game-header h1').textContent.trim();
    
    // 获取当前游戏类别
    const gameCategory = document.querySelector('.game-category').textContent.replace(/^\s*\S+\s+/, '').trim();
    
    // 过滤出同类别但不是当前游戏的游戏
    let similarGames = games.filter(game => {
        return game.categories.includes(gameCategory) && game.title !== currentGameTitle;
    });
    
    // 如果同类别游戏不足6个，添加其他类别的随机游戏补足
    if (similarGames.length < 6) {
        const otherGames = games.filter(game => {
            return !game.categories.includes(gameCategory) && game.title !== currentGameTitle;
        });
        
        // 随机打乱其他游戏
        otherGames.sort(() => Math.random() - 0.5);
        
        // 补足到6个
        similarGames = [...similarGames, ...otherGames.slice(0, 6 - similarGames.length)];
    }
    
    // 限制为最多6个，并随机打乱顺序
    similarGames = similarGames.slice(0, 6).sort(() => Math.random() - 0.5);
    
    // 生成HTML并添加到容器
    if (similarGames.length > 0) {
        const similarGamesHTML = similarGames.map(game => {
            return `
                <div class="game-card">
                    <div class="game-card-inner">
                        <a href="${game.slug}.html" class="game-link">
                            <div class="game-thumbnail">
                                <img src="../img/game-placeholder.jpg" alt="${game.title}" onerror="this.src='../img/game-placeholder.jpg'">
                                <div class="game-overlay">
                                    <span class="play-now">Play Now</span>
                                </div>
                            </div>
                            <div class="game-info">
                                <h3 class="game-title">${game.title}</h3>
                                <div class="game-meta">
                                    <span class="game-category">${game.categories[0]}</span>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            `;
        }).join('');
        
        similarGamesContainer.innerHTML = similarGamesHTML;
    } else {
        // 如果没有相似游戏，隐藏整个相似游戏部分
        const similarGamesSection = document.querySelector('.similar-games');
        if (similarGamesSection) {
            similarGamesSection.style.display = 'none';
        }
    }
}); 