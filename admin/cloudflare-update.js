/**
 * Cloudflare Pages兼容的游戏数据更新脚本
 * 替代原有的PHP更新脚本，适用于静态托管环境
 */

document.addEventListener('DOMContentLoaded', function() {
    const updateForm = document.getElementById('update-form');
    const resultArea = document.getElementById('result-area');
    const gamesList = document.getElementById('games-list');
    
    if (updateForm) {
        updateForm.addEventListener('submit', function(e) {
            e.preventDefault();
            updateGameData();
        });
    }
    
    // 从本地存储加载游戏数据
    loadGamesFromStorage();
    
    // 从远程JSON文件加载游戏数据
    fetchGameData();
    
    // 更新按钮点击事件
    function updateGameData() {
        resultArea.innerHTML = '<div class="alert alert-info">Updating game data...</div>';
        
        // 创建新的游戏数据对象
        const gameData = {
            timestamp: Math.floor(new Date().getTime() / 1000),
            games: []
        };
        
        // 获取页面上的所有游戏卡片
        const gameCards = document.querySelectorAll('.game-card');
        gameCards.forEach(card => {
            const gameId = card.getAttribute('data-id');
            const gameTitle = card.querySelector('.game-title').textContent;
            const gameCategory = card.getAttribute('data-category');
            const gameCategoryName = getCategoryName(gameCategory);
            const gameRating = card.querySelector('.game-rating').textContent.trim();
            const gameThumbnail = card.querySelector('.game-thumbnail img').getAttribute('data-src');
            
            gameData.games.push({
                id: gameId,
                title: gameTitle,
                category: gameCategory,
                categoryName: gameCategoryName,
                rating: gameRating,
                thumbnail: gameThumbnail
            });
        });
        
        // 将数据保存到本地存储
        localStorage.setItem('gameData', JSON.stringify(gameData));
        
        // 在实际环境中，这一步需要通过API将数据提交到服务器
        // 在Cloudflare静态环境中，你需要一个serverless函数或外部API来处理
        
        // 显示成功消息
        resultArea.innerHTML = `
            <div class="alert alert-success">
                <p>Game data has been updated locally.</p>
                <p>In Cloudflare environment, you need to manually update the games-data.json file:</p>
                <ol>
                    <li>Copy the JSON data below</li>
                    <li>Update your Git repository with this data</li>
                    <li>Deploy the changes to Cloudflare</li>
                </ol>
                <div class="code-block">
                    <pre>${JSON.stringify(gameData, null, 4)}</pre>
                </div>
                <button id="copy-json" class="btn btn-primary mt-2">Copy JSON to Clipboard</button>
            </div>
        `;
        
        // 添加复制按钮功能
        document.getElementById('copy-json').addEventListener('click', function() {
            const jsonText = JSON.stringify(gameData, null, 4);
            navigator.clipboard.writeText(jsonText)
                .then(() => {
                    this.textContent = 'Copied!';
                    setTimeout(() => {
                        this.textContent = 'Copy JSON to Clipboard';
                    }, 2000);
                })
                .catch(err => {
                    console.error('Failed to copy: ', err);
                    this.textContent = 'Copy failed';
                });
        });
    }
    
    // 获取分类名称
    function getCategoryName(category) {
        const categories = {
            'action': 'Action Games',
            'puzzle': 'Puzzle Games',
            'racing': 'Racing Games',
            'sports': 'Sports Games',
            'strategy': 'Strategy Games',
            'horror': 'Horror Games',
            'adventure': 'Adventure Games',
            'casual': 'Casual Games'
        };
        
        return categories[category] || 'Uncategorized';
    }
    
    // 从本地存储加载游戏
    function loadGamesFromStorage() {
        const storedData = localStorage.getItem('gameData');
        if (!storedData) return;
        
        try {
            const data = JSON.parse(storedData);
            displayGames(data.games || []);
        } catch (error) {
            console.error('Error loading games from storage:', error);
        }
    }
    
    // 从远程JSON加载游戏数据
    function fetchGameData() {
        const timestamp = new Date().getTime();
        fetch(`../js/games-data.json?v=${timestamp}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to load game data');
                }
                return response.json();
            })
            .then(data => {
                displayGames(data.games || data);
                
                // 更新本地存储
                localStorage.setItem('gameData', JSON.stringify(data));
            })
            .catch(error => {
                console.error('Error fetching game data:', error);
                resultArea.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
            });
    }
    
    // 显示游戏列表
    function displayGames(games) {
        if (!gamesList) return;
        
        gamesList.innerHTML = '';
        
        games.forEach(game => {
            const card = document.createElement('div');
            card.className = 'game-card';
            card.setAttribute('data-id', game.id);
            card.setAttribute('data-category', game.category);
            
            card.innerHTML = `
                <div class="card">
                    <div class="card-header">
                        <h5 class="game-title">${game.title}</h5>
                    </div>
                    <div class="card-body">
                        <div class="game-thumbnail">
                            <img src="../images/games/${game.thumbnail}" data-src="${game.thumbnail}" alt="${game.title}">
                        </div>
                        <div class="game-info">
                            <p>Category: <span class="game-category">${game.category}</span></p>
                            <p>Rating: <span class="game-rating">${game.rating}</span></p>
                        </div>
                    </div>
                </div>
            `;
            
            gamesList.appendChild(card);
        });
    }
}); 