    </main>
    
    <!-- 页脚 -->
    <footer class="glass mt-12 px-6 py-8 text-center">
        <p class="opacity-70">
            <?= e($settings->get('footer_text', 'Powered by MyGlassBlog PHP')) ?>
        </p>
        <p class="mt-2 opacity-50 text-sm">
            © <?= date('Y') ?> <?= e($settings->get('site_author')) ?>
        </p>
        <?php 
        $icpNumber = $settings->get('icp_number', '');
        if (!empty($icpNumber)): 
        ?>
            <p class="mt-2 opacity-40 text-xs">
                <a href="https://beian.miit.gov.cn/" target="_blank" rel="nofollow" class="hover:opacity-70 transition-opacity">
                    <?= e($icpNumber) ?>
                </a>
            </p>
        <?php endif; ?>
    </footer>
    
    <!-- 暗色模式脚本 -->
    <script>
        // 检查本地存储或系统偏好
        if (localStorage.getItem('darkMode') === 'true' || 
            (!localStorage.getItem('darkMode') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
            document.getElementById('darkIcon').textContent = '☀️';
        }
        
        function toggleDarkMode() {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('darkMode', isDark);
            document.getElementById('darkIcon').textContent = isDark ? '☀️' : '🌙';
        }
    </script>
</body>
</html>
