/**
 * Free Mini Games - 主JavaScript文件
 * 包含网站的基本交互功能
 */

// 等待DOM加载完成
document.addEventListener('DOMContentLoaded', function () {
    // 移动端菜单切换
    const menuToggle = document.querySelector('.menu-toggle');
    const mainNav = document.querySelector('.main-nav');
    
    if (menuToggle && mainNav) {
        menuToggle.addEventListener('click', function () {
            mainNav.classList.toggle('active');
            // 切换菜单图标
            const icon = menuToggle.querySelector('i');
            if (icon.classList.contains('fa-bars')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            } else {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });
    }
    
    // 游戏页面全屏按钮
    const fullscreenBtn = document.querySelector('.fullscreen-btn');
    const gameFrame = document.querySelector('.game-frame');
    const gameIframe = document.querySelector('.game-frame iframe');
    
    if (fullscreenBtn && gameFrame) {
        fullscreenBtn.addEventListener('click', function () {
            // 首先尝试让iframe全屏
            if (gameIframe) {
                try {
                    if (gameIframe.requestFullscreen) {
                        gameIframe.requestFullscreen();
                    } else if (gameIframe.mozRequestFullScreen) { // Firefox
                        gameIframe.mozRequestFullScreen();
                    } else if (gameIframe.webkitRequestFullscreen) { // Chrome, Safari and Opera
                        gameIframe.webkitRequestFullscreen();
                    } else if (gameIframe.msRequestFullscreen) { // IE/Edge
                        gameIframe.msRequestFullscreen();
                    }
                } catch (e) {
                    console.log('无法直接全屏iframe, 尝试全屏游戏框架');
                    // 如果iframe全屏失败，则尝试全屏整个游戏框架
                    if (gameFrame.requestFullscreen) {
                        gameFrame.requestFullscreen();
                    } else if (gameFrame.mozRequestFullScreen) {
                        gameFrame.mozRequestFullScreen();
                    } else if (gameFrame.webkitRequestFullscreen) {
                        gameFrame.webkitRequestFullscreen();
                    } else if (gameFrame.msRequestFullscreen) {
                        gameFrame.msRequestFullscreen();
                    }
                }
            }
            // 如果没有iframe元素，则直接全屏游戏框架
            else if (gameFrame) {
                if (gameFrame.requestFullscreen) {
                    gameFrame.requestFullscreen();
                } else if (gameFrame.mozRequestFullScreen) {
                    gameFrame.mozRequestFullScreen();
                } else if (gameFrame.webkitRequestFullscreen) {
                    gameFrame.webkitRequestFullscreen();
                } else if (gameFrame.msRequestFullscreen) {
                    gameFrame.msRequestFullscreen();
                }
            }
        });
    }
    
    // 处理全屏状态改变事件
    document.addEventListener('fullscreenchange', handleFullscreenChange);
    document.addEventListener('webkitfullscreenchange', handleFullscreenChange);
    document.addEventListener('mozfullscreenchange', handleFullscreenChange);
    document.addEventListener('MSFullscreenChange', handleFullscreenChange);
    
    function handleFullscreenChange() {
        const fullscreenElement = document.fullscreenElement || document.webkitFullscreenElement || 
                                 document.mozFullScreenElement || document.msFullscreenElement;
        
        if (fullscreenBtn) {
            if (fullscreenElement) {
                // 全屏模式下
                fullscreenBtn.innerHTML = '<i class="fas fa-compress"></i> Exit Fullscreen';
            } else {
                // 非全屏模式
                fullscreenBtn.innerHTML = '<i class="fas fa-expand"></i> Fullscreen';
            }
        }
    }
    
    // 游戏喜欢按钮
    const likeBtn = document.querySelector('.like-btn');
    if (likeBtn) {
        likeBtn.addEventListener('click', function () {
            // 切换按钮图标和文本
            const icon = likeBtn.querySelector('i');
            if (icon.classList.contains('far')) {
                icon.classList.remove('far');
                icon.classList.add('fas');
                likeBtn.classList.add('liked');
                // 这里可以发送请求到服务器记录用户喜欢的游戏
                showNotification('感谢您的喜欢！');
            } else {
                icon.classList.remove('fas');
                icon.classList.add('far');
                likeBtn.classList.remove('liked');
                // 这里可以发送请求到服务器取消用户喜欢的游戏
                showNotification('已取消喜欢。');
            }
        });
    }
    
    // 游戏分享按钮
    const shareBtn = document.querySelector('.share-btn');
    if (shareBtn) {
        shareBtn.addEventListener('click', function () {
            // 检查网页分享API是否可用
            if (navigator.share) {
                navigator.share({
                    title: document.title,
                    url: window.location.href
                })
                .then(() => showNotification('分享成功！'))
                .catch(error => console.log('分享失败:', error));
            } else {
                // 如果不支持分享API，则复制链接到剪贴板
                const url = window.location.href;
                const tempInput = document.createElement('input');
                tempInput.value = url;
                document.body.appendChild(tempInput);
                tempInput.select();
                document.execCommand('copy');
                document.body.removeChild(tempInput);
                showNotification('链接已复制到剪贴板！');
            }
        });
    }
    
    // 游戏问题报告按钮
    const reportBtn = document.querySelector('.report-btn');
    if (reportBtn) {
        reportBtn.addEventListener('click', function () {
            // 显示报告问题表单
            // 这里可以使用模态框或其他方式显示表单
            alert('请告诉我们您遇到的问题，我们会尽快处理！');
        });
    }
    
    // 通知功能
    function showNotification(message, type = 'success') {
        // 创建通知元素
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        
        // 添加到页面
        document.body.appendChild(notification);
        
        // 显示通知
        setTimeout(() => {
            notification.classList.add('show');
        }, 10);
        
        // 几秒后隐藏通知
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }
    
    // 添加全局样式
    const style = document.createElement('style');
    style.textContent = `
        .notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 15px 20px;
            background-color: #2ecc71;
            color: white;
            border-radius: 4px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
            transform: translateY(100px);
            opacity: 0;
            transition: all 0.3s ease;
            z-index: 1000;
        }
        
        .notification.show {
            transform: translateY(0);
            opacity: 1;
        }
        
        .notification.error {
            background-color: #e74c3c;
        }
        
        .notification.warning {
            background-color: #f39c12;
        }
        
        .like-btn.liked {
            background-color: #2ecc71;
            color: white;
        }
    `;
    document.head.appendChild(style);
    
    // 添加滚动动画
    const scrollElements = document.querySelectorAll('.game-card, .category-card');
    
    // 如果页面有滚动元素，才初始化滚动动画
    if (scrollElements.length > 0) {
        const elementInView = (el, percentageScroll = 100) => {
            const elementTop = el.getBoundingClientRect().top;
            return (
                elementTop <= 
                ((window.innerHeight || document.documentElement.clientHeight) * (percentageScroll/100))
            );
        };
        
        const displayScrollElement = (element) => {
            element.classList.add('scrolled');
        };
        
        const hideScrollElement = (element) => {
            element.classList.remove('scrolled');
        };
        
        const handleScrollAnimation = () => {
            scrollElements.forEach((el) => {
                if (elementInView(el, 90)) {
                    displayScrollElement(el);
                } else {
                    hideScrollElement(el);
                }
            });
        };
        
        // 添加CSS动画样式
        const animStyle = document.createElement('style');
        animStyle.textContent = `
            .game-card, .category-card {
                opacity: 0;
                transform: translateY(20px);
                transition: opacity 0.5s ease, transform 0.5s ease;
            }
            
            .game-card.scrolled, .category-card.scrolled {
                opacity: 1;
                transform: translateY(0);
            }
        `;
        document.head.appendChild(animStyle);
        
        // 初始检查
        handleScrollAnimation();
        
        // 使用节流函数优化滚动监听
        const throttle = (func, delay) => {
            let lastCall = 0;
            return (...args) => {
                const now = new Date().getTime();
                if (now - lastCall < delay) return;
                lastCall = now;
                return func(...args);
            };
        };
        
        // 滚动监听
        window.addEventListener('scroll', throttle(() => {
            handleScrollAnimation();
        }, 100));
    }

    /*
    // 游戏分类导航功能
    document.addEventListener('DOMContentLoaded', function() {
        // 获取URL参数
        const getUrlParameter = function(name) {
            name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
            const regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
            const results = regex.exec(location.search);
            return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
        };
        
        // 处理games.html页面的URL参数筛选
        const currentPage = window.location.pathname.split('/').pop();
        if (currentPage === 'games.html') {
            const currentCategory = getUrlParameter('category');
            if (currentCategory) {
                const categoryLinks = document.querySelectorAll('.category-link');
                categoryLinks.forEach(link => {
                    link.classList.remove('active');
                    if (link.getAttribute('data-category') === currentCategory) {
                        link.classList.add('active');
                    }
                });
                
                // 筛选游戏卡片
                const gameCards = document.querySelectorAll('.game-card');
                if (gameCards.length > 0 && currentCategory !== 'all') {
                    gameCards.forEach(card => {
                        if (card.getAttribute('data-category')) {
                            const cardCategories = card.getAttribute('data-category').split(' ');
                            if (cardCategories.includes(currentCategory)) {
                                card.style.display = 'block';
                            } else {
                                card.style.display = 'none';
                            }
                        }
                    });
                }
            }
        }
        
        // 分类导航的筛选功能 (任何页面都可使用)
        const categoryLinks = document.querySelectorAll('.category-link');
        
        categoryLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                // 更新active状态
                categoryLinks.forEach(l => l.classList.remove('active'));
                this.classList.add('active');
                
                // 获取分类
                const category = this.getAttribute('data-category');
                
                // 筛选游戏
                const gameCards = document.querySelectorAll('.game-card');
                if (category === 'all') {
                    gameCards.forEach(card => {
                        card.style.display = 'block';
                    });
                } else {
                    gameCards.forEach(card => {
                        if (card.getAttribute('data-category')) {
                            const cardCategories = card.getAttribute('data-category').split(' ');
                            if (cardCategories.includes(category)) {
                                card.style.display = 'block';
                            } else {
                                card.style.display = 'none';
                            }
                        }
                    });
                }
            });
        });
    });
    
    // 无限滚动加载功能
    const infiniteScroll = () => {
        // 游戏列表容器
        const gamesGrid = document.querySelector('.games-grid');
        // 当前显示的分类
        let currentCategory = 'all';
        // 跟踪加载的游戏页码
        let page = 1;
        // 是否正在加载
        let isLoading = false;
        // 示例游戏数据
        const sampleGames = [
            { title: 'Traffic Rider', category: 'racing', rating: '4.8', image: 'images/games/1743257697685.jpg', url: 'games/traffic-rider.html' },
            { title: 'Tetris', category: 'puzzle', rating: '4.9', image: 'images/game-placeholder.jpg', url: 'games/tetris.html' },
            { title: 'Sky Riders', category: 'action', rating: '4.5', image: 'images/game-placeholder.jpg', url: 'games/sky-riders.html' },
            { title: 'Platformer', category: 'adventure', rating: '4.2', image: 'images/game-placeholder.jpg', url: 'games/platformer.html' },
            { title: 'Match 3', category: 'puzzle', rating: '4.7', image: 'images/game-placeholder.jpg', url: 'games/match3.html' },
            { title: 'Basketball', category: 'sports', rating: '4.4', image: 'images/game-placeholder.jpg', url: 'games/basketball.html' },
            { title: 'Racing Game', category: 'racing', rating: '4.6', image: 'images/game-placeholder.jpg', url: 'games/racing.html' },
            { title: 'Fishing Master', category: 'casual', rating: '4.1', image: 'images/game-placeholder.jpg', url: 'games/fishing.html' }
        ];
        
        // 获取被选中的分类
        const getActiveCategory = () => {
            const activeLink = document.querySelector('.category-link.active');
            return activeLink ? activeLink.getAttribute('data-category') : 'all';
        };
        
        // 加载更多游戏
        const loadMoreGames = () => {
            if (!gamesGrid || isLoading) return;
            
            isLoading = true;
            currentCategory = getActiveCategory();
            
            // 显示加载指示器
            const loader = document.createElement('div');
            loader.className = 'loader';
            loader.innerHTML = '<div class="spinner"></div>';
            document.querySelector('.game-section .container').appendChild(loader);
            
            // 模拟网络请求延迟
            setTimeout(() => {
                // 创建新的游戏卡片
                let newGamesHTML = '';
                
                sampleGames.forEach(game => {
                    // 如果是"all"分类或者游戏的分类匹配当前选中的分类
                    if (currentCategory === 'all' || game.category === currentCategory) {
                        newGamesHTML += `
                        <div class="game-card" data-category="${game.category}">
                            <a href="${game.url}">
                                <div class="game-thumbnail">
                                    <img src="${game.image}" alt="${game.title}">
                                    <div class="game-overlay">
                                        <div class="play-button">
                                            <i class="fas fa-play"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="game-info">
                                    <h3>${game.title}</h3>
                                    <div class="game-meta">
                                        <span class="game-category">${game.category.charAt(0).toUpperCase() + game.category.slice(1)}</span>
                                        <span class="game-rating"><i class="fas fa-star"></i> ${game.rating}</span>
                                    </div>
                                </div>
                            </a>
                        </div>
                        `;
                    }
                });
                
                // 添加新游戏卡片到网格
                gamesGrid.insertAdjacentHTML('beforeend', newGamesHTML);
                
                // 移除加载指示器
                document.querySelector('.loader').remove();
                isLoading = false;
                page++;
                
                // 为新加载的游戏卡片添加滚动动画效果
                const newCards = gamesGrid.querySelectorAll('.game-card:not(.scrolled)');
                newCards.forEach(card => {
                    setTimeout(() => {
                        card.classList.add('scrolled');
                    }, 100);
                });
            }, 800); // 模拟加载延迟
        };
        
        // 检测滚动到底部
        const handleScroll = () => {
            const scrollHeight = document.documentElement.scrollHeight;
            const scrollTop = window.scrollY;
            const clientHeight = window.innerHeight;
            
            // 当滚动到页面底部时加载更多游戏
            if (scrollTop + clientHeight >= scrollHeight - 300 && !isLoading) {
                loadMoreGames();
            }
        };
        
        // 监听滚动事件
        window.addEventListener('scroll', handleScroll);
        
        // 加载指示器样式
        const loaderStyle = document.createElement('style');
        loaderStyle.textContent = `
            .loader {
                text-align: center;
                padding: 20px;
                width: 100%;
                clear: both;
            }
            
            .spinner {
                width: 40px;
                height: 40px;
                margin: 0 auto;
                border: 4px solid rgba(255, 255, 255, 0.1);
                border-radius: 50%;
                border-top: 4px solid var(--primary-color);
                animation: spin 1s linear infinite;
            }
            
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        `;
        document.head.appendChild(loaderStyle);
        
        // 分类切换时重置加载状态
        document.querySelectorAll('.category-link').forEach(link => {
            link.addEventListener('click', function() {
                // 重置页码和加载状态
                page = 1;
                isLoading = false;
                // 更新当前分类
                currentCategory = this.getAttribute('data-category');
            });
        });
    };
    
    // 初始化无限滚动
    infiniteScroll();
    */
}); 