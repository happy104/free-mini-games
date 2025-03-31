/**
 * Free Mini Games - 侧边栏热门游戏加载
 * 负责加载游戏详情页右侧边栏的热门游戏列表
 * 2024-04-05
 */

// 等待DOM加载完成
document.addEventListener('DOMContentLoaded', function() {
    loadTopGamesInSidebar();
});

/**
 * 加载侧边栏中的热门游戏
 */
function loadTopGamesInSidebar() {
    // 获取侧边栏容器
    const topGamesList = document.querySelector('.sidebar-widget.top-games ul');
    if (!topGamesList) return;
    
    // 获取当前游戏分类
    let category = 'action'; // 默认为action
    const breadcrumbs = document.querySelector('.breadcrumbs');
    if (breadcrumbs) {
        const categoryLink = breadcrumbs.querySelector('a[href*="category="]');
        if (categoryLink) {
            const categoryMatch = categoryLink.getAttribute('href').match(/category=([^&]+)/);
            if (categoryMatch && categoryMatch[1]) {
                category = categoryMatch[1].toLowerCase();
            }
        }
    }
    
    // 获取游戏标题，用于排除当前游戏
    const gameTitle = document.querySelector('.game-header h1')?.textContent.trim();
    
    // 加载热门游戏数据
    const topGames = getTopGamesByCategory(category, gameTitle);
    
    // 检查是否有游戏数据
    if (topGames.length === 0) {
        topGamesList.innerHTML = '<li class="no-games">No games available in this category yet.</li>';
        return;
    }
    
    // 生成游戏列表HTML
    let gamesHTML = '';
    topGames.forEach(game => {
        gamesHTML += `
            <li>
                <a href="${game.url}">
                    <img src="${game.image}" alt="${game.title}">
                    <div class="game-info">
                        <h4>${game.title}</h4>
                        <div class="game-meta">
                            <span class="game-rating"><i class="fas fa-star"></i> ${game.rating}</span>
                        </div>
                    </div>
                </a>
            </li>
        `;
    });
    
    // 更新DOM
    topGamesList.innerHTML = gamesHTML;
}

/**
 * 根据分类获取热门游戏
 * @param {string} category - 游戏分类
 * @param {string} currentGame - 当前游戏标题（排除在列表外）
 * @returns {Array} 游戏列表
 */
function getTopGamesByCategory(category, currentGame) {
    // 模拟游戏数据
    // 在实际应用中，这些数据应该从服务器获取
    const allGames = {
        action: [
            {
                title: "Defender Idle 2",
                url: "../games/defender-idle-2.html",
                image: "../images/games/1743317898901.jpg",
                rating: "5.0"
            },
            {
                title: "Squid Game Online",
                url: "../games/squid-game-online-new.html",
                image: "../images/games/1743317727556.jpg",
                rating: "5.0"
            },
            {
                title: "Sandbox City",
                url: "../games/sandbox-city.html",
                image: "../images/games/1743317823377.jpg",
                rating: "4.8"
            }
        ],
        horror: [
            {
                title: "Scary Horror Escape Room",
                url: "../games/scary-horror-escape-room.html",
                image: "../images/games/1743317478830.jpg",
                rating: "4.8"
            },
            {
                title: "Exhibit of Sorrows",
                url: "../games/exhibit-of-sorrows.html",
                image: "../images/games/1743317420879.jpg",
                rating: "5.0"
            },
            {
                title: "Haunted School",
                url: "../games/haunted-school.html",
                image: "../images/games/1743317376675.jpg",
                rating: "4.6"
            }
        ],
        racing: [
            {
                title: "Mr. Racer",
                url: "../games/mr-racer.html",
                image: "../images/games/1743316631415.jpg",
                rating: "5.0"
            },
            {
                title: "Rally Racer Dirt",
                url: "../games/rally-racer-dirt.html",
                image: "../images/games/1743316340014.jpg",
                rating: "5.0"
            },
            {
                title: "Smash Karts",
                url: "../games/smash-karts.html",
                image: "../images/games/1743315018354.jpg",
                rating: "5.0"
            }
        ],
        puzzle: [
            {
                title: "Words of Wonders",
                url: "../games/words-of-wonders.html",
                image: "../images/games/1743266067856.jpg",
                rating: "4.7"
            },
            {
                title: "Farm Merge Valley",
                url: "../games/farm-merge-valley.html",
                image: "../images/games/1743265320769.jpg",
                rating: "4.4"
            },
            {
                title: "Mahjongg Solitaire",
                url: "../games/mahjongg-solitaire.html",
                image: "../images/games/1743264862795.jpg",
                rating: "4.8"
            }
        ]
    };
    
    // 如果没有该分类的游戏，显示任意分类游戏
    const games = allGames[category] || 
                 allGames.action || 
                 Object.values(allGames).find(games => games.length > 0) || 
                 [];
    
    // 过滤掉当前游戏
    return games.filter(game => game.title !== currentGame);
}

// 添加CSS样式以美化侧边栏游戏列表
function addSidebarStyles() {
    const styleEl = document.createElement('style');
    styleEl.textContent = `
        .sidebar-widget.top-games ul li {
            margin-bottom: 15px;
            border-bottom: 1px solid #333;
            padding-bottom: 15px;
        }
        
        .sidebar-widget.top-games ul li:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        
        .sidebar-widget.top-games a {
            display: flex;
            text-decoration: none;
            color: #ddd;
            transition: color 0.2s ease;
        }
        
        .sidebar-widget.top-games a:hover {
            color: #fff;
        }
        
        .sidebar-widget.top-games img {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
            margin-right: 10px;
        }
        
        .sidebar-widget.top-games .game-info {
            flex: 1;
        }
        
        .sidebar-widget.top-games h4 {
            font-size: 14px;
            margin: 0 0 5px 0;
            font-weight: 500;
        }
        
        .sidebar-widget.top-games .game-meta {
            font-size: 12px;
            color: #aaa;
        }
        
        .sidebar-widget.top-games .game-rating i {
            color: #FFD700;
            margin-right: 3px;
        }
        
        .sidebar-widget.top-games .no-games {
            color: #888;
            text-align: center;
            padding: 20px 0;
        }
    `;
    document.head.appendChild(styleEl);
}

// 添加样式
addSidebarStyles(); 