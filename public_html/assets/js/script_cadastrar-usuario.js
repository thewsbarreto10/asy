// script_cadastrar-usuarios.js (substitua tudo por este conteúdo)
document.addEventListener("DOMContentLoaded", () => {

  // ----- VARIÁVEIS GLOBAIS -----
  const form = document.getElementById("cadastroForm");
  const PROXY = typeof PROXY_URL !== "undefined" ? PROXY_URL : "";

  // ----- TOGGLE SENHA -----
  (function initToggleSenha() {
    const toggleSenha = document.getElementById("toggleSenha");
    const senhaInput = document.querySelector('input[name="senhaUsuario"], #senhaUsuario');

    if (!toggleSenha || !senhaInput) return;

    toggleSenha.addEventListener("click", () => {
      const isPassword = senhaInput.type === "password";
      senhaInput.type = isPassword ? "text" : "password";

      // Alterna ícones: manter compatível com bootstrap icons
      toggleSenha.classList.toggle("bi-eye-fill", !isPassword);
      toggleSenha.classList.toggle("bi-eye-slash-fill", isPassword);
    });
  })();


  // ----- ENVIO DO FORM (AJAX) -----
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
        const response = await fetch(`${PROXY}/processar_cadastro_usuario.php`, {
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
            // Redireciona após o alerta de sucesso
            window.location.replace("lista_usuarios.php");
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


  // ----- MÁSCARA TELEFONE (formato: (DD) 9 XXXX-XXXX) -----
  (function initTelefoneMask() {
    const telefoneInput = document.querySelector('input[name="telefoneUsuario"]');
    if (!telefoneInput) return;

    telefoneInput.addEventListener("input", (e) => {
      let v = e.target.value.replace(/\D/g, "");
      if (v.length > 11) v = v.slice(0, 11);

      // Formatação progressiva
      if (v.length > 6) {
        e.target.value = `(${v.slice(0,2)}) ${v.slice(2,3)} ${v.slice(3,7)}-${v.slice(7)}`;
      } else if (v.length > 2) {
        e.target.value = `(${v.slice(0,2)}) ${v.slice(2)}`;
      } else if (v.length > 0) {
        e.target.value = v;
      } else {
        e.target.value = "";
      }
    });
  })();


  // ----- MÁSCARA DATA (formato dd/mm/aaaa) -----
  (function initDataMask() {
    const dataInput = document.querySelector('input[name="dataNascimentoUsuario"]');
    if (!dataInput) return;

    dataInput.addEventListener("input", (e) => {
      let v = e.target.value.replace(/\D/g, "");
      if (v.length > 8) v = v.slice(0, 8);

      if (v.length > 4) {
        e.target.value = `${v.slice(0,2)}/${v.slice(2,4)}/${v.slice(4)}`;
      } else if (v.length > 2) {
        e.target.value = `${v.slice(0,2)}/${v.slice(2)}`;
      } else {
        e.target.value = v;
      }
    });
  })();


  // ----- HINT-STYLE (placeholder pequeno) -----
  (function initHints() {
    document.querySelectorAll(".form-group").forEach(group => {
      const input = group.querySelector(".form-control");
      const hint = group.querySelector(".hint-text");

      if (!input || !hint) return;

      input.addEventListener("focus", () => group.classList.add("active"));
      input.addEventListener("blur", () => {
        if (!input.value.trim()) group.classList.remove("active");
      });

      // Se o campo já tem valor ao carregar (ex: preenchido pelo CEP), marca active
      if (input.value && input.value.trim()) group.classList.add("active");
    });
  })();


  // ----- CEP: máscara e busca ViaCEP -----
  (function initCep() {
    const cepInput = document.getElementById("cepUsuario");
    const endereco = document.getElementById("enderecoUsuario");
    const bairro = document.getElementById("bairroEnderecoUsuario");
    const cidade = document.getElementById("cidadeUsuario");
    const estado = document.getElementById("estadoUsuario");

    if (!cepInput) return;

    // máscara XXXXX-XXX
    cepInput.addEventListener("input", (e) => {
      let value = e.target.value.replace(/\D/g, "");
      if (value.length > 5) value = value.replace(/^(\d{5})(\d)/, "$1-$2");
      e.target.value = value;
    });

    cepInput.addEventListener("blur", async () => {
      const cep = cepInput.value.replace(/\D/g, "");
      if (cep.length !== 8) return;

      try {
        const res = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
        const data = await res.json();

        if (data.erro) {
          Swal.fire({
            icon: "warning",
            title: "CEP não encontrado",
            text: "Verifique o número e tente novamente."
          });
          // libera edição para preenchimento manual
          [endereco, bairro, cidade, estado].forEach(c => { if (c) c.readOnly = false; });
          return;
        }

        if (endereco) endereco.value = data.logradouro || "";
        if (bairro) bairro.value = data.bairro || "";
        if (cidade) cidade.value = data.localidade || "";
        if (estado) estado.value = data.uf || "";

        // marca hints como ativos (caso estejam escondidos)
        [endereco, bairro, cidade, estado].forEach(c => {
          if (c) {
            c.readOnly = !!(data.logradouro || data.localidade);
            const group = c.closest('.form-group');
            if (group) group.classList.add('active');
          }
        });

      } catch (err) {
        console.error("Erro ViaCEP:", err);
        Swal.fire({
          icon: "error",
          title: "Erro ao buscar CEP",
          text: "Não foi possível consultar o CEP. Tente novamente mais tarde."
        });
      }
    });
  })();


  // ----- Garantia: se algum campo já veio preenchido ao carregar, ativa hint -----
  // (ex.: se o servidor renderizar valores)
  (function activatePreFilled() {
    document.querySelectorAll('.form-group .form-control').forEach(input => {
      if (input.value && input.value.trim()) {
        const g = input.closest('.form-group');
        if (g) g.classList.add('active');
      }
    });
  })();

}); // end DOMContentLoaded
