/* Google AdSense广告管理脚本 */
document.addEventListener("DOMContentLoaded", function() {
    // 替换广告占位符
    replaceAdPlaceholders();
});

function replaceAdPlaceholders() {
    // 选择所有广告容器
    const adContainers = document.querySelectorAll(".ad-container");
    
    // 为每个广告容器创建AdSense广告
    adContainers.forEach(function(container, index) {
        // 清除容器中的现有内容
        container.innerHTML = "";
        
        // 创建AdSense广告元素
        const adInsElement = document.createElement("ins");
        adInsElement.className = "adsbygoogle";
        adInsElement.style.display = "block";
        adInsElement.setAttribute("data-ad-client", "ca-pub-2406571508028686"); // 您的发布商ID
        
        // 根据容器类名设置广告尺寸和格式
        if (container.classList.contains("sidebar-ad") || container.parentElement.classList.contains("sidebar-ad")) {
            // 侧边栏广告 - 300x250
            adInsElement.style.width = "300px";
            adInsElement.style.height = "250px";
            adInsElement.setAttribute("data-ad-slot", "1234567890"); // 替换为实际的广告位ID
            adInsElement.setAttribute("data-ad-format", "rectangle");
        } else if (container.classList.contains("game-bottom-ad")) {
            // 游戏页面底部广告 - 728x90
            adInsElement.style.width = "728px";
            adInsElement.style.height = "90px";
            adInsElement.setAttribute("data-ad-slot", "2345678901"); // 替换为实际的广告位ID
            adInsElement.setAttribute("data-ad-format", "horizontal");
        } else {
            // 默认横幅广告 - 728x90
            adInsElement.style.width = "728px";
            adInsElement.style.height = "90px";
            adInsElement.setAttribute("data-ad-slot", "3456789012"); // 替换为实际的广告位ID
            adInsElement.setAttribute("data-ad-format", "horizontal");
        }
        
        // 将广告元素添加到容器
        container.appendChild(adInsElement);
        
        // 加载广告
        try {
            (adsbygoogle = window.adsbygoogle || []).push({});
        } catch (e) {
            console.error("AdSense加载失败", e);
        }
    });
}
