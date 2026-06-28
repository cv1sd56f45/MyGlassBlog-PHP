<?php
/**
 * MyGlassBlog PHP - 站点设置
 */
require_once 'common.php';
admin_header('站点设置');

$settings = site_config();
$message = '';
$error = '';

// 处理保存
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $keys = [
        'site_title', 'site_author', 'site_bio', 'site_avatar',
        'nav_title', 'theme_colors', 'default_cover',
        'social_github', 'social_twitter', 'social_email', 'social_wechat',
        'footer_text', 'posts_per_page', 'chatters_per_page', 'icp_number'
    ];
    
    $success = true;
    foreach ($keys as $key) {
        $value = $_POST[$key] ?? '';
        if (!$settings->set($key, $value)) {
            $success = false;
        }
    }
    
    if ($success) {
        $message = '设置已保存';
    } else {
        $error = '部分设置保存失败';
    }
}
?>

<?php if ($message): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4"><?= e($message) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4"><?= e($error) ?></div>
<?php endif; ?>

<div class="bg-white rounded-xl shadow-sm p-6">
    <form method="POST">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- 基本信息 -->
            <div>
                <h3 class="font-bold text-gray-800 mb-4 text-lg border-b pb-2">基本信息</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">网站标题</label>
                        <input type="text" name="site_title" value="<?= e($settings->get('site_title')) ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">作者名</label>
                        <input type="text" name="site_author" value="<?= e($settings->get('site_author')) ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">个人简介</label>
                        <textarea name="site_bio" rows="2"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"><?= e($settings->get('site_bio')) ?></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">头像URL</label>
                        <input type="text" name="site_avatar" value="<?= e($settings->get('site_avatar')) ?>"
                               placeholder="https://..."
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">导航栏名称</label>
                        <input type="text" name="nav_title" value="<?= e($settings->get('nav_title')) ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>
            
            <!-- 社交链接 -->
            <div>
                <h3 class="font-bold text-gray-800 mb-4 text-lg border-b pb-2">社交链接</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">GitHub</label>
                        <input type="text" name="social_github" value="<?= e($settings->get('social_github')) ?>"
                               placeholder="https://github.com/username"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Twitter</label>
                        <input type="text" name="social_twitter" value="<?= e($settings->get('social_twitter')) ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">邮箱</label>
                        <input type="email" name="social_email" value="<?= e($settings->get('social_email')) ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">微信二维码URL</label>
                        <input type="text" name="social_wechat" value="<?= e($settings->get('social_wechat')) ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>
            
            <!-- 主题设置 -->
            <div>
                <h3 class="font-bold text-gray-800 mb-4 text-lg border-b pb-2">主题设置</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">主题颜色（逗号分隔）</label>
                        <input type="text" name="theme_colors" value="<?= e($settings->get('theme_colors')) ?>"
                               placeholder="#a18cd1,#fbc2eb,#a1c4fd"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-sm text-gray-500 mt-1">建议2-4个颜色，用英文逗号分隔</p>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">默认文章封面</label>
                        <input type="text" name="default_cover" value="<?= e($settings->get('default_cover')) ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>
            
            <!-- 其他设置 -->
            <div>
                <h3 class="font-bold text-gray-800 mb-4 text-lg border-b pb-2">其他设置</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">页脚文字</label>
                        <input type="text" name="footer_text" value="<?= e($settings->get('footer_text')) ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">每页文章数</label>
                        <input type="number" name="posts_per_page" value="<?= e($settings->get('posts_per_page', 10)) ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">每页说说数</label>
                        <input type="number" name="chatters_per_page" value="<?= e($settings->get('chatters_per_page', 20)) ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">ICP备案号</label>
                        <input type="text" name="icp_number" value="<?= e($settings->get('icp_number')) ?>"
                               placeholder="如：京ICP备xxxxxxxx号"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-sm text-gray-500 mt-1">将显示在页面底部</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-6 pt-6 border-t">
            <button type="submit" class="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                <i class="fas fa-save mr-2"></i> 保存设置
            </button>
        </div>
    </form>
</div>

<?php admin_footer(); ?>
