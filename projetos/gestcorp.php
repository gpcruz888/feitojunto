<?php
$pageTitle = 'Gestcorp · ECA/USP — feito.';
$pageDescription = 'Case de design de interface e desenvolvimento do site do Gestcorp, curso de especialização em Comunicação Organizacional e Relações Públicas da ECA/USP.';
$isHome = false;

require_once __DIR__ . '/../includes/head.php';
require_once __DIR__ . '/../includes/header.php';
?>

<main class="gestcorp-case">
  <section class="gestcorp-case__opening" aria-labelledby="gestcorp-titulo">
    <div class="container">
      <div class="gestcorp-case__intro">
        <div class="gestcorp-copy">
          <h1 id="gestcorp-titulo" class="gestcorp-case__title">gestcorp<span>.</span></h1>
          <p class="gestcorp-case__subtitle">um novo site para o curso de especialização da eca/usp.</p>
          <p>da organização das informações ao design da interface e ao desenvolvimento web, um projeto pensado para apresentar o Gestcorp, seus professores e sua estrutura acadêmica.</p>
        </div>
        <dl class="gestcorp-case__details">
          <div><dt>projeto</dt><dd>Gestcorp · ECA/USP</dd></div>
          <div><dt>categoria</dt><dd>website</dd></div>
          <div><dt>atuação</dt><dd>UI Design · Desenvolvimento Web</dd></div>
        </dl>
      </div>
      <img class="gestcorp-image" src="/assets/projetos/gestcorp/abertura.webp"
        srcset="/assets/projetos/gestcorp/abertura-720.webp 720w, /assets/projetos/gestcorp/abertura-1200.webp 1200w, /assets/projetos/gestcorp/abertura.webp 1920w"
        sizes="(max-width: 768px) calc(100vw - 48px), (max-width: 1024px) calc(100vw - 64px), (max-width: 1240px) calc(100vw - 80px), 1160px"
        width="1920" height="1032" alt="Página inicial do Gestcorp com a navegação da ECA-USP e uma fotografia em sala de aula"
        fetchpriority="high" decoding="async">
    </div>
  </section>

  <section class="gestcorp-case__section gestcorp-case__section--light" aria-labelledby="gestcorp-sobre">
    <div class="container">
      <div class="gestcorp-copy gestcorp-copy--reading">
        <h2 id="gestcorp-sobre">sobre o projeto.</h2>
        <p>O Gestcorp é um curso de especialização da Escola de Comunicações e Artes da Universidade de São Paulo, voltado à formação de profissionais em Comunicação Organizacional e Relações Públicas.</p>
        <p>O site reúne informações sobre o curso, disciplinas, professores e processo seletivo.</p>
        <p>Na feito., trabalhamos na criação do site que reúne essas informações e apresenta o Gestcorp para quem deseja conhecer a especialização.</p>
      </div>
    </div>
  </section>

  <section class="gestcorp-case__section gestcorp-case__section--soft" aria-labelledby="gestcorp-estrutura">
    <div class="container gestcorp-case__structure">
      <div class="gestcorp-copy">
        <h2 id="gestcorp-estrutura">uma página, diferentes informações.</h2>
        <p>O projeto reúne conteúdos para diferentes momentos da navegação.</p>
        <p>Quem está conhecendo o curso precisa entender sua proposta. Quem já tem interesse pode procurar informações mais específicas sobre disciplinas, professores e inscrições.</p>
        <p>A estrutura organiza esses assuntos em seções, permitindo que cada informação tenha seu próprio espaço.</p>
      </div>
      <img class="gestcorp-image" src="/assets/projetos/gestcorp/estrutura.webp" width="1440" height="2568"
        alt="Sequência de seções que apresenta o curso, sua relação com a USP e a ECA e o perfil de quem pode cursá-lo"
        loading="lazy" decoding="async">
    </div>
  </section>

  <section class="gestcorp-case__section" aria-labelledby="gestcorp-pessoas">
    <div class="container">
      <div class="gestcorp-copy gestcorp-enter">
        <h2 id="gestcorp-pessoas">destaque para quem faz parte do curso.</h2>
        <p>O corpo docente tem uma seção dedicada a apresentar os professores, com nomes e fotografias. Logo abaixo, a trajetória do Gestcorp reúne marcos do curso e publicações ligadas à sua produção acadêmica.</p>
      </div>
      <div class="gestcorp-case__people">
        <img class="gestcorp-image" src="/assets/projetos/gestcorp/professores.webp" width="1600" height="740"
          alt="Seção do corpo docente com retratos, nomes e links para os currículos dos professores"
          loading="lazy" decoding="async">
        <img class="gestcorp-image" src="/assets/projetos/gestcorp/trajetoria.webp" width="1320" height="800"
          alt="Seção sobre a trajetória do Gestcorp, com marcos do curso, a revista Organicom e livros do corpo docente"
          loading="lazy" decoding="async">
      </div>
    </div>
  </section>

  <section class="gestcorp-case__section gestcorp-case__section--soft" aria-labelledby="gestcorp-interface">
    <div class="container">
      <div class="gestcorp-copy">
        <h2 id="gestcorp-interface">conteúdo organizado. interface consistente.</h2>
        <p>Na construção da interface, a hierarquia visual ajuda a separar conteúdos que possuem funções diferentes.</p>
        <p>Títulos, textos, imagens e áreas de navegação são organizados para que a apresentação do curso e as informações acadêmicas possam ser consultadas com facilidade.</p>
        <p>O trabalho reúne design de interface e desenvolvimento web em uma estrutura adaptada aos diferentes dispositivos.</p>
      </div>
      <div class="gestcorp-case__devices">
        <img class="gestcorp-image" src="/assets/projetos/gestcorp/programa.webp" width="1600" height="1045"
          alt="Versão desktop da página do Gestcorp com o programa organizado em módulos e uma lista de disciplinas"
          loading="lazy" decoding="async">
        <img class="gestcorp-image" src="/assets/projetos/gestcorp/mobile.webp" width="585" height="1245"
          alt="Abertura do Gestcorp no celular com menu compacto, título e botões em coluna"
          loading="lazy" decoding="async">
      </div>
    </div>
  </section>

  <section class="gestcorp-case__section gestcorp-case__closing" aria-labelledby="gestcorp-final">
    <div class="container">
      <div class="gestcorp-copy">
        <h2 id="gestcorp-final">gestcorp<span>.</span></h2>
        <p>design e desenvolvimento web para o curso de especialização da eca/usp.</p>
        <div class="gestcorp-actions">
          <a href="https://gestcorp.eca.usp.br/" target="_blank" rel="noopener" class="btn btn--gestcorp">ver site ao vivo</a>
          <a href="/#projetos" class="btn btn--glass">voltar aos projetos</a>
        </div>
      </div>
    </div>
  </section>
</main>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
  <script src="/script.js?v=<?= time(); ?>"></script>
</body>
</html>
