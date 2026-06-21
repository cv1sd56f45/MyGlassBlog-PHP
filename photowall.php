<?php
/**
 * MyGlassBlog PHP - 照片墙
 */
require_once __DIR__ . '/includes/functions.php';

$settings = site_config();
$photoModel = new Photo();
$photos = $photoModel->getAll();

require_once __DIR__ . '/templates/header.php';
?>

<h1 class="text-3xl font-bold mb-8 text-center">📷 照片墙</h1>

<?php if (empty($photos)): ?>
    <div class="glass rounded-xl p-8 text-center opacity-70">
        暂无照片
    </div>
<?php else: ?>
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        <?php foreach ($photos as $photo): ?>
            <div class="group relative glass rounded-xl overflow-hidden cursor-pointer" 
                 onclick="openLightbox('<?= e($photo['url']) ?>', '<?= e($photo['title']) ?>')">
                <img src="<?= e($photo['thumb']) ?>" alt="<?= e($photo['title']) ?>" 
                     class="w-full aspect-square object-cover group-hover:scale-105 transition-transform duration-300">
                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-colors flex items-center justify-center">
                    <span class="opacity-0 group-hover:opacity-100 transition-opacity text-white font-medium">
                        <?= e($photo['title']) ?>
                    </span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- 灯箱 -->
<div id="lightbox" class="fixed inset-0 bg-black/90 z-50 hidden items-center justify-center" onclick="closeLightbox()">
    <img id="lightboxImg" src="" alt="" class="max-w-full max-h-full object-contain">
    <button class="absolute top-4 right-4 text-white text-3xl" onclick="closeLightbox()">&times;</button>
</div>

<script>
function openLightbox(url, title) {
    document.getElementById('lightboxImg').src = url;
    document.getElementById('lightbox').classList.remove('hidden');
    document.getElementById('lightbox').classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closeLightbox() {
    document.getElementById('lightbox').classList.add('hidden');
    document.getElementById('lightbox').classList.remove('flex');
    document.body.style.overflow = '';
}
</script>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
