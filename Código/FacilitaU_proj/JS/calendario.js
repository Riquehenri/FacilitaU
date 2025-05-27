document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        events: 'api_calendario.php',
        eventClick: function(info) {
            const evento = info.event;
            const tags = evento.extendedProps.tags ? evento.extendedProps.tags.split(',') : [];
            const confirmEdit = confirm(`Evento: ${evento.title}\nDescrição: ${evento.extendedProps.descricao || 'Nenhuma'}\nTurmas: ${tags.join(', ') || 'Nenhuma'}\n\nDeseja editar ou excluir este evento?`);
            if (confirmEdit) {
                const acao = prompt("Digite 'editar' para editar ou 'excluir' para excluir o evento:");
                if (acao && acao.toLowerCase() === 'editar') {
                    const tipo = prompt("Novo tipo (atividade, plano, aviso):", evento.title.toLowerCase());
                    const dataInicio = prompt("Nova data de início (YYYY-MM-DD HH:mm):", evento.start.toISOString().slice(0, 16).replace('T', ' '));
                    const dataFim = prompt("Nova data de fim (opcional, YYYY-MM-DD HH:mm):", evento.end ? evento.end.toISOString().slice(0, 16).replace('T', ' ') : '');
                    const descricao = prompt("Nova descrição:", evento.extendedProps.descricao || '');
                    const tagsInput = prompt("Novas turmas (IDs separados por vírgula):", tags.join(','));

                    const eventoAtualizado = {
                        evento_id: evento.id,
                        tipo_evento: tipo,
                        data_inicio: dataInicio + ':00',
                        data_fim: dataFim ? dataFim + ':00' : null,
                        descricao: descricao,
                        tags: tagsInput ? tagsInput.split(',').map(Number) : []
                    };
                    fetch('api_calendario.php', {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(eventoAtualizado)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Evento atualizado com sucesso!');
                            calendar.refetchEvents();
                        } else {
                            alert('Erro ao atualizar evento: ' + (data.error || 'Desconhecido'));
                        }
                    });
                } else if (acao && acao.toLowerCase() === 'excluir') {
                    fetch('api_calendario.php', {
                        method: 'DELETE',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ evento_id: evento.id })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Evento excluído com sucesso!');
                            calendar.refetchEvents();
                        } else {
                            alert('Erro ao excluir evento: ' + (data.error || 'Desconhecido'));
                        }
                    });
                }
            }
        },
        eventDidMount: function(info) {
            if (info.event.extendedProps.tags) {
                info.el.querySelector('.fc-event-title').innerHTML += 
                    '<br><small>' + info.event.extendedProps.tags + '</small>';
            }
        }
    });
    calendar.render();

    fetch('api_tags.php')
        .then(response => response.json())
        .then(tags => {
            const selectFiltro = document.getElementById('tag-filtro');
            const selectForm = document.getElementById('tags');
            tags.forEach(tag => {
                const optionFiltro = document.createElement('option');
                optionFiltro.value = tag.tag_id;
                optionFiltro.textContent = tag.nome;
                selectFiltro.appendChild(optionFiltro);

                const optionForm = document.createElement('option');
                optionForm.value = tag.tag_id;
                optionForm.textContent = tag.nome;
                selectForm.appendChild(optionForm);
            });
        });

    document.getElementById('tag-filtro').addEventListener('change', function() {
        const tagIds = Array.from(this.selectedOptions).map(opt => opt.value).join(',');
        calendar.getEvents().forEach(event => event.remove());
        fetch(`api_calendario.php?tag_id=${tagIds}`)
            .then(response => response.json())
            .then(events => calendar.addEventSource(events));
    });

    document.getElementById('form-evento').addEventListener('submit', function(e) {
        e.preventDefault();
        const evento = {
            tipo_evento: document.getElementById('tipo-evento').value,
            data_inicio: document.getElementById('data-inicio').value.replace('T', ' ') + ':00',
            data_fim: document.getElementById('data-fim').value ? document.getElementById('data-fim').value.replace('T', ' ') + ':00' : null,
            descricao: document.getElementById('descricao').value,
            tags: Array.from(document.getElementById('tags').selectedOptions).map(opt => opt.value)
        };
        fetch('api_calendario.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(evento)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Evento adicionado com sucesso!');
                calendar.refetchEvents();
            } else {
                alert('Erro ao adicionar evento: ' + (data.error || 'Desconhecido'));
            }
        });
    });
});

function interagirIA() {
    const comando = document.getElementById('ia-comando').value;
    const sugestaoTexto = document.getElementById('sugestao-texto');
    sugestaoTexto.textContent = 'Processando...';

    interagirComIA(comando).then(resultado => {
        sugestaoTexto.textContent = resultado;
        if (resultado.includes('criado')) {
            calendar.refetchEvents();
        }
    }).catch(() => {
        sugestaoTexto.textContent = 'Erro ao processar o comando. Tente novamente.';
    });
}

function gerarAviso() {
    fetch('api_calendario.php')
        .then(response => response.json())
        .then(events => {
            const hoje = new Date();
            const proximos = events.filter(e => new Date(e.start) > hoje).sort((a, b) => new Date(a.start) - new Date(b.start));
            if (proximos.length) {
                const evento = proximos[0];
                const aviso = {
                    tipo_evento: 'aviso',
                    data_inicio: new Date().toISOString().slice(0, 19).replace('T', ' '),
                    descricao: `Lembrete: ${evento.title} está chegando em ${new Date(evento.start).toLocaleDateString()}!`,
                    tags: evento.tags ? evento.tags.split(',') : []
                };
                fetch('api_calendario.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(aviso)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Aviso criado com sucesso!');
                        calendar.refetchEvents();
                    }
                });
            }
        });
}