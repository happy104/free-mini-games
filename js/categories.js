/**
 * 游戏分类筛选功能
 * 允许用户点击分类按钮筛选游戏
 */

// 游戏分类过滤器
document.addEventListener('DOMContentLoaded', function() {
    // 获取所有分类按钮
    const categoryButtons = document.querySelectorAll('.category-filter button');
    
    // 当前激活的分类
    let activeCategory = 'all';
    
    // 为每个分类按钮添加点击事件
    categoryButtons.forEach(button => {
        button.addEventListener('click', function() {
            // 获取分类值
            const category = this.getAttribute('data-category');
            
            // 如果已经是当前选中的分类，不做任何操作
            if (category === activeCategory) return;
            
            // 更新激活状态
            categoryButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // 更新当前激活的分类
            activeCategory = category;
            
            // 调用无限滚动脚本中的过滤函数
            if (typeof window.filterGamesByCategory === 'function') {
                window.filterGamesByCategory(category);
            } else {
                console.error('Function not found: filterGamesByCategory');
            }
        });
    });
}); 