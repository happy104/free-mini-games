/**
 * Free Mini Games - Game Detail Navigation
 * 处理游戏详情页导航交互
 * 2024-04-02
 */

// 等待DOM加载完成
document.addEventListener('DOMContentLoaded', function() {
    initGameNavigation();
});

// 初始化游戏详情页导航
function initGameNavigation() {
    setupMobileMenu();
    highlightCurrentCategory();
    setupNavigationScroll();
}

// 设置移动端菜单
function setupMobileMenu() {
    const menuToggle = document.querySelector('.menu-toggle');
    if (!menuToggle) return;
    
    const header = document.querySelector('.site-header');
    
    menuToggle.addEventListener('click', function(e) {
        e.stopPropagation();
        header.classList.toggle('menu-open');
        
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
    
    // 点击页面其他区域关闭菜单
    document.addEventListener('click', function(e) {
        if (header.classList.contains('menu-open') && !header.contains(e.target)) {
            header.classList.remove('menu-open');
            
            // 恢复菜单图标
            const icon = menuToggle.querySelector('i');
            icon.classList.remove('fa-times');
            icon.classList.add('fa-bars');
        }
    });
}

// 高亮当前分类
function highlightCurrentCategory() {
    // 获取当前游戏类别
    const breadcrumbs = document.querySelector('.breadcrumbs');
    if (!breadcrumbs) return;
    
    // 从面包屑获取当前分类
    const categoryLinks = breadcrumbs.querySelectorAll('a');
    let currentCategory = '';
    
    categoryLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href && href.includes('category=')) {
            currentCategory = href.split('category=')[1];
        }
    });
    
    if (!currentCategory) return;
    
    // 高亮导航中的当前分类
    const categoryButtons = document.querySelectorAll('.category-btn');
    categoryButtons.forEach(button => {
        const href = button.getAttribute('href');
        if (href && href.includes(`category=${currentCategory}`)) {
            button.classList.add('active');
        } else {
            button.classList.remove('active');
        }
    });
}

// 设置导航滚动
function setupNavigationScroll() {
    const categoryNav = document.querySelector('.categories-list');
    if (!categoryNav) return;
    
    // 找到当前活跃的分类按钮
    const activeButton = categoryNav.querySelector('.active');
    if (!activeButton) return;
    
    // 将活跃按钮滚动到可见区域
    setTimeout(() => {
        const navRect = categoryNav.getBoundingClientRect();
        const buttonRect = activeButton.getBoundingClientRect();
        
        // 计算滚动位置
        const scrollLeft = buttonRect.left - navRect.left - (navRect.width / 2) + (buttonRect.width / 2);
        
        // 平滑滚动
        categoryNav.scrollTo({
            left: scrollLeft,
            behavior: 'smooth'
        });
    }, 300);
} 