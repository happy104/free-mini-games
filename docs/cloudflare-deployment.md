# Cloudflare Pages部署指南

本文档提供将Free Mini Games 2部署到Cloudflare Pages的详细说明。

## 准备工作

1. 确保您有一个[Cloudflare账户](https://dash.cloudflare.com/sign-up)
2. 确保您的代码已推送到Git仓库(GitHub, GitLab等)

## 部署步骤

### 1. 在Cloudflare中创建项目

1. 登录[Cloudflare Dashboard](https://dash.cloudflare.com/)
2. 点击左侧导航中的**Pages**
3. 点击**Create a project**按钮
4. 选择**Connect to Git**
5. 授权并选择包含Free Mini Games 2代码的仓库
6. 点击**Begin setup**

### 2. 配置构建设置

1. **项目名称**：输入您的项目名称，例如`free-mini-games`
2. **生产分支**：选择您的主分支(通常是`main`或`master`)
3. **框架预设**：选择**None**（我们不使用预定义框架）
4. **构建设置**：保留为空（我们的项目是静态HTML）
5. 点击**Save and Deploy**按钮

### 3. 等待部署完成

Cloudflare将自动部署您的站点。等待构建和部署过程完成。

### 4. 配置环境变量（可选）

如果您需要设置任何环境变量：

1. 在项目页面中，转到**Settings** > **Environment variables**
2. 添加所需的环境变量
3. 点击**Save**按钮
4. 重新部署项目以应用新的环境变量

## 注意事项

### PHP文件

Cloudflare Pages是静态网站托管服务，无法执行PHP文件。我们已经做了以下调整以适应这一限制：

1. 将`js/get-games-data.php`的调用改为直接获取`js/games-data.json`
2. 添加了Cloudflare特定的`_headers`和`_redirects`文件
3. 创建了`admin/cloudflare-update.js`作为游戏数据更新工具的替代方案

### 管理功能

在Cloudflare静态环境中，管理功能有所限制：

1. **更新游戏数据**：只能通过手动更新`games-data.json`文件并重新部署
2. **添加新游戏**：必须手动添加游戏HTML文件并更新游戏数据

如需完整的管理功能，可以考虑：

1. 使用[Cloudflare Workers](https://workers.cloudflare.com/)创建无服务器API
2. 使用[Cloudflare KV](https://www.cloudflare.com/products/workers-kv/)存储数据
3. 配置[Cloudflare R2](https://www.cloudflare.com/products/r2/)存储图片文件

## 疑难解答

### 加载游戏数据错误

如果遇到"Error loading game data"错误：

1. 检查浏览器控制台中的具体错误消息
2. 确认`js/games-data.json`文件存在并格式正确
3. 验证Cloudflare的缓存设置未阻止JSON文件加载
4. 尝试清除浏览器缓存后重新加载

### 自定义域名

要将自定义域名绑定到您的Cloudflare Pages站点：

1. 在项目页面中，转到**Settings** > **Custom domains**
2. 点击**Set up a custom domain**
3. 输入您的域名并按照提示完成设置

## 更新站点

要更新已部署的站点：

1. 将更改推送到Git仓库
2. Cloudflare将自动检测更改并重新部署
3. 您可以在项目的**Deployments**选项卡中监视部署进度 