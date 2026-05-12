<?php
declare(strict_types=1);
/** @var string $carouselSlidesId id único del contenedor .hero__slides (p. ej. hero-slides, login-hero-slides) */
$carouselSlidesId = $carouselSlidesId ?? 'hero-slides';

$heroSlidePaths = [
    'img/hero-carousel/slide-01.png',
    'img/hero-carousel/slide-02.png',
    'img/hero-carousel/slide-03.png',
    'img/hero-carousel/slide-04.png',
    'img/hero-carousel/slide-05.png',
    'img/hero-carousel/slide-06.png',
    'img/hero-carousel/slide-07.png',
    'img/hero-carousel/slide-08.png',
    'img/hero-carousel/slide-09.png',
];
?>
<div class="hero__bg" aria-hidden="true">
    <div class="hero__slides" id="<?= e($carouselSlidesId) ?>">
        <?php foreach ($heroSlidePaths as $idx => $rel): ?>
            <?php $slideUrl = asset($rel); ?>
            <img
                class="hero__slide<?= $idx === 0 ? ' is-active' : '' ?>"
                src="<?= e($slideUrl) ?>"
                alt=""
                decoding="async"
                <?= $idx === 0 ? 'fetchpriority="high"' : 'loading="lazy"' ?>
            >
        <?php endforeach; ?>
    </div>
    <div class="hero__overlay"></div>
</div>
<script>
(function () {
    var id = <?= json_encode($carouselSlidesId, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var root = document.getElementById(id);
    if (!root) return;
    var slides = root.querySelectorAll('.hero__slide');
    if (slides.length < 2) return;
    var i = 0;
    var intervalMs = 5000;
    window.setInterval(function () {
        slides[i].classList.remove('is-active');
        i = (i + 1) % slides.length;
        slides[i].classList.add('is-active');
    }, intervalMs);
})();
</script>
