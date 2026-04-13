// script_cadastrar-curso.js
document.addEventListener("DOMContentLoaded", () => {

  // ==============================
  // VARIÁVEIS GLOBAIS
  // ==============================
  const form = document.getElementById("cadastroForm");
  const PROXY = typeof PROXY_URL !== "undefined" ? PROXY_URL : "";

  const inputFile = document.getElementById("cardImagem");
  const fileNameLabel = document.querySelector(".file-name");
  const previewContainer = document.getElementById("previewContainer");
  const previewImage = document.getElementById("previewImage");
  const removeImageBtn = document.getElementById("removeImageBtn");
  const textarea = document.querySelector("textarea[name='descricaoCurso']");


  // ==============================
  // ENVIO DO FORMULÁRIO (AJAX)
  // ==============================
  if (form) {
    form.addEventListener("submit", async (e) => {
      e.preventDefault();

      const formData = new FormData(form);

      Swal.fire({
        title: "Aguarde...",
        text: "Processando cadastro",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
      });

      try {
        const response = await fetch(`${PROXY}/cursos/processar_cadastro_curso.php`, {
          method: "POST",
          body: formData
        });

        const data = await response.json();
        Swal.close();

        if (data.success) {
          Swal.fire({
            icon: "success",
            title: "Sucesso!",
            text: data.success || "Usuário cadastrado com sucesso!",
            timer: 2000,
            showConfirmButton: false
          }).then(() => {
            window.location.replace("lista_cursos.php");
          });
        } else {
          Swal.fire({
            icon: "error",
            title: "Erro",
            text: data.erro || "Não foi possível cadastrar o usuário."
          });
        }

      } catch (err) {
        Swal.close();
        console.error(err);
        Swal.fire({
          icon: "error",
          title: "Erro",
          text: "Falha ao conectar ao servidor."
        });
      }
    });
  }


  // ==============================
  // PRÉ-PREENCHIMENTO DE HINTS
  // ==============================
  (function activatePreFilled() {
    document.querySelectorAll('.form-group .form-control').forEach(input => {
      if (input.value && input.value.trim()) {
        const g = input.closest('.form-group');
        if (g) g.classList.add('active');
      }
    });
  })();


  // ==============================
  // PREVIEW DA IMAGEM SELECIONADA
  // ==============================
  if (inputFile) {
    inputFile.addEventListener("change", function (event) {
      const file = event.target.files[0];

      if (!file) {
        resetImagePreview();
        return;
      }

      fileNameLabel.textContent = file.name;

      const reader = new FileReader();
      reader.onload = function (e) {
        previewImage.src = e.target.result;
        previewContainer.style.display = "block";
      };
      reader.readAsDataURL(file);
    });
  }


  // ==============================
  // BOTÃO "LIMPAR" LIMPA TUDO
  // ==============================
  const resetBtn = document.querySelector("button[type='reset']");

  if (resetBtn) {
    resetBtn.addEventListener("click", function () {

      // Limpa imagem
      resetImagePreview();

      // Limpa textarea
      textarea.value = "";
      textarea.classList.remove("not-empty");
    });
  }


  // ==============================
  // BOTÃO "X" PARA REMOVER IMAGEM
  // ==============================
  if (removeImageBtn) {
    removeImageBtn.addEventListener("click", function () {
      resetImagePreview();
    });
  }


  // ==============================
  // FUNÇÃO PADRÃO DE RESET DE IMAGEM
  // ==============================
  function resetImagePreview() {
    inputFile.value = "";
    fileNameLabel.textContent = "Nenhuma imagem selecionada";
    previewContainer.style.display = "none";
    previewImage.src = "#";
  }

}); // Fim do DOMContentLoaded
