    $(document).ready(function () {
      var table = $('#usuariosTable').DataTable({
        language: { url: `${ASSETS_URL}/js/datatables/pt-BR.json` },
        dom: 'Bfrtip',
        buttons: [
          { extend: 'copy', text: '<i class="fas fa-copy"></i> Copiar' },
          { extend: 'csv', text: '<i class="fas fa-file-csv"></i> CSV' },
          { extend: 'excel', text: '<i class="fas fa-file-excel"></i> Excel' },
          { extend: 'pdf', text: '<i class="fas fa-file-pdf"></i> PDF' },
          { extend: 'print', text: '<i class="fas fa-print"></i> Imprimir' },
          { extend: 'colvis', text: '<i class="fas fa-columns"></i> Colunas' }
        ],
        scrollY: '60vh',
        scrollCollapse: true,
        ordering: true,
        paging: true,
        info: true,
        responsive: true,
        lengthChange: true,
        searching: true,
        initComplete: function () {
          $('.dataTables_filter').hide();
          $('#tableSearch').on('keyup change clear', function () {
            if (table.search() !== this.value) {
              table.search(this.value).draw();
            }
          });
        }
      });

      // 🔹 Ação de Inativar/Reativar usuário
      $(document).on('click', '.btn-toggle-status', function () {
        const id = $(this).data('id');
        const novoStatus = $(this).data('status');
        const acao = (novoStatus === 'inativo') ? 'inativar' : 'reativar';

        Swal.fire({
          title: 'Confirmação',
          text: `Deseja realmente ${acao} este usuário?`,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Sim',
          cancelButtonText: 'Cancelar'
        }).then(result => {
          if (result.isConfirmed) {
            $.post(`${BASE_URL}/proxy/usuario/status_usuario.php`, {
              idUsuario: id,
              statusUsuario: novoStatus
            }, function (resp) {
              if (resp.success) {
                Swal.fire('Sucesso', resp.mensagem, 'success').then(() => location.reload());
              } else {
                Swal.fire('Erro', resp.error || 'Falha ao atualizar status.', 'error');
              }
            }, 'json').fail(() => {
              Swal.fire('Erro', 'Falha na comunicação com o servidor.', 'error');
            });
          }
        });
      });
    });