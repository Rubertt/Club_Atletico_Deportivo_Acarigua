<section class="hero">
    <div class="hero__carousel" id="heroCarousel">
        <?php for ($i = 1; $i <= 7; $i++): ?>
            <div class="carousel-slide <?= $i === 1 ? 'active' : '' ?>" 
                 style="background-image: linear-gradient(rgba(220,38,38,0.65), rgba(17,24,39,0.85)), url('<?= e(asset('img/carrusel/' . $i . '.jpeg')) ?>');<?= $i === 2 ? ' background-position: center top;' : '' ?>">
            </div>
        <?php endfor; ?>
    </div>
    
    <div class="hero__content">
        <span class="hero__badge">Fundado 2019</span>
        <h1 class="hero__title">Club Atlético<br>Deportivo Acarigua</h1>
        <p class="hero__subtitle">
            Formando atletas con valores cristianos, disciplina y excelencia
            deportiva. Más de 250 personas beneficiadas en el municipio Páez.
        </p>
        <div class="hero__actions">
            <a href="<?= e(url('/login')) ?>" class="btn btn-primary btn-lg">Acceder al Sistema</a>
            <a href="<?= e(url('/nosotros')) ?>" class="btn btn-outline btn-lg">Conocer Más</a>
        </div>
    </div>
</section>

<style>
.hero__carousel {
    position: absolute;
    inset: 0;
    z-index: 1;
    overflow: hidden;
    background-color: #111;
}
.carousel-slide {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center;
    opacity: 0;
    transition: opacity 1.5s ease-in-out;
}
.carousel-slide.active {
    opacity: 1;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const slides = document.querySelectorAll('.carousel-slide');
    let currentSlide = 0;
    
    if(slides.length > 0) {
        setInterval(() => {
            slides[currentSlide].classList.remove('active');
            currentSlide = (currentSlide + 1) % slides.length;
            slides[currentSlide].classList.add('active');
        }, 5000); // Cambia de imagen cada 5 segundos
    }
});
</script>

<section class="section" id="caracteristicas">
    <h2 class="section__title">Gestión deportiva integral</h2>
    <p class="section__subtitle">
        Una solución moderna y robusta para el seguimiento técnico, médico, físico y administrativo 
        del talento deportivo del club, potenciada con analíticas y control de seguridad avanzado.
    </p>
    <div class="feature-grid">
        <div class="feature-card">
            <div class="feature-card__icon">👥</div>
            <h3>Atletas y Representantes</h3>
            <p>Expediente digital completo con datos personales, tutor legal (obligatorio para menores) y dirección exacta con cascada geográfica de Venezuela.</p>
        </div>
        <div class="feature-card">
            <div class="feature-card__icon">📈</div>
            <h3>Antropometría y ECharts</h3>
            <p>Monitoreo físico (peso, talla, envergadura) y cálculo automático de IMC, visualizado en elegantes gráficos interactivos de evolución temporal.</p>
        </div>
        <div class="feature-card">
            <div class="feature-card__icon">🏥</div>
            <h3>Control Clínico Completo</h3>
            <p>Historial médico de salud y discapacidades (con paginación fija a 5 registros), además de control y registro de consultas y tratamientos.</p>
        </div>
        <div class="feature-card">
            <div class="feature-card__icon">📋</div>
            <h3>Asistencias y Convocatorias</h3>
            <p>Pase de lista diario por categorías, planificación de convocatorias a partidos y entrenamientos, y análisis de asistencia del jugador.</p>
        </div>
        <div class="feature-card">
            <div class="feature-card__icon">🛡️</div>
            <h3>Seguridad Multi-Rol (RBAC)</h3>
            <p>Control de acceso seguro basado en 5 roles de usuario (Superusuario, Admin, Directivo, Entrenador y Médico) con protección CSRF y rate limit.</p>
        </div>
        <div class="feature-card">
            <div class="feature-card__icon">📄</div>
            <h3>Reportes Oficiales</h3>
            <p>Generación automatizada de fichas técnicas individuales en formato PDF (TCPDF) y reportes de plantel listos para impresión oficial.</p>
        </div>
    </div>
</section>
