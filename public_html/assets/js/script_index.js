document.addEventListener('DOMContentLoaded', () => {

  const form = document.getElementById('loginForm');
  const toggleSenha = document.getElementById('toggleSenha');
  const inputSenha = document.querySelector('input[name="senha"]');

  // ===== Alternar visibilidade da senha =====
  if (toggleSenha && inputSenha) {
    toggleSenha.style.cursor = 'pointer'; // ícone clicável

    toggleSenha.addEventListener('click', () => {
      console.log('👁️ Clique detectado no ícone de senha');
      if (inputSenha.type === 'password') {
        inputSenha.type = 'text';
        toggleSenha.classList.remove('bi-eye-fill');
        toggleSenha.classList.add('bi-eye-slash-fill');
      } else {
        inputSenha.type = 'password';
        toggleSenha.classList.remove('bi-eye-slash-fill');
        toggleSenha.classList.add('bi-eye-fill');
      }
    });
  } else {
    console.warn('⚠️ toggleSenha ou inputSenha não encontrado no DOM');
  }

  const linkEsqueciSenha = document.getElementById('esqueciSenha');

if (linkEsqueciSenha) {
  linkEsqueciSenha.addEventListener('click', (e) => {
    e.preventDefault();

    Swal.fire({
      icon: 'info',
      title: 'Recuperação de Senha',
      html: 'Entre em contato com o pastor ou com a liderança responsável.',
      confirmButtonText: 'Ok'
    });
  });
}


 // ===== Envio do formulário =====
if (form) {
  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const formData = new FormData(form);
    formData.append('csrf_token', CSRF_TOKEN);

    Swal.fire({
      title: 'Aguarde...',
      text: 'Verificando credenciais',
      allowOutsideClick: false,
      didOpen: () => { Swal.showLoading(); }
    });

    try {
      const response = await fetch(`${PROXY_URL}/processar_login.php`, {
        method: 'POST',
        body: formData
      });

      const data = await response.json();
      Swal.close();

      if (data.success) {

        // ✅ Checa se precisa trocar a senha
        if (data.forcarTrocaSenha) {
          Swal.fire({
            icon: 'info',
            title: 'Troca de senha obrigatória',
            text: 'Você precisa atualizar sua senha antes de continuar.',
            confirmButtonText: 'Ok'
          }).then(() => {
            window.location.href = 'trocar_senha.php';
          });
          return; // impede o redirecionamento normal
        }

        Swal.fire({
          icon: 'success',
          title: 'Sucesso!',
          text: 'Redirecionando...',
          timer: 1500,
          showConfirmButton: false
        }).then(() => {
          window.location.href = 'painel.php';
        });

      } else {
        Swal.fire({
          icon: 'error',
          title: 'Erro',
          text: data.erro || 'Usuário ou senha incorretos.'
        });
      }

    } catch (err) {
      Swal.close();
      Swal.fire({
        icon: 'error',
        title: 'Erro',
        text: 'Não foi possível processar a solicitação.'
      });
    }
  });
}

});
