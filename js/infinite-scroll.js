/**
 * Infinite Scroll for Games
 * Loads games with infinite scroll functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    // 日志系统用于调试
    const logger = {
        debug: (message) => {
            if (window.debugMode) {
                console.log(`[DEBUG] ${message}`);
            }
        },
        info: (message) => {
            console.log(`[INFO] ${message}`);
        },
        error: (message) => {
            console.error(`[ERROR] ${message}`);
        }
    };

    try {
        // 存储游戏卡片数据
        const gameDataContainer = document.getElementById('game-data');
        const gameCardsData = gameDataContainer ? Array.from(gameDataContainer.querySelectorAll('.game-card')) : [];
        
        // 游戏容器
        const gameContainer = document.getElementById('games-container');
        
        // 加载状态指示器
        const loadingIndicator = document.getElementById('loading-indicator');
        const noMoreGamesMessage = document.getElementById('no-more-games');
        
        // 每页游戏数量
        const gamesPerPage = 8;
        
        // 当前页码和是否所有游戏都已加载标志
        let currentPage = 0;
        let allGames = [];
        let filteredGames = [];
        let isLoading = false;
        let allLoaded = false;
        
        // 添加节流函数，避免滚动事件频繁触发
        function throttle(func, delay) {
            let lastCall = 0;
            return function(...args) {
                const now = new Date().getTime();
                if (now - lastCall < delay) {
                    return;
                }
                lastCall = now;
                return func(...args);
            };
        }
        
        // 初始化游戏数据
        function initGameData() {
            // 检查游戏数据和容器是否存在
            if (!gameContainer) {
                logger.error('Game container not found: #games-container');
                return;
            }
            
            if (gameCardsData.length > 0) {
                logger.info(`Loaded ${gameCardsData.length} games from data container`);
                allGames = gameCardsData.map(card => card.outerHTML);
                filteredGames = [...allGames];
                loadNextPage();
            } else {
                logger.warn('No game data found');
                if (noMoreGamesMessage) {
                    noMoreGamesMessage.style.display = 'block';
                }
                if (loadingIndicator) {
                    loadingIndicator.style.display = 'none';
                }
            }
        }
        
        // 加载下一页游戏
        function loadNextPage() {
            try {
                // 检查容器是否存在
                if (!gameContainer) {
                    logger.error('Game container not available');
                    return;
                }
                
                if (isLoading || allLoaded) return;
                
                isLoading = true;
                
                if (loadingIndicator) {
                    loadingIndicator.style.display = 'block';
                }
                
                // 计算本页需要加载的游戏范围
                const startIndex = currentPage * gamesPerPage;
                let endIndex = startIndex + gamesPerPage;
                
                // 检查是否还有更多游戏
                if (startIndex >= filteredGames.length) {
                    // 没有更多游戏了
                    allLoaded = true;
                    if (loadingIndicator) {
                        loadingIndicator.style.display = 'none';
                    }
                    if (noMoreGamesMessage) {
                        noMoreGamesMessage.style.display = 'block';
                        // 重置无更多游戏消息文本
                        noMoreGamesMessage.querySelector('p').textContent = 'You\'ve reached the end. No more games to load.';
                    }
                    isLoading = false;
                    logger.info('All games loaded');
                    
                    // 清理页面上可能存在的多余元素
                    cleanupPageElements();
                    return;
                }
                
                // 调整结束索引，确保不超出范围
                if (endIndex > filteredGames.length) {
                    endIndex = filteredGames.length;
                    allLoaded = true;
                }
                
                // 获取这一页的游戏数据
                const pageGames = filteredGames.slice(startIndex, endIndex);
                logger.debug(`Loading games ${startIndex+1}-${endIndex} of ${filteredGames.length}`);
                
                // 创建文档片段，提高性能
                const fragment = document.createDocumentFragment();
                
                // 插入游戏卡片到文档片段
                pageGames.forEach(gameHTML => {
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = gameHTML;
                    const gameElement = tempDiv.firstElementChild;
                    
                    // 添加初始状态类，用于动画
                    gameElement.classList.add('loading-card');
                    fragment.appendChild(gameElement);
                });
                
                // 将文档片段添加到游戏容器
                gameContainer.appendChild(fragment);
                
                // 使用setTimeout触发动画，让浏览器有时间渲染初始状态
                setTimeout(() => {
                    const newCards = gameContainer.querySelectorAll('.loading-card');
                    newCards.forEach((card, index) => {
                        // 用延迟实现卡片的级联动画效果
                        setTimeout(() => {
                            card.classList.remove('loading-card');
                            card.classList.add('loaded-card');
                        }, index * 50);
                    });
                }, 10);
                
                // 更新页码
                currentPage++;
                
                // 如果已加载所有游戏
                if (endIndex >= filteredGames.length) {
                    if (loadingIndicator) {
                        loadingIndicator.style.display = 'none';
                    }
                    if (noMoreGamesMessage) {
                        noMoreGamesMessage.style.display = 'block';
                        // 重置无更多游戏消息文本
                        noMoreGamesMessage.querySelector('p').textContent = 'You\'ve reached the end. No more games to load.';
                    }
                    logger.info('All games loaded');
                    allLoaded = true;
                    
                    // 清理页面上可能存在的多余元素
                    cleanupPageElements();
                } else {
                    // 隐藏无更多游戏消息
                    if (loadingIndicator) {
                        loadingIndicator.style.display = 'none';
                    }
                }
                
                // 完成加载
                isLoading = false;
            } catch (err) {
                isLoading = false;
                logger.error('Error loading next page', err);
            }
        }
        
        // 清理页面上可能存在的多余元素
        function cleanupPageElements() {
            try {
                logger.info('开始清理页面多余元素');
                
                // 1. 删除页面底部可能出现的任何iframe、embed或其他不必要的元素
                const iframes = document.querySelectorAll('iframe:not([src*="crazygames"])');
                iframes.forEach(iframe => {
                    logger.debug('Removing unexpected iframe: ' + iframe.src);
                    iframe.parentNode.removeChild(iframe);
                });
                
                // 2. 移除底部可能存在的多余链接或文本
                const footerLinks = document.querySelectorAll('body > a:not([href^="http"])');
                footerLinks.forEach(link => {
                    logger.debug('Removing unexpected link: ' + link.href);
                    link.parentNode.removeChild(link);
                });
                
                // 3. 检测并删除重复的游戏卡片
                // 3.1 首先获取所有游戏卡片的链接
                const gameCards = document.querySelectorAll('.game-card');
                const gameUrls = new Map();
                
                gameCards.forEach(card => {
                    const link = card.querySelector('a');
                    if (link) {
                        const href = link.getAttribute('href');
                        if (gameUrls.has(href)) {
                            // 找到重复的卡片，将其删除
                            logger.debug(`Removing duplicate game card: ${href}`);
                            card.parentNode.removeChild(card);
                        } else {
                            gameUrls.set(href, card);
                        }
                    }
                });
                
                // 4. 移除其他可能的不必要元素
                const unexpectedElements = document.querySelectorAll('body > *:not(header):not(section):not(.ad-banner):not(script):not(#game-data):not(style)');
                unexpectedElements.forEach(element => {
                    // 排除已知的必要元素
                    if (element.id !== 'games-container' && 
                        element.id !== 'loading-indicator' && 
                        element.id !== 'no-more-games' &&
                        !element.classList.contains('container') &&
                        !element.classList.contains('site-header') &&
                        !element.classList.contains('pagination')) {
                        logger.debug('Removing unexpected element: ' + element.tagName);
                        element.parentNode.removeChild(element);
                    }
                });
                
                // 5. 移除可能出现在底部的游戏卡片重复元素
                const duplicateCardElements = document.querySelectorAll('body > .game-card, body > .game-thumbnail, body > .game-info, body > .game-overlay, body > .play-button, body > div > .game-card:not(#games-container .game-card):not(#game-data .game-card)');
                duplicateCardElements.forEach(element => {
                    logger.debug('Removing duplicate game card element: ' + element.tagName);
                    element.parentNode.removeChild(element);
                });
                
                // 6. 清理直接挂在body下的文本节点
                const bodyChildNodes = document.body.childNodes;
                for (let i = bodyChildNodes.length - 1; i >= 0; i--) {
                    const node = bodyChildNodes[i];
                    if (node.nodeType === Node.TEXT_NODE && node.textContent.trim() !== '') {
                        logger.debug('Removing text node from body: ' + node.textContent.trim().substring(0, 50) + '...');
                        document.body.removeChild(node);
                    }
                }
                
                // 7. 检查并修复body的直接子元素
                const validBodyChildren = ['HEADER', 'SECTION', 'DIV', 'SCRIPT', 'STYLE', 'LINK', 'MAIN', 'FOOTER', 'NAV'];
                Array.from(document.body.children).forEach(child => {
                    if (!validBodyChildren.includes(child.tagName) && 
                        child.id !== 'game-data' && 
                        child.id !== 'games-container' &&
                        !child.classList.contains('ad-banner')) {
                        logger.debug('Removing invalid body child: ' + child.tagName);
                        child.parentNode.removeChild(child);
                    }
                });
                
                // 8. 清理页面中所有包含特定URL的元素
                const problematicUrls = ['file:///', 'traffic-rider.html', 'games/traffic-rider.html'];
                for (const url of problematicUrls) {
                    const elementsWithUrl = Array.from(document.querySelectorAll('*')).filter(el => 
                        el.textContent && el.textContent.includes(url) && 
                        !el.closest('#game-data') && // 不处理#game-data内的元素
                        !el.closest('.game-card') && // 不处理正常的游戏卡片
                        el.tagName !== 'SCRIPT' && el.tagName !== 'STYLE'
                    );
                    
                    elementsWithUrl.forEach(el => {
                        logger.debug(`Cleaning element with URL text (${url}): ${el.tagName}`);
                        if (el.childNodes.length === 0) {
                            try {
                                el.parentNode.removeChild(el);
                            } catch (err) {
                                logger.error('Failed to remove element', err);
                            }
                        } else {
                            // 尝试保留元素但清除其中的文本
                            let hasNonTextChildren = false;
                            for (const child of el.childNodes) {
                                if (child.nodeType !== Node.TEXT_NODE) {
                                    hasNonTextChildren = true;
                                    break;
                                }
                            }
                            
                            if (!hasNonTextChildren) {
                                el.textContent = '';
                            } else {
                                // 只清除文本节点，保留其他节点
                                Array.from(el.childNodes).forEach(child => {
                                    if (child.nodeType === Node.TEXT_NODE && child.textContent.trim() !== '') {
                                        try {
                                            el.removeChild(child);
                                        } catch (err) {
                                            logger.error('Failed to remove text node', err);
                                        }
                                    }
                                });
                            }
                        }
                    });
                }
                
                // 9. 确保game-data元素中只有游戏卡片
                const gameDataElement = document.getElementById('game-data');
                if (gameDataElement) {
                    Array.from(gameDataElement.childNodes).forEach(node => {
                        if (node.nodeType === Node.TEXT_NODE || 
                            (node.nodeType === Node.ELEMENT_NODE && !node.classList.contains('game-card'))) {
                            logger.debug('Removing non-game-card element from game-data: ' + 
                                (node.nodeType === Node.TEXT_NODE ? 'TEXT_NODE' : node.tagName));
                            try {
                                gameDataElement.removeChild(node);
                            } catch (err) {
                                logger.error('Failed to remove node from game-data', err);
                            }
                        }
                    });
                }
                
                // 10. 检查任何直接挂在body或不正确容器下的a标签
                document.querySelectorAll('body > a, div > a:not(.game-card a)').forEach(link => {
                    // 检查是否为游戏链接
                    if (link.href && link.href.includes('games/')) {
                        logger.debug('Removing incorrectly placed game link: ' + link.href);
                        try {
                            link.parentNode.removeChild(link);
                        } catch (err) {
                            logger.error('Failed to remove incorrectly placed link', err);
                        }
                    }
                });
                
                // 11. 特别检查混乱的div嵌套
                document.querySelectorAll('div > div:not([class]):not([id])').forEach(div => {
                    // 检查这个div是否只包含游戏相关的元素但没有正确的结构
                    if (div.querySelector('.game-thumbnail') || div.querySelector('.game-info')) {
                        if (!div.classList.contains('game-card')) {
                            logger.debug('Removing incorrectly structured game div');
                            try {
                                div.parentNode.removeChild(div);
                            } catch (err) {
                                logger.error('Failed to remove incorrectly structured div', err);
                            }
                        }
                    }
                });
                
                // 12. 修正游戏卡片中的分类标签，确保统一使用英文
                document.querySelectorAll('.game-card .game-category').forEach(category => {
                    // 检查是否使用了中文分类
                    if (category.textContent === '竞速游戏') {
                        category.textContent = 'Racing';
                    } else if (category.textContent === '益智游戏') {
                        category.textContent = 'Puzzle';
                    } else if (category.textContent === '动作游戏') {
                        category.textContent = 'Action';
                    } else if (category.textContent === '冒险游戏') {
                        category.textContent = 'Adventure';
                    } else if (category.textContent === '策略游戏') {
                        category.textContent = 'Strategy';
                    } else if (category.textContent === '休闲游戏') {
                        category.textContent = 'Casual';
                    } else if (category.textContent === '恐怖游戏') {
                        category.textContent = 'Horror';
                    } else if (category.textContent === '体育游戏') {
                        category.textContent = 'Sports';
                    }
                });
                
                // 13. 检测并清理可能的HTML注入攻击
                document.querySelectorAll('*[src*="javascript:"], *[href*="javascript:"]').forEach(el => {
                    logger.debug('Removing potential JavaScript injection: ' + el.outerHTML.substring(0, 50));
                    try {
                        el.parentNode.removeChild(el);
                    } catch (err) {
                        logger.error('Failed to remove potential injection', err);
                    }
                });
                
                // 14. 清理body底部可能出现的不必要的元素
                const bodyChildren = document.body.children;
                for (let i = bodyChildren.length - 1; i >= 0; i--) {
                    const child = bodyChildren[i];
                    // 忽略常见的页面结构元素
                    if (child.tagName !== 'HEADER' && 
                        child.tagName !== 'MAIN' && 
                        child.tagName !== 'FOOTER' && 
                        child.tagName !== 'SCRIPT' && 
                        child.tagName !== 'STYLE' && 
                        !child.id && 
                        !child.className && 
                        child.tagName !== 'DIV') {
                        logger.debug('Removing unexpected element at bottom of body: ' + child.tagName);
                        try {
                            document.body.removeChild(child);
                        } catch (err) {
                            logger.error('Failed to remove unexpected element', err);
                        }
                    }
                }
                
                // 15. 检查并移除空元素
                document.querySelectorAll('div:empty:not([id]):not([class])').forEach(div => {
                    logger.debug('Removing empty div');
                    try {
                        div.parentNode.removeChild(div);
                    } catch (err) {
                        logger.error('Failed to remove empty div', err);
                    }
                });
                
                // 16. 确保所有游戏卡片都放置在正确的容器中
                const misplacedGameCards = document.querySelectorAll('.game-card:not(#games-container .game-card):not(#game-data .game-card)');
                misplacedGameCards.forEach(card => {
                    logger.debug('Removing misplaced game card');
                    try {
                        card.parentNode.removeChild(card);
                    } catch (err) {
                        logger.error('Failed to remove misplaced game card', err);
                    }
                });
                
                logger.info('页面清理完成');
            } catch (err) {
                logger.error('Error in cleanupPageElements:', err);
            }
        }
        
        // 节流版的清理函数，用于频繁调用的场景
        const throttledCleanup = throttle(cleanupPageElements, 1000);
        
        // 处理滚动事件的函数
        function handleScroll() {
            // 如果已经全部加载完，不再触发加载
            if (allLoaded) return;
            
            // 计算页面滚动位置
            const scrollPosition = window.scrollY;
            const windowHeight = window.innerHeight;
            const documentHeight = document.documentElement.scrollHeight;
            
            // 当滚动到页面底部附近时加载更多游戏
            // 这里设置为距离底部300px时开始加载
            if (documentHeight - (scrollPosition + windowHeight) < 300 && !isLoading) {
                loadNextPage();
            }
            
            // 每次滚动也执行清理，但使用节流版本避免过于频繁
            throttledCleanup();
        }
        
        // 使用节流函数包装滚动事件处理
        const throttledScroll = throttle(handleScroll, 200);
        
        // 滚动事件监听器
        window.addEventListener('scroll', throttledScroll);
        
        // 在多个时机执行清理
        window.addEventListener('load', function() {
            // 立即执行一次
            cleanupPageElements();
            
            // 延迟执行多次，确保所有动态内容加载后也进行清理
            setTimeout(cleanupPageElements, 500);
            setTimeout(cleanupPageElements, 1500);
            setTimeout(cleanupPageElements, 3000);
        });
        
        // DOMContentLoaded时也执行清理
        window.addEventListener('DOMContentLoaded', cleanupPageElements);
        
        // 使用MutationObserver监听DOM变化，在发生变化时进行清理
        const observer = new MutationObserver(function(mutations) {
            // 检查是否有需要立即清理的变化
            let needsImmediateCleanup = false;
            
            for (const mutation of mutations) {
                // 1. 检查新增的节点
                if (mutation.addedNodes.length > 0) {
                    for (const node of mutation.addedNodes) {
                        // 检查可疑的标签或内容
                        if (node.nodeType === Node.ELEMENT_NODE) {
                            const tagName = node.tagName;
                            
                            // 可疑的元素标签
                            if (tagName === 'IFRAME' || 
                                (tagName === 'A' && !node.href.startsWith('http') && !node.href.includes('games/')) ||
                                (node.innerHTML && node.innerHTML.includes('traffic-rider.html'))) {
                                needsImmediateCleanup = true;
                                break;
                            }
                        }
                        // 检查文本节点是否包含可疑内容
                        else if (node.nodeType === Node.TEXT_NODE) {
                            const text = node.textContent;
                            if (text && (text.includes('http') || text.includes('.html'))) {
                                needsImmediateCleanup = true;
                                break;
                            }
                        }
                    }
                }
                
                // 2. 检查移除的节点
                // 如果重要的网站结构元素被移除，可能需要恢复
                if (mutation.removedNodes.length > 0) {
                    for (const node of mutation.removedNodes) {
                        if (node.nodeType === Node.ELEMENT_NODE) {
                            // 检查是否有关键元素被移除
                            if (node.id === 'game-data' || 
                                node.id === 'games-container' || 
                                node.classList.contains('site-header')) {
                                needsImmediateCleanup = true;
                                break;
                            }
                        }
                    }
                }
                
                if (needsImmediateCleanup) break;
            }
            
            if (needsImmediateCleanup) {
                // 立即清理，不使用节流
                logger.debug('检测到需要立即清理的DOM变化');
                cleanupPageElements();
            } else {
                // 使用节流版本的清理函数
                throttledCleanup();
            }
        });
        
        // 在页面加载完成后启动观察器
        window.addEventListener('load', function() {
            // 配置观察器
            observer.observe(document.body, {
                childList: true,  // 观察直接子节点的添加或删除
                subtree: true,    // 观察所有后代节点
                attributes: false, // 不观察属性变化
                characterData: true // 观察文本内容变化
            });
            
            logger.debug('DOM变化观察器已启动');
            
            // 设置定期清理任务，每5秒执行一次，无论是否有变化
            setInterval(cleanupPageElements, 5000);
        });
        
        // 过滤游戏（从categories.js调用）
        window.filterGamesByCategory = function(category) {
            try {
                // 重置状态
                currentPage = 0;
                allLoaded = false;
                isLoading = false;
                
                // 清空游戏容器
                while (gameContainer && gameContainer.firstChild) {
                    gameContainer.removeChild(gameContainer.firstChild);
                }
                
                // 重置加载指示器
                if (loadingIndicator) {
                    loadingIndicator.style.display = 'none';
                }
                if (noMoreGamesMessage) {
                    noMoreGamesMessage.style.display = 'none';
                }
                
                logger.info(`Filtering games by category: ${category}`);
                
                // 应用过滤器
                if (category === 'all') {
                    filteredGames = [...allGames];
                } else {
                    filteredGames = allGames.filter(gameHTML => {
                        const tempDiv = document.createElement('div');
                        tempDiv.innerHTML = gameHTML;
                        const gameCard = tempDiv.firstElementChild;
                        return gameCard.getAttribute('data-category') === category;
                    });
                }
                
                logger.debug(`Found ${filteredGames.length} games in category "${category}"`);
                
                // 如果没有找到任何游戏
                if (filteredGames.length === 0) {
                    if (noMoreGamesMessage) {
                        noMoreGamesMessage.querySelector('p').textContent = 'No games found in this category.';
                        noMoreGamesMessage.style.display = 'block';
                    }
                } else {
                    // 加载第一页
                    loadNextPage();
                }
            } catch (err) {
                logger.error('Error filtering games', err);
            }
        };
        
        // 初始化
        initGameData();
    } catch (err) {
        logger.error('Error initializing infinite scroll', err);
    }
});
