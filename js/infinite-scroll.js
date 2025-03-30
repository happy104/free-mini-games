/**
 * Infinite Scroll for Games
 * Loads games with infinite scroll functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    // 简单日志系统
    const logger = {
        debug: (msg) => console.debug(`[InfiniteScroll] ${msg}`),
        info: (msg) => console.info(`[InfiniteScroll] ${msg}`),
        warn: (msg) => console.warn(`[InfiniteScroll] ${msg}`),
        error: (msg, err) => {
            console.error(`[InfiniteScroll] ${msg}`);
            if (err) console.error(err);
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
                        noMoreGamesMessage.textContent = 'You\'ve reached the end. No more games to load.';
                    }
                    isLoading = false;
                    logger.info('All games loaded');
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
                        noMoreGamesMessage.textContent = 'You\'ve reached the end. No more games to load.';
                    }
                    logger.info('All games loaded');
                    allLoaded = true;
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
        
        // 处理滚动事件的函数
        function handleScroll() {
            // 如果已经全部加载完，不再触发
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
        }
        
        // 使用节流函数包装滚动事件处理
        const throttledScroll = throttle(handleScroll, 200);
        
        // 滚动事件监听器
        window.addEventListener('scroll', throttledScroll);
        
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
                        noMoreGamesMessage.textContent = 'No games found in this category.';
                        noMoreGamesMessage.style.display = 'block';
                    }
                } else {
                    // 加载第一页
                    loadNextPage();
                }
            } catch (err) {
                logger.error(`Error filtering by category "${category}"`, err);
            }
        };
        
        // 初始化加载
        initGameData();
        
    } catch (err) {
        logger.error('Fatal error initializing infinite scroll', err);
    }
}); 