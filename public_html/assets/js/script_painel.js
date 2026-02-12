document.addEventListener('DOMContentLoaded', function() {
  const calendarEl = document.getElementById('calendar');
  const listaEl = document.getElementById('lista-eventos');

  // Converte status para classe CSS
  function statusClass(status) {
    switch(status.toLowerCase()) {
      case 'ativo': return 'ativo';
      case 'cancelado': return 'cancelado';
      case 'finalizado': return 'finalizado';
      case 'em andamento': return 'em-andamento';
      default: return '';
    }
  }


  // Popula lista lateral
  listaEl.innerHTML = '';
  eventos.forEach(ev => {
    const li = document.createElement('li');
    li.className = `list-group-item status-${statusClass(ev.status)}`;
    li.textContent = `${new Date(ev.dataHoraInicio).toLocaleDateString('pt-BR')} ${new Date(ev.dataHoraInicio).toLocaleTimeString('pt-BR', {hour: '2-digit', minute:'2-digit'})} - ${ev.nomeEvento} (${ev.status})`;
    li.style.cursor = 'pointer';
    li.addEventListener('click', () => mostrarModal(ev));
    listaEl.appendChild(li);
  });

  // Inicializa FullCalendar
  const calendar = new FullCalendar.Calendar(calendarEl, {
    locale: 'pt-br',
    themeSystem: 'bootstrap',
    initialView: 'dayGridMonth',
    height: 450,
    headerToolbar: { left: 'prev,next today', center: 'title', right: '' },
    buttonText: { today: 'Hoje' },
    events: eventos.map(ev => ({
      id: ev.idEvento,
      title: ev.nomeEvento,
      start: ev.dataHoraInicio,
      end: ev.dataHoraFim,
      className: `fc-event-${statusClass(ev.status)}`
    })),
    eventClick: function(info) {
      const ev = eventos.find(e => e.idEvento == info.event.id);
      if(ev) mostrarModal(ev);
    }
  });

  calendar.render();

  // Modal
  function mostrarModal(ev) {
    document.getElementById('evNome').textContent = ev.nomeEvento;
    document.getElementById('evTipo').textContent = ev.tipoEvento;
    document.getElementById('evInicio').textContent = new Date(ev.dataHoraInicio).toLocaleString('pt-BR');
    document.getElementById('evFim').textContent = new Date(ev.dataHoraFim).toLocaleString('pt-BR');
    document.getElementById('evMinisterio').textContent = ev.ministerio || '-';
    document.getElementById('evResponsavel').textContent = ev.responsavel || '-';
    document.getElementById('evCriadoPor').textContent = ev.criadoPor || '-';
    document.getElementById('evStatus').textContent = ev.status;
    document.getElementById('evObs').textContent = ev.observacao || '-';
    $('#modalEvento').modal('show');
  }
});