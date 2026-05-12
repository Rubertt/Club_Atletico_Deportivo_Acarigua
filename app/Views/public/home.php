<section class="hero">
    <?php $carouselSlidesId = 'hero-slides'; include view_path('partials.hero_carousel_background'); ?>
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

<section class="section section--history" id="nuestra-historia">
    <h2 class="section__title">Nuestra Historia</h2>
    <p class="section__subtitle">De movimiento social a escuela de formación deportiva</p>
    <div class="history-grid">
        <article class="history-card">
            <div class="history-card__icon" aria-hidden="true">⛪</div>
            <h3>Nuestros Inicios</h3>
            <p>
                Fundado en junio 2019 como «Movimiento de Atletas Cristianos» por el Prof. Carlos
                Pérez, iniciando como proyecto social para alejar a los jóvenes de los vicios y la
                delincuencia durante la pandemia.
            </p>
        </article>
        <article class="history-card">
            <div class="history-card__icon" aria-hidden="true">🏛️</div>
            <h3>Evolución Institucional</h3>
            <p>
                El 19/09/2021 se convierte en Club Atlético Deportivo Acarigua, registrándose legalmente
                el 15/11/2022. Hoy contamos con junta directiva, consejo de honor y asamblea de padres.
            </p>
        </article>
        <article class="history-card">
            <div class="history-card__icon" aria-hidden="true">🎯</div>
            <h3>Misión</h3>
            <p>
                Formar atletas profesionales altamente capacitados física, motriz y cognitivamente, con
                principios cristianos que gerencien su desarrollo humanístico para la excelencia deportiva.
            </p>
        </article>
        <article class="history-card">
            <div class="history-card__icon" aria-hidden="true">👁️</div>
            <h3>Visión</h3>
            <p>
                Ser una institución modelo de impacto social, constituida con fundamentos cristianos,
                inspirando a atletas en su desarrollo integral y validando su potencial deportivo.
            </p>
        </article>
        <article class="history-card">
            <div class="history-card__icon" aria-hidden="true">🤝</div>
            <h3>Alianzas</h3>
            <p>
                Trabajamos en convenio con la U.P.T.P Juan de Jesús Montilla, utilizando sus instalaciones
                deportivas y desarrollando proyectos de mejoras continuas para beneficio de la comunidad.
            </p>
        </article>
        <article class="history-card">
            <div class="history-card__icon" aria-hidden="true">📊</div>
            <h3>Impacto Social</h3>
            <p>
                Capacitamos mensualmente a más de 250 personas entre niños, adolescentes y adultos,
                fortaleciendo el núcleo familiar y desarrollando el potencial deportivo del municipio Páez.
            </p>
        </article>
    </div>
</section>

<section class="home-stats" aria-label="Cifras del club">
    <div class="home-stats__inner">
        <div class="home-stats__item">
            <div class="home-stats__value">250+</div>
            <div class="home-stats__label">Personas Beneficiadas</div>
        </div>
        <div class="home-stats__item">
            <div class="home-stats__value">2019</div>
            <div class="home-stats__label">Año de Fundación</div>
        </div>
        <div class="home-stats__item">
            <div class="home-stats__value">60</div>
            <div class="home-stats__label">Miembros Iniciales</div>
        </div>
    </div>
</section>
