class FacilitauIA {
    constructor() {
        this.apiUrl = 'http://localhost/facilitau/api_calendario.php'; // Ajuste para o URL do seu servidor
    }

    processarComando(comando) {
        comando = comando.toLowerCase().trim();

        // Identificar intent e extrair entidades com regex
        let intent = 'desconhecido';
        let materia = 'matéria não especificada';
        let dataFim = null;
        let evento = 'evento não especificado';

        // Intent: Criar plano
        const planoMatch = comando.match(/(criar|sugerir)\s+(plano|plano de estudo)\s+(para|de)\s+(\w+)\s*(até|na)?\s*(\w+\-?\w*)/i);
        if (planoMatch) {
            intent = 'criar_plano';
            materia = planoMatch[4] || materia;
            dataFim = planoMatch[6];
            if (dataFim) {
                const dias = { 'amanhã': 1, 'sexta-feira': 4, 'semana': 7, 'próxima semana': 7 };
                const offset = dias[dataFim.toLowerCase()] || 7;
                dataFim = new Date(Date.now() + offset * 24 * 60 * 60 * 1000).toISOString().slice(0, 19).replace('T', ' ');
            }
        }

        // Intent: Criar aviso
        const avisoMatch = comando.match(/(criar|adicionar)\s+(aviso)\s+(para)\s+(\w+)/i);
        if (avisoMatch) {
            intent = 'criar_aviso';
            evento = avisoMatch[4] || evento;
        }

        return this.interpretarComando(intent, materia, dataFim, evento);
    }

    interpretarComando(intent, materia, dataFim, evento) {
        switch (intent) {
            case 'criar_plano':
                const plano = {
                    tipo_evento: 'plano',
                    data_inicio: new Date().toISOString().slice(0, 19).replace('T', ' '),
                    data_fim: dataFim || new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString().slice(0, 19).replace('T', ' '),
                    descricao: `Plano de estudo para ${materia}`,
                    prioridade: 1
                };
                return this.enviarParaBackend(plano, `Plano criado para ${materia} até ${dataFim ? new Date(dataFim).toLocaleDateString() : 'uma semana'}. Estude 1 hora por dia!`);

            case 'criar_aviso':
                const aviso = {
                    tipo_evento: 'aviso',
                    data_inicio: new Date().toISOString().slice(0, 19).replace('T', ' '),
                    descricao: `Lembrete: ${evento} está chegando!`,
                    prioridade: 3
                };
                return this.enviarParaBackend(aviso, `Aviso criado: ${aviso.descricao}`);

            default:
                return 'Comando não reconhecido. Tente "Crie um plano para [matéria] até [data]" ou "Criar aviso para [evento]". Exemplos: "Crie um plano para matemática até sexta-feira" ou "Criar aviso para prova".';
        }
    }

    async enviarParaBackend(dados, mensagemSucesso) {
        try {
            const response = await fetch(this.apiUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dados)
            });
            const result = await response.json();
            return result.success ? mensagemSucesso : 'Erro ao processar o comando.';
        } catch (error) {
            return 'Erro ao conectar com o servidor.';
        }
    }
}

const ia = new FacilitauIA();

// Função para interagir com a IA (a ser chamada de calendario.js)
window.interagirComIA = async (comando) => {
    const resultado = await ia.processarComando(comando);
    return resultado;
};