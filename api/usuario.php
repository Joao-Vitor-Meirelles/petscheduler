<?php

require_once "conexao.php"; // Inclui o arquivo de conexão

header('Content-Type: application/json'); // Define o tipo de conteúdo da resposta como JSON

// Função para criar um novo usuário
function criarUsuario($conexao) {
    // Obtém os dados do usuário enviados no corpo da requisição
    $dados = json_decode(file_get_contents('php://input'), true);

    // Verifica se os dados foram recebidos corretamente
    if (isset($dados['nome'], $dados['telefone'], $dados['email'], $dados['senha'])) {
        $nome = $conexao->real_escape_string($dados['nome']);
        $telefone = $conexao->real_escape_string($dados['telefone']);
        $email = $conexao->real_escape_string($dados['email']);
        $senha = password_hash($conexao->real_escape_string($dados['senha']), PASSWORD_DEFAULT); // Hash da senha

        // Prepara a query SQL para inserir o usuário no banco de dados (utilizando prepared statements)
        $stmt = $conexao->prepare("INSERT INTO Usuario (nome, telefone, email, senha) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nome, $telefone, $email, $senha);

        if ($stmt->execute()) {
            $resposta = array('mensagem' => 'Usuário cadastrado com sucesso!');
            http_response_code(201); // Created
            echo json_encode($resposta);
        } else {
            $resposta = array('mensagem' => 'Erro ao cadastrar usuário: ' . $stmt->error);
            http_response_code(500); // Internal Server Error
            echo json_encode($resposta);
        }

        $stmt->close();
    } else {
        $resposta = array('mensagem' => 'Dados incompletos para criar usuário.');
        http_response_code(400); // Bad Request
        echo json_encode($resposta);
    }
}

// Função para listar os usuários
function listarUsuarios($conexao) {
    $sql = "SELECT * FROM Usuario";
    $resultado = $conexao->query($sql);

    if ($resultado->num_rows > 0) {
        $usuarios = array();
        while ($linha = $resultado->fetch_assoc()) {
            $usuarios[] = $linha;
        }
        return json_encode($usuarios);
    } else {
        return json_encode(array('mensagem' => 'Nenhum usuário encontrado.'));
    }
}

// Função para buscar um usuário pelo ID
function buscarUsuario($conexao, $id_usuario) {
    $sql = "SELECT * FROM Usuario WHERE id_usuario = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $usuario = $resultado->fetch_assoc();
        return json_encode($usuario);
    } else {
        http_response_code(404); // Not Found
        return json_encode(array('mensagem' => 'Usuário não encontrado.'));
    }
}

// Função para atualizar os dados de um usuário
function atualizarUsuario($conexao, $id_usuario) {
    $dados = json_decode(file_get_contents('php://input'), true);

    if (isset($dados['nome'], $dados['telefone'], $dados['email'], $dados['senha'])) {
        $nome = $conexao->real_escape_string($dados['nome']);
        $telefone = $conexao->real_escape_string($dados['telefone']);
        $email = $conexao->real_escape_string($dados['email']);
        $senha = password_hash($conexao->real_escape_string($dados['senha']), PASSWORD_DEFAULT); // Hash da senha

        $stmt = $conexao->prepare("UPDATE Usuario SET nome = ?, telefone = ?, email = ?, senha = ? WHERE id_usuario = ?");
        $stmt->bind_param("ssssi", $nome, $telefone, $email, $senha, $id_usuario);

        if ($stmt->execute()) {
            $resposta = array('mensagem' => 'Dados do usuário atualizados com sucesso!');
            echo json_encode($resposta);
        } else {
            http_response_code(500); // Internal Server Error
            $resposta = array('mensagem' => 'Erro ao atualizar os dados do usuário: ' . $stmt->error);
            echo json_encode($resposta);
        }

        $stmt->close();
    } else {
        http_response_code(400); // Bad Request
        $resposta = array('mensagem' => 'Dados incompletos para atualizar usuário.');
        echo json_encode($resposta);
    }
}

// Função para deletar um usuário pelo ID
function deletarUsuario($conexao, $id_usuario) {
    $sql = "DELETE FROM Usuario WHERE id_usuario = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $id_usuario);

    if ($stmt->execute()) {
        $resposta = array('mensagem' => 'Usuário deletado com sucesso!');
        echo json_encode($resposta);
    } else {
        http_response_code(500); // Internal Server Error
        $resposta = array('mensagem' => 'Erro ao deletar usuário: ' . $stmt->error);
        echo json_encode($resposta);
    }

    $stmt->close();
}

// ... (código de roteamento) ...

?>