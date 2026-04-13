document.addEventListener('DOMContentLoaded', () => {
  const btnLogout = document.getElementById('btnLogout');

  if (!btnLogout) return;

  btnLogout.addEventListener('click', (e) => {
    e.preventDefault();

    Swal.fire({
      title: 'Deseja realmente sair?',
      text: 'Sua sessão será encerrada.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Sim, sair',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = '/asy/proxy/logout.php';
      }
    });
  });
});
