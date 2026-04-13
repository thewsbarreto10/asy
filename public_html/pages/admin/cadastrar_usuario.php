<?php
  require_once __DIR__ . '/../../../app/core/init.php'; // já puxa config e $pdo
  require_once __DIR__ . '/../../../app/core/sessao_segura.php'; // já puxa config e $pdo

  // Verifica se o usuário está logado
  if (!$usuario) {
      header('Location: /asy/');
      exit;
  }

  // Buscar ministérios disponíveis
  try {
      $stmt = $pdo->query("SELECT idMinisterio, descricaoMinisterio FROM asy_ministerios ORDER BY descricaoMinisterio ASC");
      $ministerios = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
      $ministerios = [];
  }
?>

<!doctype html>
<html lang="pt-br">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">

    <title>Cadastro - ASY Gospel Church</title>

    <link rel="icon" href="<?= ASSETS_URL ?>/img/favicon.ico" type="image/x-icon">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- CSS personalizado -->
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/estilo_cadastrar-usuario.css?v=<?= time() ?>">

<style>

/* Controle igual ao CPF */
.form-group .email-feedback {
  display: none;
}

.form-group.email-error .hint-text {
  display: none;
}

.form-group.email-error .email-feedback {
  display: block;
}

.form-group .cpf-feedback {
  display: none;
}

.form-group.cpf-error .hint-text {
  display: none;
}

.form-group.cpf-error .cpf-feedback {
  display: block;
}

/* Espaço pro ícone da direita */
.cpf-input {
  padding-left: 35px;
  padding-right: 40px;
}

/* Ícone status (direita) */
.status-icon {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  font-size: 18px;
  display: none;
}

/* Feedback */
.cpf-feedback {
  font-size: 12px;
  margin-top: 3px;
  display: block;
}

.email-suggestions {
  position: absolute;
  top: 100%;
  left: 0;
  width: 100%;
  background: #595864;
  border: 1px solid #ddd;
  border-radius: 6px;
  margin-top: 5px;
  z-index: 10;
  display: none;
  max-height: 180px;
  overflow-y: auto;
}

.email-suggestions div {
  padding: 10px;
  cursor: pointer;
  font-size: 14px;
}

.email-suggestions div:hover {
  background-color: #f1f1f1;
  color: #000
}

</style>

  </head>

  <body>
    <div class="login-container">
      <div class="login-image">
        <img src="<?= ASSETS_URL ?>/img/asy_logo.png" alt="Logo">
      </div>

      <h4 class="text-center mb-4">Cadastrar usuário</h4>

      <form id="cadastroForm">
        <div class="row g-3"> <!-- g-3 adiciona espaçamento entre colunas -->

          <div class="col-md-12">
            <div class="form-group position-relative">
              <i class="bi bi-person-fill icon-left"></i>
              <input type="text" class="form-control" name="nomeUsuario" placeholder="Nome Completo" required>
              <span class="hint-text">Nome Completo</span>
            </div>
          </div>

<div class="col-md-4">
  <div class="form-group position-relative">
    <i class="bi bi-person-vcard icon-left"></i>

    <input 
      type="text" 
      class="form-control cpf-input" 
      name="cpfUsuario" 
      placeholder="CPF"
      maxlength="14"
      required
    >

    <i class="bi status-icon"></i>

    <span class="hint-text">CPF</span>
    <small class="cpf-feedback text-danger"></small>
  </div>
</div>        

          <div class="col-md-4">
            <div class="form-group position-relative">
              <i class="bi bi-calendar-date-fill icon-left"></i>
              <input type="text" class="form-control" name="dataNascimentoUsuario" placeholder="Data de nascimento" required inputmode="numeric" maxlength="10">
              <span class="hint-text">Data de Nascimento</span>
            </div>
          </div>

          <div class="col-md-4">
            <div class="form-group position-relative">
              <i class="bi bi-whatsapp icon-left"></i>
              <input type="tel" class="form-control" name="telefoneUsuario" placeholder="Celular | WhatsApp" required>
              <span class="hint-text">Celular | WhatsApp</span>
            </div>
          </div>

          <div class="col-md-12">
            <div class="form-group position-relative">
              <i class="bi bi-diagram-3-fill icon-left"></i>
              <select class="form-control" name="idMinisterioUsuario" required>
                <option value="" readonly selected>Selecione o ministério</option>
                <?php foreach ($ministerios as $m): ?>
                  <option value="<?= $m['idMinisterio'] ?>"><?= htmlspecialchars($m['descricaoMinisterio']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="col-md-3">
            <div class="form-group position-relative">
              <i class="bi bi-123 icon-left"></i>
              <input type="text" class="form-control" name="cepUsuario" id="cepUsuario" placeholder="CEP" required>
              <span class="hint-text">CEP</span>
            </div>
          </div>

          <div class="col-md-7">
            <div class="form-group position-relative">
              <i class="bi bi-geo-alt-fill icon-left"></i>
              <input type="text" class="form-control" name="enderecoUsuario" id="enderecoUsuario" placeholder="Endereço" readonly>
              <span class="hint-text">Endereço</span>
            </div>
          </div>

          <div class="col-md-2">
            <div class="form-group position-relative">
              <i class="bi bi-123 icon-left"></i>
              <input type="text" class="form-control" name="numeroEnderecoUsuario" placeholder="Número" required>
              <span class="hint-text">Número</span>
            </div>
          </div>

          <div class="col-md-12">
            <div class="form-group position-relative">
              <i class="bi bi-mailbox-flag icon-left"></i>
              <input type="text" class="form-control" name="complementoEnderecoUsuario" placeholder="Complemento">
              <span class="hint-text">Complemento</span>
            </div>
          </div>

          <div class="col-md-5">
            <div class="form-group position-relative">
              <i class="bi bi-geo-alt-fill icon-left"></i>
              <input type="text" class="form-control" name="bairroEnderecoUsuario" id="bairroEnderecoUsuario" placeholder="Bairro" readonly>
              <span class="hint-text">Bairro</span>
            </div>
          </div>

          <div class="col-md-5">
            <div class="form-group position-relative">
              <i class="bi bi-geo-alt-fill icon-left"></i>
              <input type="text" class="form-control" name="cidadeUsuario" id="cidadeUsuario" placeholder="Cidade" readonly>
              <span class="hint-text">Cidade</span>
            </div>
          </div>

          <div class="col-md-2">
            <div class="form-group position-relative">
              <i class="bi bi-geo-alt-fill icon-left"></i>
              <input type="text" class="form-control" name="estadoUsuario" id="estadoUsuario" placeholder="Estado" readonly>
              <span class="hint-text">Estado</span>
            </div>
          </div>

<div class="col-md-6">
  <div class="form-group position-relative">
    <i class="bi bi-envelope-fill icon-left"></i>

    <input 
      type="email" 
      class="form-control email-input" 
      name="emailUsuario" 
      placeholder="E-mail" 
      autocomplete="off"
      required
    >

    <i class="bi status-icon email-status-icon"></i>

    <span class="hint-text">E-mail</span>
    <small class="email-feedback text-danger"></small>

    <!-- sugestões -->
    <div class="email-suggestions"></div>
  </div>
</div>

          <div class="col-md-6">
            <div class="form-group position-relative">
              <i class="bi bi-backpack2 icon-left"></i>
              <select class="form-control" name="asy_aluno" required>
                <option value="" readonly selected>É aluno?</option>
                <option value="sim">Aluno</option>
                <option value="nao">Não Aluno</option>
              </select>
            </div>
          </div>          

          <div class="col-md-4 d-flex justify-content-center">
            <button class="btn btn-secondary btn-lg px-5" onclick="window.location.href='lista_usuarios.php'">Cancelar</button>
          </div>
          <div class="col-md-4 d-flex justify-content-center">
            <button type="submit" class="btn btn-success btn-lg px-5">Cadastrar</button>
          </div>
          <div class="col-md-4 d-flex justify-content-center">
            <button type="reset" class="btn btn-light btn-lg px-5">Limpar</button>
          </div>

        </div>
      </form>
    </div>

    <script>
const cpfInput = document.querySelector('.cpf-input');
const feedback = document.querySelector('.cpf-feedback');
const statusIcon = document.querySelector('.status-icon');

cpfInput.addEventListener('input', function () {
    let v = cpfInput.value.replace(/\D/g, '');

    // máscara
    if (v.length > 11) v = v.slice(0, 11);

    v = v.replace(/(\d{3})(\d)/, '$1.$2');
    v = v.replace(/(\d{3})(\d)/, '$1.$2');
    v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');

    cpfInput.value = v;

    // validação em tempo real
    validarCampoCPF(v);
});

function validarCampoCPF(cpf) {
    if (cpf.length < 11) {
        resetCPF();
        return;
    }

    if (validarCPF(cpf)) {
        cpfInput.classList.remove('cpf-invalid');
        cpfInput.classList.add('cpf-valid');

        feedback.textContent = 'CPF válido';
        feedback.classList.remove('text-danger');
        feedback.classList.add('text-success');

        statusIcon.className = 'bi bi-check-circle-fill status-icon text-success';
        statusIcon.style.display = 'block';
    } else {
        cpfInput.classList.remove('cpf-valid');
        cpfInput.classList.add('cpf-invalid');

        feedback.textContent = 'CPF inválido';
        feedback.classList.remove('text-success');
        feedback.classList.add('text-danger');

        statusIcon.className = 'bi bi-x-circle-fill status-icon text-danger';
        statusIcon.style.display = 'block';
    }
}

function resetCPF() {
    cpfInput.classList.remove('cpf-valid', 'cpf-invalid');
    feedback.textContent = '';
    statusIcon.style.display = 'none';
}

// algoritmo oficial
function validarCPF(cpf) {
    cpf = cpf.replace(/[^\d]+/g,'');

    if (cpf.length !== 11 || /^(\d)\1+$/.test(cpf)) return false;

    let soma = 0;
    let resto;

    for (let i=1; i<=9; i++)
        soma += parseInt(cpf.substring(i-1, i)) * (11 - i);

    resto = (soma * 10) % 11;
    if (resto >= 10) resto = 0;
    if (resto !== parseInt(cpf.substring(9, 10))) return false;

    soma = 0;
    for (let i=1; i<=10; i++)
        soma += parseInt(cpf.substring(i-1, i)) * (12 - i);

    resto = (soma * 10) % 11;
    if (resto >= 10) resto = 0;

    return resto === parseInt(cpf.substring(10, 11));
}
</script>

<script>
const emailInput = document.querySelector('.email-input');
const emailFeedback = document.querySelector('.email-feedback');
const emailIcon = document.querySelector('.email-status-icon');

emailInput.addEventListener('input', function () {
    const email = emailInput.value.trim();
    validarCampoEmail(email);
});

function validarCampoEmail(email) {
    const formGroup = emailInput.closest('.form-group');

    if (email.length === 0) {
        resetEmail();
        return;
    }

    if (validarEmail(email)) {
        formGroup.classList.remove('email-error');
        emailInput.classList.remove('cpf-invalid');
        emailInput.classList.add('cpf-valid');

        emailIcon.className = 'bi bi-check-circle-fill status-icon text-success';
        emailIcon.style.display = 'block';

    } else {
        formGroup.classList.add('email-error');
        emailInput.classList.remove('cpf-valid');
        emailInput.classList.add('cpf-invalid');

        emailIcon.className = 'bi bi-x-circle-fill status-icon text-danger';
        emailIcon.style.display = 'block';
    }
}

function resetEmail() {
    const formGroup = emailInput.closest('.form-group');

    formGroup.classList.remove('email-error');
    emailInput.classList.remove('cpf-valid', 'cpf-invalid');
    emailFeedback.textContent = '';
    emailIcon.style.display = 'none';
}

// Validador simples e eficiente
function validarEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}
</script>

<script>
const domains = [
  'gmail.com',
  'hotmail.com',
  'outlook.com',
  'icloud.com',
  'yahoo.com',
  'live.com'
];

const suggestionBox = document.querySelector('.email-suggestions');

emailInput.addEventListener('input', function () {
    const value = emailInput.value;
    
    if (!value.includes('@')) {
        suggestionBox.style.display = 'none';
        return;
    }

    const [name, domainPart] = value.split('@');

    if (!name) return;

    const filtered = domains.filter(d => 
        d.startsWith(domainPart || '')
    );

    if (filtered.length === 0) {
        suggestionBox.style.display = 'none';
        return;
    }

    suggestionBox.innerHTML = '';

    filtered.forEach(domain => {
        const item = document.createElement('div');
        item.textContent = `${name}@${domain}`;

        item.addEventListener('click', () => {
            emailInput.value = item.textContent;
            suggestionBox.style.display = 'none';

            // força validar depois de escolher
            validarCampoEmail(emailInput.value);
        });

        suggestionBox.appendChild(item);
    });

    suggestionBox.style.display = 'block';
});

document.addEventListener('click', function(e) {
    if (!e.target.closest('.form-group')) {
        suggestionBox.style.display = 'none';
    }
});
</script>

    <!-- JS personalizado -->
    <script>
      const PROXY_URL = '<?= PUBLIC_URL ?>/proxy';
    </script>
    <script src="<?= ASSETS_URL ?>/js/script_cadastrar-usuario.js?v=<?= time() ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
