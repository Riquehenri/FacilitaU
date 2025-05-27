class FacilitauIA {
    constructor() {
        this.apiKey = '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'; // Sua API key
        this.apiUrl = 'https://api.openai.com/v1/chat/completions'; // Endpoint da OpenAI
        this.apiUrl = 'http://localhost/facilitau/api_calendario.php'; // Ajuste para o URL do seu servidor
    }

    async processarComando(comando) {
        const payload = {
            model: '', // Modelo da OpenAI, ajuste se necessário
            messages: [
                {
                    role: 'system',
                    content: 'Você é uma assistente que ajuda a gerenciar um calendário escolar. Responda em português e interprete comandos como "Crie um plano para [matéria] até [data]" ou "Criar aviso para [evento]" para gerar JSON com tipo_evento, data_inicio, data_fim, descrição e prioridade. Exemplo de resposta: {"tipo_evento": "plano", "data_inicio": "2025-05-27T16:00:00Z", "data_fim": "2025-05-30T16:00:00Z", "descrição": "Plano para matemática", "prioridade": 1}. Se o comando não for reconhecido, retorne: "Comando não reconhecido."'
                },
                {
                    role: 'user',
                    content: comando
                }
            ],
            max_tokens: 150
        };

        try {
            const response = await fetch(this.apiUrl, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${this.apiKey}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();
            const content = data.choices[0].message.content;

            // Tentar interpretar a resposta como JSON
            let result;
            try {
                result = JSON.parse(content);
            } catch (e) {
                return content; // Retorna a string se não for JSON
            }

            return this.enviarParaBackend(result, `Ação realizada: ${result.descrição}`);
        } catch (error) {
            return 'Erro ao processar o comando com a API.';
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
            return result.success ? mensagemSucesso : 'Erro ao processar o comando no backend.';
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