document.addEventListener('DOMContentLoaded', () => {

  const form = document.getElementById('loginForm');
  const inputSenha = document.querySelector('input[name="novaSenha"]');
    if (!toggleSenha || !inputSenha) {
    console.warn('⚠️ toggleSenha ou inputSenha não encontrado no DOM');
  }


  // ===== Alternar visibilidade da senha =====
  if (toggleSenha && inputSenha) {
    toggleSenha.style.cursor = 'pointer';
    toggleSenha.addEventListener('click', () => {
      if (inputSenha.type === 'password') {
        inputSenha.type = 'text';
        toggleSenha.classList.replace('bi-eye-fill', 'bi-eye-slash-fill');
      } else {
        inputSenha.type = 'password';
        toggleSenha.classList.replace('bi-eye-slash-fill', 'bi-eye-fill');
      }
    });
  }

  // ===== Envio do formulário =====
  if (form) {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();

      const formData = new FormData(form);

      Swal.fire({
        title: 'Aguarde...',
        text: 'Atualizando senha',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
      });

      try {
        const response = await fetch('trocar_senha.php', {
          method: 'POST',
          body: formData
        });

        const text = await response.text();
        Swal.close();

        Swal.fire({
          icon: 'success',
          title: 'Senha atualizada!',
          html: text, // mostra o link para dashboard
          timer: 1500,
          showConfirmButton: false
        }).then(() => {
          window.location.href = 'painel.php';
        });

      } catch (err) {
        Swal.close();
        Swal.fire({
          icon: 'error',
          title: 'Erro',
          text: 'Não foi possível atualizar a senha.'
        });
      }
    });
  }

});
