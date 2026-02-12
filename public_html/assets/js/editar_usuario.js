$(document).ready(function () {

  // ===============================
  // 🔹 Função genérica para enviar formulários via AJAX
  // ===============================
  function enviarFormulario(formSelector, mensagemSucesso) {
    const form = $(formSelector);
    $.ajax({
      url: '../../proxy/usuario/atualizar_usuario.php',
      type: 'POST',
      data: form.serialize(),
      dataType: 'json',
      xhrFields: { withCredentials: true },
      success: function (resp) {
        if (resp.success) {
          Swal.fire({
            title: 'Sucesso!',
            text: resp.mensagem || mensagemSucesso,
            icon: 'success',
            confirmButtonText: 'OK'
          }).then(() => {
            window.location.href = 'lista_usuarios.php';
          });
        } else {
          Swal.fire('Erro', resp.error || 'Não foi possível atualizar.', 'error');
        }
      },
      error: function (xhr, status, error) {
        console.error("Erro AJAX:", status, error, xhr.responseText);
        Swal.fire('Erro', 'Falha na comunicação com o servidor.', 'error');
      }
    });
  }

  // ===============================
  // 🔹 Envio dos formulários
  // ===============================
  $('#formDadosUsuario').on('submit', function (e) {
    e.preventDefault();
    enviarFormulario(this, 'Dados atualizados com sucesso!');
  });

  $('#formPermissaoUsuario').on('submit', function (e) {
    e.preventDefault();
    enviarFormulario(this, 'Permissão atualizada com sucesso!');
  });

  $('#formAcessoUsuario').on('submit', function (e) {
    e.preventDefault();
    enviarFormulario(this, 'Dados de acesso atualizados com sucesso!');
  });

  $('#formMinisterioUsuario').on('submit', function (e) {
    e.preventDefault();
    enviarFormulario(this, 'Ministério atualizado com sucesso!');
  });

  // ===============================
  // 🔹 CEP: máscara, busca automática e restauração de valores
  // ===============================
  const valoresOriginais = {
    cep: $("#cepUsuario").val(),
    endereco: $("#enderecoUsuario").val(),
    bairro: $("#bairroEnderecoUsuario").val(),
    cidade: $("#cidadeUsuario").val(),
    estado: $("#estadoEnderecoUsuario").val(),
  };

  $("#cepUsuario").on("input", function () {
    let cep = $(this).val().replace(/\D/g, "");

    // Máscara: 00000-000
    if (cep.length > 5) cep = cep.replace(/^(\d{5})(\d)/, "$1-$2");
    if (cep.length > 9) cep = cep.substring(0, 9);

    $(this).val(cep);

    // Se apagou tudo → restaura originais
    if (cep.length === 0) {
      $("#enderecoUsuario").val(valoresOriginais.endereco);
      $("#bairroEnderecoUsuario").val(valoresOriginais.bairro);
      $("#cidadeUsuario").val(valoresOriginais.cidade);
      $("#estadoEnderecoUsuario").val(valoresOriginais.estado);
      return;
    }

    // Consulta API ViaCEP quando completo
    if (cep.length === 9) {
      $.getJSON(`https://viacep.com.br/ws/${cep.replace("-", "")}/json/`, function (data) {
        if (!("erro" in data)) {
          $("#enderecoUsuario").val(data.logradouro);
          $("#bairroEnderecoUsuario").val(data.bairro);
          $("#cidadeUsuario").val(data.localidade);
          $("#estadoEnderecoUsuario").val(data.uf);
        } else {
          Swal.fire("CEP não encontrado!", "Verifique e tente novamente.", "warning");
          $("#enderecoUsuario").val(valoresOriginais.endereco);
          $("#bairroEnderecoUsuario").val(valoresOriginais.bairro);
          $("#cidadeUsuario").val(valoresOriginais.cidade);
          $("#estadoEnderecoUsuario").val(valoresOriginais.estado);
        }
      }).fail(() => {
        Swal.fire("Erro", "Falha ao consultar o CEP.", "error");
      });
    }
  });

  // ===============================
  // 🔹 Máscara de telefone ((99) 9 9999-9999)
  // ===============================
  const telefoneInput = $('[name="telefoneUsuario"]');
  telefoneInput.on("input", function () {
    let tel = $(this).val().replace(/\D/g, "");
    if (tel.length > 11) tel = tel.substring(0, 11);

    if (tel.length > 2 && tel.length <= 7)
      tel = tel.replace(/^(\d{2})(\d*)/, "($1) $2");
    else if (tel.length >= 8 && tel.length <= 10)
      tel = tel.replace(/^(\d{2})(\d{4})(\d*)/, "($1) $2-$3");
    else if (tel.length === 11)
      tel = tel.replace(/^(\d{2})(\d{1})(\d{4})(\d{4})/, "($1) $2 $3-$4");

    $(this).val(tel);
  });

  // Formatação inicial (ao carregar)
  let telefoneInicial = telefoneInput.val().replace(/\D/g, "");
  if (telefoneInicial.length === 10)
    telefoneInicial = telefoneInicial.replace(/^(\d{2})(\d{4})(\d{4})/, "($1) $2-$3");
  else if (telefoneInicial.length === 11)
    telefoneInicial = telefoneInicial.replace(/^(\d{2})(\d{1})(\d{4})(\d{4})/, "($1) $2 $3-$4");
  telefoneInput.val(telefoneInicial);

  // ===============================
  // 🔹 Máscara de data de nascimento (dd/mm/yyyy)
  // ===============================
  const dataInput = $('[name="dataNascimentoUsuario"]');
  dataInput.on("input", function () {
    let val = $(this).val().replace(/\D/g, "");
    if (val.length > 8) val = val.substring(0, 8);

    if (val.length > 4)
      val = val.replace(/^(\d{2})(\d{2})(\d{0,4})/, "$1/$2/$3");
    else if (val.length > 2)
      val = val.replace(/^(\d{2})(\d{0,2})/, "$1/$2");

    $(this).val(val);
  });

  // Formatação inicial (yyyy-mm-dd → dd/mm/yyyy)
  let dataOriginal = dataInput.val();
  if (dataOriginal && dataOriginal.match(/^\d{4}-\d{2}-\d{2}$/)) {
    const [ano, mes, dia] = dataOriginal.split("-");
    dataInput.val(`${dia}/${mes}/${ano}`);
  }

  $('#btnResetarSenha').on('click', function() {
    if (!confirm('Deseja realmente resetar a senha deste usuário?')) return;

    const idUsuario = $('input[name="idUsuario"]').val();

    $.post('../../proxy/usuario/resetar_senha.php', { idUsuario }, function(res) {
        if (res.erro) {
            Swal.fire('Erro', res.erro, 'error');
        } else {
            Swal.fire('Sucesso', 'Senha temporária: ' + res.senhaTemp, 'success');
        }
    }, 'json');
});


});
