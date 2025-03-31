/**
 * Game Data Loader
 * Loads game data from games-data.json and displays on the page
 * Provides game filtering and pagination functionality
 */

// Use immediately invoked function to protect variable scope
(function() {
    // Simple logging utility
    const logger = {
        info: function(msg) {
            console.log('[GameLoader] ' + msg);
        },
        error: function(msg) {
            console.error('[GameLoader] ' + msg);
        }
    };

    // Game data and states
    let gameData = [];
    let isLoading = false;
    let currentPage = 1;
    let hasMoreGames = true;
    let selectedCategory = 'all';
    const gamesPerPage = 12; // Number of games per page

    // Initialize when page is loaded
    document.addEventListener('DOMContentLoaded', function() {
        try {
            // Get games container
            const gamesContainer = document.getElementById('games-container');
            if (!gamesContainer) {
                logger.error('Games container not found, cannot initialize');
                return;
            }
            
            // Ensure loading indicators exist
            const loadingIndicator = document.getElementById('loading-indicator');
            const noMoreGames = document.getElementById('no-more-games');
            const loadMoreBtn = document.getElementById('load-more-btn');
            
            // Load more button click event
            if (loadMoreBtn) {
                loadMoreBtn.addEventListener('click', loadGames);
            }
            
            // Load game data
            fetchGameData();
            
            // Setup category filter
            setupCategoryFilter();
        } catch (error) {
            logger.error('Error initializing game loader: ' + error.message);
        }
    });
    
    // Throttle function to limit function calls
    function throttle(func, delay) {
        let lastCall = 0;
        return function() {
            const now = new Date().getTime();
            if (now - lastCall < delay) {
                return;
            }
            lastCall = now;
            return func.apply(this, arguments);
        };
    }
    
    // Load game data from JSON file
    function fetchGameData() {
        logger.info('Loading game data from JSON file');
        
        // 添加时间戳作为随机参数，确保不使用缓存
        const timestamp = new Date().getTime();
        const randomParam = Math.floor(Math.random() * 1000000);
        
        // 使用PHP脚本提供的游戏数据，确保不会被缓存
        fetch(`js/get-games-data.php?v=${timestamp}&r=${randomParam}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Unable to load game data: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                // 处理新的JSON数据结构（带时间戳）
                const games = data.games || data; // 兼容新旧结构
                
                logger.info(`Successfully loaded ${games.length} game entries`);
                logger.info(`Data timestamp: ${data.timestamp || 'not available'}`);
                
                // 确保每次刷新页面时都有不同的游戏排序
                gameData = randomizeGames(games);
                
                // Remove loading animation
                const loadingSpinner = document.querySelector('.loading-spinner');
                if (loadingSpinner) {
                    loadingSpinner.style.display = 'none';
                }
                
                // Load first batch of games
                loadGames();
            })
            .catch(error => {
                logger.error('Error loading game data: ' + error.message);
                
                // Display error message
                const gamesContainer = document.getElementById('games-container');
                if (gamesContainer) {
                    gamesContainer.innerHTML = `
                        <div class="error-message" style="text-align:center;padding:30px;">
                            <i class="fas fa-exclamation-triangle" style="font-size:48px;color:#e74c3c;"></i>
                            <p style="margin-top:15px;">Error loading game data</p>
                            <p style="margin-top:5px;font-size:14px;color:#777;">${error.message}</p>
                            <button onclick="location.reload()" style="margin-top:15px;padding:8px 15px;background:#3498db;color:#fff;border:none;border-radius:4px;cursor:pointer;">
                                Retry
                            </button>
                        </div>
                    `;
                }
            });
    }
    
    // Load games with pagination
    function loadGames() {
        if (isLoading || !hasMoreGames) return;
        
        isLoading = true;
        
        // Show loading indicator
        const loadingIndicator = document.getElementById('loading-indicator');
        if (loadingIndicator) {
            loadingIndicator.style.display = 'block';
        }
        
        // Hide "no more games" message
        const noMoreGames = document.getElementById('no-more-games');
        if (noMoreGames) {
            noMoreGames.style.display = 'none';
        }
        
        // Filter games by selected category
        const filteredGames = selectedCategory === 'all' 
            ? gameData 
            : gameData.filter(game => game.category === selectedCategory);
        
        // Calculate games to load for current page
        const startIndex = (currentPage - 1) * gamesPerPage;
        const endIndex = Math.min(startIndex + gamesPerPage, filteredGames.length);
        
        // Check if we've reached the end
        if (startIndex >= filteredGames.length) {
            hasMoreGames = false;
            isLoading = false;
            
            if (loadingIndicator) {
                loadingIndicator.style.display = 'none';
            }
            
            if (noMoreGames) {
                noMoreGames.style.display = 'block';
            }
            
            // Hide load more button
            const loadMoreBtn = document.getElementById('load-more-btn');
            if (loadMoreBtn) {
                loadMoreBtn.style.display = 'none';
            }
            
            return;
        }
        
        // Get games container
        const gamesContainer = document.getElementById('games-container');
        if (!gamesContainer) {
            logger.error('Games container not found');
            isLoading = false;
            return;
        }
        
        // Add game cards to container
        for (let i = startIndex; i < endIndex; i++) {
            if (i < filteredGames.length) {
                const game = filteredGames[i];
                
                // Create game card element
                const gameCard = document.createElement('div');
                gameCard.className = 'game-card';
                gameCard.setAttribute('data-category', game.category);
                
                gameCard.innerHTML = `
                    <a href="games/${game.id}.html">
                        <div class="game-thumbnail">
                            <img src="images/games/${game.thumbnail}" alt="${game.title}" loading="lazy">
                            <div class="game-overlay">
                                <div class="play-button">
                                    <i class="fas fa-play"></i>
                                </div>
                            </div>
                        </div>
                        <div class="game-info">
                            <h3>${game.title}</h3>
                            <div class="game-meta">
                                <span class="game-category">${game.categoryName}</span>
                                <span class="game-rating"><i class="fas fa-star"></i> ${game.rating}</span>
                            </div>
                        </div>
                    </a>
                `;
                
                gamesContainer.appendChild(gameCard);
            }
        }
        
        // Update page counter and loading state
        currentPage++;
        isLoading = false;
        
        // Hide loading indicator
        if (loadingIndicator) {
            loadingIndicator.style.display = 'none';
        }
        
        // Check if there are more games
        if (endIndex >= filteredGames.length) {
            hasMoreGames = false;
            
            if (noMoreGames) {
                noMoreGames.style.display = 'block';
            }
            
            // Hide load more button
            const loadMoreBtn = document.getElementById('load-more-btn');
            if (loadMoreBtn) {
                loadMoreBtn.style.display = 'none';
            }
        }
    }
    
    // Setup category filter buttons
    function setupCategoryFilter() {
        const categoryButtons = document.querySelectorAll('.category-filter button');
        if (!categoryButtons.length) return;
        
        categoryButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Skip if already selected
                if (this.classList.contains('active')) return;
                
                // Update active button
                document.querySelectorAll('.category-filter button').forEach(btn => {
                    btn.classList.remove('active');
                });
                this.classList.add('active');
                
                // Get selected category
                const category = this.getAttribute('data-category');
                selectedCategory = category;
                
                // Reset pagination
                currentPage = 1;
                hasMoreGames = true;
                
                // Clear current games
                const gamesContainer = document.getElementById('games-container');
                if (gamesContainer) {
                    gamesContainer.innerHTML = '';
                }
                
                // Show load more button
                const loadMoreBtn = document.getElementById('load-more-btn');
                if (loadMoreBtn) {
                    loadMoreBtn.style.display = 'block';
                }
                
                // Hide no more games message
                const noMoreGames = document.getElementById('no-more-games');
                if (noMoreGames) {
                    noMoreGames.style.display = 'none';
                }
                
                // Load games for selected category
                loadGames();
            });
        });
    }

    // 随机打乱游戏数组
    function randomizeGames(games) {
        logger.info('Randomizing game order');
        
        // 复制数组避免修改原始数据
        const gamesCopy = [...games];
        
        // Fisher-Yates随机算法
        for (let i = gamesCopy.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [gamesCopy[i], gamesCopy[j]] = [gamesCopy[j], gamesCopy[i]];
        }
        
        return gamesCopy;
    }
})(); 