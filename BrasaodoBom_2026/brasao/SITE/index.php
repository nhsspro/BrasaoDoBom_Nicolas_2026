<?php
declare(strict_types=1);
require_once __DIR__ . '/php/funcoes.php';
iniciar_sessao();   // precisa vir antes de qualquer saída HTML (cookie da sessão / token CSRF)

// Conteúdo dinâmico vindo do banco. Se o banco estiver fora do ar, o site continua abrindo.
$promocoes = $depoimentos = [];
try {
    $pdo = db();
    $promocoes   = $pdo->query('SELECT titulo, descricao FROM promocoes WHERE ativo = 1 ORDER BY id')->fetchAll();
    $depoimentos = $pdo->query('SELECT autor, texto FROM depoimentos WHERE aprovado = 1 ORDER BY id')->fetchAll();
} catch (Throwable $ex) {
    error_log('Falha ao carregar conteúdo: ' . $ex->getMessage());
}
$hoje   = date('Y-m-d');
$maxima = date('Y-m-d', strtotime('+' . DIAS_ANTECEDENCIA_MAX . ' days'));
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Churrascaria Brasão do Bom</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">

  <!-- Google Font -->
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Roboto:wght@400;500&display=swap" rel="stylesheet">

  <!-- Estilo personalizado -->
  <link rel="stylesheet" href="css/estilo.css">

  <style>
    /* Adicionando imagem de fundo no banner principal */
    .hero {
      background: url("img/fundo-churrascaria.jpg") center center/cover no-repeat;
      min-height: 100vh;
      position: relative;
    }

    /* Camada escura para leitura do texto */
    .hero::before {
      content: "";
      position: absolute;
      inset: 0;
      background: rgba(0, 0, 0, 0.5);
    }

    .hero .container {
      position: relative;
      z-index: 1;
    }
  </style>
</head>
<body>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top shadow">
    <div class="container">
      <a class="navbar-brand fw-bold text-uppercase text-gold" href="#">Brasão do Bom</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div id="menu" class="collapse navbar-collapse justify-content-end">
        <ul class="navbar-nav">
          <li class="nav-item"><a class="nav-link" href="#sobre">Sobre</a></li>
          <li class="nav-item"><a class="nav-link" href="#eventos">Eventos</a></li>
          <li class="nav-item"><a class="nav-link" href="#galeria">Galeria</a></li>
          <li class="nav-item"><a class="nav-link" href="#depoimentos">Depoimentos</a></li>
          <li class="nav-item"><a class="nav-link" href="#promocoes">Promoções</a></li>
          <li class="nav-item"><a class="nav-link" href="#reservas">Reservas</a></li>
          <li class="nav-item"><a class="nav-link" href="#localizacao">Localização</a></li>
          <li class="nav-item"><a class="nav-link" href="#horarios">Horários</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Hero / Banner -->
  <section class="hero d-flex align-items-center text-center text-light">
    <div class="container">
      <h1 class="display-3 fw-bold text-uppercase text-gold fade-in">Churrascaria Brasão do Bom</h1>
      <p class="lead">Desde 2025 produzindo churrasco da melhor qualidade!</p>
      <a href="#reservas" class="btn btn-gold mt-3 me-2">Reservar mesa</a>
      <a href="#sobre" class="btn btn-outline-light mt-3">Conheça-nos</a>
    </div>
  </section>

  <main class="bg-dark text-light">

 <!-- Sobre -->
<section id="sobre" class="py-5 bg-black text-light">
  <div class="container">
    <div class="row align-items-center">
      
      <!-- Coluna da imagem -->
      <div class="col-md-6 mb-4 mb-md-0 text-center fade-in">
        <img src="img/logochurrascaria.png" 
             alt="Churrascaria Brasão do Bom" 
             class="img-fluid rounded shadow"
             style="max-height: 400px; object-fit: cover;">
      </div>

      <!-- Coluna do texto -->
      <div class="col-md-6">
        <h2 class="section-title text-gold mb-4 text-center text-md-start fade-in">Sobre Nós</h2>

        <p>
          Fundada em 2025, a <strong>Churrascaria Brasão do Bom</strong> nasceu com o propósito de celebrar o verdadeiro sabor do churrasco brasileiro. 
          Unindo tradição, qualidade e hospitalidade, oferecemos uma experiência gastronômica que vai muito além da refeição: é um momento de prazer, 
          encontro e celebração da boa comida.
        </p>

        <p>
          Trabalhamos com <strong>carnes nobres cuidadosamente selecionadas</strong>, grelhadas na brasa por mestres churrasqueiros que respeitam o ponto 
          e o sabor de cada corte. Nossos acompanhamentos são preparados com ingredientes frescos, valorizando o sabor caseiro e a autenticidade de cada prato.
        </p>

        <p>
          Nosso ambiente foi pensado para acolher todos os públicos — famílias, amigos e empresas — em um espaço que combina 
          <strong>sofisticação, conforto e tradição gaúcha</strong>. A decoração rústica e elegante cria o cenário ideal para saborear o melhor do churrasco, 
          enquanto nossa equipe garante um atendimento atencioso e cordial.
        </p>

        <p>
          Na <strong>Churrascaria Brasão do Bom</strong>, acreditamos que o verdadeiro churrasco é uma arte que une pessoas. 
          Cada detalhe é planejado para proporcionar uma experiência inesquecível — do aroma da brasa ao último brinde à mesa.
        </p>

        <p class="text-gold mt-3 fade-in"><em>Churrascaria Brasão do Bom — Tradição e sabor que marcam momentos.</em></p>
      </div>

    </div>
  </div>
</section>

    <!-- Eventos -->
    <section id="eventos" class="py-5 bg-black">
      <div class="container text-center">
        <h2 class="section-title text-gold fade-in">Eventos e Pacotes</h2>
        <p class="mt-3">Temos pacotes exclusivos para aniversários, casamentos e confraternizações empresariais.</p>

        <div class="row mt-4 g-4">
          <div class="col-md-4">
            <div class="card bg-dark text-light border-0 shadow-sm">
              <div class="card-body">
                <h5 class="card-title text-gold">Pacote Executivo</h5>
                <p>Ideal para eventos corporativos, com jantar sofisticado e serviço personalizado.</p>
              </div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="card bg-dark text-light border-0 shadow-sm">
              <div class="card-body">
                <h5 class="card-title text-gold">Pacote Familiar</h5>
                <p>Perfeito para aniversários e reuniões com quem você ama.</p>
              </div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="card bg-dark text-light border-0 shadow-sm">
              <div class="card-body">
                <h5 class="card-title text-gold">Pacote Premium</h5>
                <p>Para celebrações exclusivas, com menu personalizado e atendimento completo.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Galeria -->
    <section id="galeria" class="py-5">
      <div class="container text-center">
        <h2 class="section-title text-gold fade-in">Galeria</h2>
        <div class="row mt-4 g-3">
          <div class="col-md-4"><img src="img/churrasco1.jpg" class="img-fluid rounded shadow-sm"></div>
          <div class="col-md-4"><img src="img/churrasco2.jpg" class="img-fluid rounded shadow-sm"></div>
          <div class="col-md-4"><img src="img/churrasco3.jpg" class="img-fluid rounded shadow-sm"></div>
          <div class="col-md-4"><img src="img/churrasco4.jpg" class="img-fluid rounded shadow-sm"></div>
          <div class="col-md-4"><img src="img/churrasco5.jpg" class="img-fluid rounded shadow-sm"></div>
          <div class="col-md-4"><img src="img/churrasco6.jpeg" class="img-fluid rounded shadow-sm"></div>
        </div>
      </div>
    </section>

    <!-- Depoimentos -->
    <section id="depoimentos" class="py-5 bg-black">
      <div class="container text-center">
        <h2 class="section-title text-gold fade-in">Depoimentos</h2>
        <div class="mt-4">
          <?php foreach ($depoimentos as $dep): ?>
            <p><em>"<?= e($dep['texto']) ?>"</em> – <?= e($dep['autor']) ?></p>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <!-- Promoções -->
    <section id="promocoes" class="py-5">
      <div class="container text-center">
        <h2 class="section-title text-gold fade-in">Promoções</h2>
        <div class="row mt-4 g-4">
          <?php foreach ($promocoes as $p): ?>
            <div class="col-md-4">
              <div class="promo-box p-4">
                <h5><?= e($p['titulo']) ?></h5>
                <p><?= e($p['descricao']) ?></p>
              </div>
            </div>
          <?php endforeach; ?>
          <?php if (!$promocoes): ?>
            <p class="text-secondary">Nenhuma promoção ativa no momento.</p>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <!-- Reservas -->
    <section id="reservas" class="py-5 bg-black">
      <div class="container" style="max-width: 720px;">
        <h2 class="section-title text-gold text-center fade-in">Reserve sua mesa</h2>
        <p class="text-center text-light-emphasis">Escolha a data e veja os horários com vagas. A reserva fica pendente até a confirmação do restaurante.</p>

        <div id="reserva-alerta" role="alert" aria-live="polite"></div>

        <form id="form-reserva" class="row g-3 mt-2" novalidate>
          <?= csrf_campo() ?>
          <!-- Campo isca contra robôs: deve permanecer vazio -->
          <div style="position:absolute; left:-9999px;" aria-hidden="true">
            <label>Não preencha <input type="text" name="site" tabindex="-1" autocomplete="off"></label>
          </div>

          <div class="col-md-8">
            <label for="r-nome" class="form-label">Nome completo</label>
            <input type="text" class="form-control" id="r-nome" name="nome" maxlength="100" required>
          </div>
          <div class="col-md-4">
            <label for="r-telefone" class="form-label">Telefone (com DDD)</label>
            <input type="tel" class="form-control" id="r-telefone" name="telefone" maxlength="15" placeholder="(11) 91234-5678" required>
          </div>
          <div class="col-md-12">
            <label for="r-email" class="form-label">E-mail <span class="text-secondary">(opcional)</span></label>
            <input type="email" class="form-control" id="r-email" name="email" maxlength="150">
          </div>
          <div class="col-md-4">
            <label for="r-data" class="form-label">Data</label>
            <input type="date" class="form-control" id="r-data" name="data" min="<?= e($hoje) ?>" max="<?= e($maxima) ?>" required>
          </div>
          <div class="col-md-4">
            <label for="r-horario" class="form-label">Horário</label>
            <select class="form-select" id="r-horario" name="horario" required disabled>
              <option value="">Escolha a data primeiro</option>
            </select>
          </div>
          <div class="col-md-4">
            <label for="r-pessoas" class="form-label">Pessoas</label>
            <input type="number" class="form-control" id="r-pessoas" name="pessoas" min="1" max="<?= MAX_PESSOAS_RESERVA ?>" value="2" required>
          </div>
          <div class="col-12">
            <label for="r-obs" class="form-label">Observações <span class="text-secondary">(opcional)</span></label>
            <textarea class="form-control" id="r-obs" name="observacoes" rows="2" maxlength="300" placeholder="Aniversário, cadeirinha de bebê, restrição alimentar..."></textarea>
          </div>
          <div class="col-12 text-center">
            <button type="submit" class="btn btn-gold px-5" id="r-enviar">Solicitar reserva</button>
          </div>
        </form>
      </div>
    </section>

    <!-- Localização -->
    <section id="localizacao" class="py-5 bg-black text-center">
      <div class="container">
        <h2 class="section-title text-gold fade-in">Localização</h2>
        <p class="mt-3">Rua da Churrascaria, 123 - Centro | Cidade - Estado</p>
        <div class="d-flex justify-content-center mt-4">
          <iframe src="https://www.google.com/maps/embed?pb=..." width="80%" height="300" style="border:0;" allowfullscreen loading="lazy"></iframe>
        </div>
      </div>
    </section>

    <!-- Horários -->
    <section id="horarios" class="py-5 text-center">
      <div class="container">
        <h2 class="section-title text-gold fade-in">Horários de Funcionamento</h2>
        <p class="mt-3"><strong>Segunda a Sexta:</strong> 11h às 23h</p>
        <p><strong>Sábado e Domingo:</strong> 10h às 23h</p>
      </div>
    </section>
  </main>

  <!-- Rodapé -->
  <footer class="footer bg-dark text-center py-3">
    <p class="mb-0 text-gold">© 2025 Churrascaria Brasão do Bom | Desenvolvido por Nicolas Henrique</p>
    <div class="social-links mt-2">
      <a href="#" class="text-light mx-2"><i class="fab fa-instagram"></i></a>
      <a href="#" class="text-light mx-2"><i class="fab fa-facebook"></i></a>
      <a href="#" class="text-light mx-2"><i class="fab fa-whatsapp"></i></a>
    </div>
    <div class="mt-3"><a href="admin/login.php" class="btn btn-outline-warning btn-sm px-3">Acesso administrativo</a></div>
  </footer>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="js/scripts.js"></script>
  <script src="js/reserva.js"></script>

</body>
</html>
