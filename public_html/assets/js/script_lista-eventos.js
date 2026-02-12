    $(document).ready(function() {
        var table = $('#eventosTable').DataTable({
            language: { url: '/../asy/assets/js/datatables/pt-BR.json' },
            dom: 'Bfrtip',
            buttons: [
                { extend: 'copy', text: '<i class="fas fa-copy"></i> Copiar' },
                { extend: 'csv', text: '<i class="fas fa-file-csv"></i> CSV' },
                { extend: 'excel', text: '<i class="fas fa-file-excel"></i> Excel' },
                { extend: 'pdf', text: '<i class="fas fa-file-pdf"></i> PDF' },
                { extend: 'print', text: '<i class="fas fa-print"></i> Imprimir' },
                { extend: 'colvis', text: '<i class="fas fa-columns"></i> Colunas' }
            ],
            ordering: true,
            paging: true,
            info: true,
            responsive: true,
            lengthChange: true,
            searching: true,
            initComplete: function() {
                $('.dataTables_filter').hide();
                $('#tableSearch').on('keyup change clear', function() {
                    if(table.search() !== this.value) table.search(this.value).draw();
                });
            }
        });

        // 🔹 Inativar/Reativar curso
        $(document).on('click', '.btn-toggle-status', function() {
            const id = $(this).data('id');
            const novoStatus = $(this).data('status');
            const acao = (novoStatus === 'inativo') ? 'inativar' : 'reativar';

            Swal.fire({
                title: 'Confirmação',
                text: `Deseja realmente ${acao} este curso?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sim',
                cancelButtonText: 'Cancelar'
            }).then(result => {
                if(result.isConfirmed){
                    $.post('<?= BASE_URL ?>/proxy/cursos/status_curso.php', {idCurso:id, statusCurso:novoStatus}, function(resp){
                        if(resp.success){
                            Swal.fire('Sucesso', resp.mensagem, 'success').then(()=>location.reload());
                        }else{
                            Swal.fire('Erro', resp.error || 'Falha ao atualizar status.', 'error');
                        }
                    }, 'json').fail(()=>Swal.fire('Erro', 'Falha na comunicação com o servidor.', 'error'));
                }
            });
        });
    });