/**
 * 游戏分类脚本
 * 处理游戏分类按钮的点击事件和分类筛选功能
 */

document.addEventListener('DOMContentLoaded', function() {
    // 创建日志工具
    const logger = {
        debug: function(message) {
            console.log('[分类] [DEBUG] ' + message);
        },
        info: function(message) {
            console.log('[分类] [INFO] ' + message);
        },
        error: function(message) {
            console.error('[分类] [ERROR] ' + message);
        }
    };

    try {
        logger.info('初始化游戏分类脚本');
        
        // 获取所有分类按钮
        const categoryButtons = document.querySelectorAll('.category-filter button');
        
        if (categoryButtons.length === 0) {
            logger.error('找不到分类按钮');
            return;
        }
        
        logger.info('找到' + categoryButtons.length + '个分类按钮');
        
        // 英文分类和data-category属性映射
        const categoryMap = {
            'All Games': 'all',
            'Action': 'action',
            'Puzzle': 'puzzle',
            'Racing': 'racing',
            'Sports': 'sports',
            'Strategy': 'strategy',
            'Horror': 'horror',
            'Adventure': 'adventure',
            'Casual': 'casual'
        };
        
        // 为每个分类按钮添加点击事件
        categoryButtons.forEach(button => {
            button.addEventListener('click', function(event) {
                event.preventDefault();
                
                // 移除所有按钮的active类
                categoryButtons.forEach(btn => btn.classList.remove('active'));
                
                // 给当前按钮添加active类
                this.classList.add('active');
                
                // 获取分类值
                const category = this.getAttribute('data-category');
                logger.info('选择分类: ' + category);
                
                // 更新页面标题
                const categoryTitle = document.querySelector('.section-title');
                if (categoryTitle) {
                    categoryTitle.textContent = this.textContent;
                }
                
                // 筛选游戏卡片
                filterGamesByCategory(category);
                
                // 更新URL参数，但不刷新页面
                const url = new URL(window.location.href);
                url.searchParams.set('category', category);
                window.history.pushState({}, '', url);
            });
        });
        
        // 从URL获取分类参数
        const urlParams = new URLSearchParams(window.location.search);
        const categoryParam = urlParams.get('category');
        
        if (categoryParam) {
            logger.info('从URL获取分类参数: ' + categoryParam);
            
            // 查找匹配的按钮并触发点击
            const matchingButton = document.querySelector(`.category-filter button[data-category="${categoryParam}"]`);
            if (matchingButton) {
                matchingButton.click();
            }
        }
        
    } catch (error) {
        logger.error('分类脚本出错: ' + error.message);
    }
});

// 根据分类筛选游戏卡片
function filterGamesByCategory(category) {
    const logger = {
        info: function(message) {
            console.log('[分类] [INFO] ' + message);
        },
        error: function(message) {
            console.error('[分类] [ERROR] ' + message);
        }
    };
    
    logger.info('筛选分类: ' + category);
    
    // 获取所有游戏卡片
    const gameCards = document.querySelectorAll('.game-card');
    
    if (gameCards.length === 0) {
        logger.error('找不到游戏卡片');
        return;
    }
    
    logger.info('找到' + gameCards.length + '个游戏卡片');
    
    // 筛选游戏卡片
    gameCards.forEach(card => {
        // 获取卡片的data-category属性
        let cardCategory = card.getAttribute('data-category');
        
        // 如果卡片没有data-category属性，尝试从游戏卡片内的分类标签获取
        if (!cardCategory) {
            const categorySpan = card.querySelector('.game-category');
            if (categorySpan) {
                const categoryText = categorySpan.textContent.trim();
                // 将中文分类名转换为英文分类ID
                if (categoryText === '竞速游戏' || categoryText.toLowerCase() === 'racing') {
                    cardCategory = 'racing';
                } else if (categoryText === '动作游戏' || categoryText.toLowerCase() === 'action') {
                    cardCategory = 'action';
                } else if (categoryText === '益智游戏' || categoryText.toLowerCase() === 'puzzle') {
                    cardCategory = 'puzzle';
                } else if (categoryText === '恐怖游戏' || categoryText.toLowerCase() === 'horror') {
                    cardCategory = 'horror';
                } else if (categoryText === '体育游戏' || categoryText.toLowerCase() === 'sports') {
                    cardCategory = 'sports';
                } else if (categoryText === '策略游戏' || categoryText.toLowerCase() === 'strategy') {
                    cardCategory = 'strategy';
                } else if (categoryText === '冒险游戏' || categoryText.toLowerCase() === 'adventure') {
                    cardCategory = 'adventure';
                } else if (categoryText === '休闲游戏' || categoryText.toLowerCase() === 'casual') {
                    cardCategory = 'casual';
                }
                
                // 设置data-category属性
                if (cardCategory) {
                    card.setAttribute('data-category', cardCategory);
                }
            }
        }
        
        // 显示或隐藏游戏卡片
        if (category === 'all' || cardCategory === category) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
    
    // 如果有无限滚动功能，通知它分类已更改
    if (typeof window.currentCategory !== 'undefined') {
        window.currentCategory = category;
        
        // 如果有filterGamesByCategory函数，调用它
        if (typeof window.filterGamesByCategory === 'function') {
            window.filterGamesByCategory(category);
        }
    }
}
