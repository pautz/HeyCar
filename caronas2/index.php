  <?php
// Verificar se a conexão é segura (HTTPS)
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
    // Gerar a URL para redirecionamento para HTTPS
    $url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    // Redirecionar para a versão HTTPS da URL atual
    header('Location: ' . $url);
    exit();
}

// Cabeçalhos de segurança
header('Strict-Transport-Security: max-age=31536000; includeSubDomains'); // HSTS
header('X-Content-Type-Options: nosniff'); // Proteção contra MIME Sniffing
header('X-Frame-Options: DENY'); // Proteger contra clickjacking
header('X-XSS-Protection: 1; mode=block'); // Proteção contra XSS
header('Referrer-Policy: no-referrer'); // Política de Referência
// Impedir que o site seja carregado em um iframe
header("X-Frame-Options: SAMEORIGIN");

// Você também pode adicionar segurança adicional
header("Content-Security-Policy: frame-ancestors 'self';");

header("Content-Security-Policy: frame-ancestors 'self' https://carlitoslocacoes.com;");
// Seu código PHP começa aqui
// ...

// Verifica se o cabeçalho enviado pelo CloudFront está presente
if (isset($_SERVER['HTTP_X_CUSTOM_HEADER'])) {
    $valorDoCabecalho = $_SERVER['HTTP_X_CUSTOM_HEADER'];

    // Exibe o valor do cabeçalho
    echo "Cabeçalho personalizado recebido: " . htmlspecialchars($valorDoCabecalho);
} else {
    // Caso o cabeçalho não exista
   
// Exibindo um botão com link específico



}

// Configura um cabeçalho de resposta (se necessário)
header("X-Powered-By: MeuServidorPHP");

?>
<?php
// Definindo os conteúdos como variáveis
$title1 = "Navegando por Novas Possibilidades";
$text1 = "Inspiramos e damos vida a ideias que refletem simplicidade e autenticidade. Nossa missão é explorar novos caminhos e transformar descobertas em experiências significativas.";

$title2 = "Conexões que Inspiram";
$text2 = "Vamos além do comum, criando vínculos autênticos que agregam valor e propósito. Acreditamos na importância de cada detalhe, com dedicação e cuidado.";

$title3 = "Detalhes que Transformam";
$text3 = "Nosso objetivo é criar algo único, que valorize a simplicidade e a conexão entre as pessoas, traduzindo ideias em experiências genuínas.";

?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <title>Locação de Trator, Retroescavadeira e Equipamentos para Linha de Transmissão | Carlitos Locações</title>
    <meta name="description" content="Locação de tratores em Palmeira das Missões - RS. Encontre tratores de qualidade para sua necessidade! Trabalhamos com linha de transmissão, oferecendo equipamentos robustos e eficientes. Estamos na Av Independência, N 877, Sala 02, Palmeira Das Missões, Rio Grande Do Sul, Brasil. CEP: 98300-000. Acesse carlitoslocacoes.com">
    <meta name="keywords" content="trator, óleo, aeroportos, Palmeira das Missões, locação de tratores, linha de transmissão, locação de máquinas, retroescavadeiras, equipamentos pesados, aluguel de máquinas, sistema de controle de óleo, sistema de caixa, pagamentos de prestações, locação de quartos">
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="index, follow">
  <meta name="author" content="Carlito Veeck Pautz Júnior">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
  <link href="https://fonts.googleapis.com/css?family=Montserrat" rel="stylesheet">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
 

<style>
/* Definição geral do corpo */
body {
            font-family: 'Roboto', Arial, sans-serif;
            line-height: 1.8;
            margin: 0;
            background-color: #e7e7e7;
            color: #333;
            zoom: 1;
        }

        #particles-js {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1; /* Mantém o fundo atrás do conteúdo */
        }

/* Parágrafos e títulos */
p, h1, h2, h3, h4, h5, h6 {
    color: #000; /* Texto em preto */
}

/* Botões com estilo moderno */
button {
    background-color: #b0b0b0; /* Tom mais escuro para contraste */
    color: #fff; /* Texto branco */
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    transition: background-color 0.3s ease-in-out, color 0.3s ease-in-out;
}

button:hover {
    background-color: #8c8c8c; /* Fundo mais escuro no hover */
    color: #fff;
}

/* Carrossel */
.carousel-caption {
    background-color: rgba(0, 0, 0, 0.5); /* Fundo semi-transparente */
    padding: 10px;
}

.carousel-inner > .item > img,
.carousel-inner > .item > a > img {
    display: block;
    height: auto;
    width: 100%;
    max-width: 100%;
    line-height: 1;
}

/* Imagens redondas */
.img-circle-custom {
    border-radius: 50%;
    width: 100px;
    height: 100px;
    object-fit: cover;
}

/* Fundo de seções */
.section {
    background: #e7e7e7; /* Fundo com a cor escolhida */
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    color: #333; /* Texto em cinza escuro */
}

.section:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}

/* Ajustes para dispositivos móveis */
@media (max-width: 767px) {
    body {
        font-size: 18px; /* Tamanho da fonte menor */
        padding: 10px;
    }

    .img-circle-custom {
        width: 80px;
        height: 80px;
    }

    .carousel-inner > .item > img,
    .carousel-inner > .item > a > img {
        height: 300px; /* Ajuste para carrosséis menores */
    }
}

@media (min-width: 768px) and (max-width: 991px) {
    body {
        font-size: 20px;
    }

    .img-circle-custom {
        width: 90px;
        height: 90px;
    }

    .carousel-inner > .item > img,
    .carousel-inner > .item > a > img {
        height: 420px;
    }
}

/* Iframes responsivos */
.video-container {
    position: relative;
    padding-bottom: 56.25%; /* Proporção 16:9 */
    height: 0;
}

.video-container iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

/* Botões fixados na parte inferior */
.stylized-button {
    position: fixed;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    z-index: 1000;
    font-size: 16px;
    color: #fff;
    background-color: #b0b0b0; /* Tom escuro para destacar */
    border: none;
    padding: 10px 20px;
    text-align: center;
    cursor: pointer;
    border-radius: 5px;
    transition: background-color 0.3s;
}

.stylized-button:hover {
    background-color: #8c8c8c;
}

/* Estilo geral do navbar */
.navbar {
  background-color: #f8f9fa; /* Mantém a cor original */
  border-bottom: 1px solid #ddd;
  padding: 10px;
}

/* Ajuste dos textos */
.navbar-brand,
.navbar-nav li a {
  color: #555 !important; /* Melhor contraste */
  font-size: 16px;
  font-weight: bold;
  text-decoration: none;
}

.navbar-brand:hover,
.navbar-nav li a:hover {
  color: #222 !important;
}

/* Estilização do telefone */
.telefone {
  font-size: 14px;
  color: #555;
  text-decoration: none;
}

/* Garante que tudo fique na mesma linha */
.navbar-header {
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: space-between; /* Distribui os itens corretamente */
  width: 100%;
}

/* Mantém "Contato" e "Entrar" na mesma linha e à direita */
.navbar-links {
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: flex-end; /* Alinha os itens à direita */
  flex-grow: 1; /* Faz com que ocupem o espaço disponível à direita */
}

.navbar-nav {
  display: flex;
  flex-direction: row;
  align-items: center;
  gap: 15px;
  list-style: none;
  padding: 0;
  margin: 0;
}

.navbar-nav li {
  display: inline-block;
}

/* Ajuste para impedir que "Entrar" desça para outra linha */
.entrar-button {
  text-align: right;
  white-space: nowrap; /* Garante que não quebre a linha */
}

/* Melhor responsividade no mobile */
@media (max-width: 768px) {
  .navbar-header {
    flex-direction: column; /* Ajusta para telas menores */
    align-items: center;
    text-align: center;
  }

  .navbar-links {
    width: 100%;
    justify-content: center; /* Alinha ao centro no mobile */
  }

  .navbar-nav {
    justify-content: center;
    flex-direction: row;
  }
}

/* Contêiner principal para PDF estilo slide */
.pdf-slide-container {
    position: relative;
    width: 100%;
    height: 100%;
    overflow: hidden;
}

/* PDF individual como parte de um slide */
.pdf-slide {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    transition: transform 0.5s ease-in-out;
}

*::before {
    content: none !important; /* Remove qualquer conteúdo */
    display: none !important; /* Garante que não ocupe espaço */
}


.container {
    display: grid;
    grid-template-columns: repeat(3, 1fr); /* 3 colunas */
    gap: 15px; /* Espaçamento entre os botões */
    justify-items: center; /* Centraliza os botões */
    align-items: center; /* Alinha verticalmente */
    padding: 20px;
   
}
}

/* Ajuste para telas menores */
@media (max-width: 768px) {
    .container {
        grid-template-columns: repeat(2, 1fr); /* 2 colunas */
    }
}

@media (max-width: 480px) {
    .container {
        grid-template-columns: repeat(1, 1fr); /* 1 coluna */
    }
}

.button-wrapper {
    width: 100%;
    text-align: center;
}

.button {
    padding: 12px 20px;
    font-size: 16px;
    cursor: pointer;
    width: 100%; /* Faz os botões ocuparem toda a largura disponível */
    max-width: 250px; /* Limita o tamanho máximo */
}
@media (max-width: 768px) {
  .navbar-nav {
    display: flex;
    flex-direction: row;
  }



</style>


</head>
<body>
  

<nav class="navbar navbar-default navbar-fixed-top">
  <div class="container-fluid">
    <div class="navbar-header">
      <div class="navbar-content">
        <a class="navbar-brand" href="../">Carlito's Locações</a>
        <a class="navbar-brand telefone" href="tel:+5555996479747">(55) 9.9647-9747</a>
      </div>
      <div class="navbar-links">
        <ul class="nav navbar-nav navbar-right">
          <li><a href="https://carlitoslocacoes.com/contato/">Contato</a></li>
          <li><a href="/site/login.php" class="entrar-button">Entrar</a></li>
        </ul>
      </div>
    </div>
  </div>
</nav>





<div id="particles-js"></div>
<script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
   
<header class="container-fluid bg-1 text-center">
        <img src="/site2/carlitoschapeu.png" class="title-img" width="400" height="400" alt="Carlitos Chapéu">
        <img src="/site2/fontcarlitos.png" class="title-img" width="400" height="400" alt="Carlitos Locações">
    </header>


<center><h1>Locação de Equipamentos & Prestação de Serviços</h3><br>
  
  <div class="responsive-container">
    <a href="https://carlitoslocacoes.com/contato/" target="_blank" class="stylized-button">Contato</a><br>
    <form method="GET" action="../site/msg.php" class="responsive-form">
        <label for="id">Defina um Contrato:</label><br>
        <input type="text" id="id" name="id" placeholder="Digite o Contrato." required>
        <button type="submit">Buscar</button>
    </form>
    <form method="GET" action="../site/pageloca.php" class="responsive-form">
       <label for="cv">Pesquisar Contrato:</label><br>
       <input type="text" id="cv" name="cv" placeholder="Digite o Contrato." required>
        <button type="submit">Buscar</button>
    </form>
    <form method="GET" action="https://carlitoslocacoes.com/site/page.php?id=&" class="responsive-form">
        <label for="id">Pesquisar Prestação:</label><br>
        <input type="text" id="id" name="id" placeholder="Digite o número da prestação." required>
        <button type="submit">Buscar</button>
    </form>
</div><br>
   <div class="container">
    <div class="button-wrapper"><button class="button" onclick="location.href='https://carlitoslocacoes.com/HeyCar/caronas3/cadastro_produto/'">Cadastro de Locações</button></div>
    <div class="button-wrapper"><button class="button" onclick="location.href='https://carlitoslocacoes.com/HeyCar/caronas2/nossasmaquinas/'">Locações</button></div>
    <div class="button-wrapper"><button class="button" onclick="location.href='https://carlitoslocacoes.com/HeyCar/caronas3/remover_produto/'">Remover Locações</button></div>
    <div class="button-wrapper"><button class="button" onclick="location.href='https://carlitoslocacoes.com/HeyCar/caronas3/cadastro_produto/cadastro_quarto.php'">Cadastro de Quartos</button></div>
    <div class="button-wrapper"><button class="button" onclick="location.href='https://carlitoslocacoes.com/HeyCar/caronas2/nossasmaquinas/quartosroom.php'">Quartos</button></div>
    <div class="button-wrapper"><button class="button" onclick="location.href='https://carlitoslocacoes.com/caixa.php'">Sistema Caixa</button></div>
    <div class="button-wrapper"><button class="button" onclick="location.href='https://carlitoslocacoes.com/HeyCar/caronas3/cadastro_produto/cadastro_voo.php'">Oferecer Carona</button></div>
    <div class="button-wrapper">
    <button class="button" onclick="location.href='https://carlitoslocacoes.com/HeyCar/caronas2/nossasmaquinas/voos.php'"
            aria-label="Botão para acessar a página Pegar Carona">
        Pegar Carona
    </button>
</div>

    <div class="button-wrapper"><button class="button" onclick="location.href='https://carlitoslocacoes.com/registros_oil.php'">Registros de Óleo</button></div>
</div>

      <br>
      


<br>
</center>

<div class="container-fluid bg-3 text-center">
 <div class="section">
        <h2><?php echo $title1; ?></h2>
        <p><?php echo $text1; ?></p>
    </div>

    <div class="section">
        <h2><?php echo $title2; ?></h2>
        <p><?php echo $text2; ?></p>
    </div>

    <div class="section">
        <h2><?php echo $title3; ?></h2>
        <p><?php echo $text3; ?></p>
    </div>

</div>
    
 
<div id="myCarousel" class="carousel slide bg-3 text-center">
  <div class="carousel-inner">
    <div class="item">
     
  <img src="allmaq/t3.png" alt="Trator"></a>
      
    </div>
    <div class="item">
      
  <img src="https://carlitoslocacoes.com/site2/tratorbutton.png" alt="trator">
  </a>
    </div>
    <div class="item">
        
  <img src="https://carlitoslocacoes.com/cia2.jpg" alt="Cia"></a>
          </div>
          
    <div class="item active">
     
  <img src="https://carlitoslocacoes.com/cia1.jpg" alt="Cia"></a>
          </div>
  </div>    
  <a class="left carousel-control" href="#myCarousel" data-slide="prev">
    <span class="glyphicon glyphicon-chevron-left"></span>
  </a>
  <a class="right carousel-control" href="#myCarousel" data-slide="next">
    <span class="glyphicon glyphicon-chevron-right"></span>
  </a>
</div>
<div class="video-container">
        <iframe src="https://www.youtube.com/embed/O4k3Dr6MztU?si=110AUg1R_8jdmzof" frameborder="0" allowfullscreen></iframe>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.13.216/pdf.min.js"></script>
    
<!-- Footer -->
<footer class="container-fluid bg-4 text-center">
<a href="https://carlitoslocacoes.com/site/login.php">Entrar</a>


<p>Apoie o projeto na Binance:</p>
<img src="https://carlitoslocacoes.com/binanceiota.jpg" style="border: 0; width: 128px; height: 128px" alt="QRCODE apoie ID Barbante.">
 <img src="//ipv6.he.net/certification/create_badge.php?pass_name=carlitopautz&amp;badge=1" style="border: 0; width: 128px; height: 128px" alt="Selo de certificação IPv6 para Carlito Pautz">
 <p xmlns:cc="http://creativecommons.org/ns#" xmlns:dct="http://purl.org/dc/terms/"><a property="dct:title" rel="cc:attributionURL" href="http://carlitoslocacoes.com/site2">Carlito's Locações</a> by <a rel="cc:attributionURL dct:creator" property="cc:attributionName" href="http://carlitoslocacoes.com/pofft">Carlito Veeck Pautz Júnior</a> is licensed under <a href="https://creativecommons.org/licenses/by/4.0/?ref=chooser-v1" target="_blank" rel="license noopener noreferrer" style="display:inline-block;">Creative Commons Attribution 4.0 International<img style="height:22px!important;margin-left:3px;vertical-align:text-bottom;" src="https://mirrors.creativecommons.org/presskit/icons/cc.svg?ref=chooser-v1" alt=""><img style="height:22px!important;margin-left:3px;vertical-align:text-bottom;" src="https://mirrors.creativecommons.org/presskit/icons/by.svg?ref=chooser-v1" alt=""></a></p>
<p>Av Independência, N 877, Sala 02, Palmeira Das Missões, Rio Grande Do Sul, Brazil 98300-000</p>
 <p>Desenvolvido por Carlito Veeck Pautz Júnior.</p> 
 </footer>

</body>
</html>
 <script>
        /* Configuração do Particles.js */
        particlesJS("particles-js", {
            particles: {
                number: {
                    value: 100,
                    density: { enable: true, value_area: 800 }
                },
                color: { value: "#333333" }, // Cor escura para as partículas
                shape: {
                    type: "circle",
                    stroke: { width: 0, color: "#000000" },
                    polygon: { nb_sides: 5 }
                },
                opacity: {
                    value: 0.7, // Transparência das partículas
                    random: false,
                    anim: { enable: false, speed: 1, opacity_min: 0.1, sync: false }
                },
                size: {
                    value: 5,
                    random: true,
                    anim: { enable: false, speed: 40, size_min: 0.1, sync: false }
                },
                line_linked: {
                    enable: true,
                    distance: 150,
                    color: "#333333", // Cor escura para as linhas
                    opacity: 0.5, // Transparência das linhas
                    width: 1
                },
                move: {
                    enable: true,
                    speed: 6,
                    direction: "none",
                    random: false,
                    straight: false,
                    out_mode: "out",
                    bounce: false,
                    attract: { enable: false, rotateX: 600, rotateY: 1200 }
                }
            },
            interactivity: {
                detect_on: "canvas",
                events: {
                    onhover: { enable: true, mode: "grab" },
                    onclick: { enable: true, mode: "push" },
                    resize: true
                },
                modes: {
                    grab: { distance: 200, line_linked: { opacity: 1 } },
                    bubble: { distance: 400, size: 40, duration: 2, opacity: 8, speed: 3 },
                    repulse: { distance: 200, duration: 0.4 },
                    push: { particles_nb: 4 },
                    remove: { particles_nb: 2 }
                }
            },
            retina_detect: true
        });
    </script>
