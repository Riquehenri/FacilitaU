
function formatarTelefone(input) {
    // Remove tudo que não é dígito
    let telefone = input.value.replace(/\D/g, '');
    
    // Aplica a máscara (XX) XXXXX-XXXX
    if (telefone.length > 2) {
        telefone = '(' + telefone.substring(0, 2) + ') ' + telefone.substring(2);
    }
    if (telefone.length > 10) {
        telefone = telefone.substring(0, 10) + '-' + telefone.substring(10, 14);
    }
    
    input.value = telefone;
}

function validarFormulario() {
    // Validar telefone
    const telefone = document.getElementById('telefone').value.replace(/\D/g, '');
    if (telefone.length != 11 || telefone[2] != '9') {
        alert('Telefone inválido. Informe um número com DDD e 9 dígitos.');
        return false;
    }
    
    // Validar data de nascimento
    const dataNascimento = new Date(document.getElementById('data_nascimento').value);
    const dataMinima = new Date();
    dataMinima.setFullYear(dataMinima.getFullYear() - 16);
    
    if (dataNascimento > dataMinima) {
        alert('Você deve ter pelo menos 16 anos.');
        return false;
    }
    
    return true;
}
