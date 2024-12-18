<?php

require_once "conexao.php"; // Inclui o arquivo de conexão

header('Content-Type: application/json'); // Define o tipo de conteúdo da resposta como JSON

// Função para obter e sanitizar os dados da requisição
function obterDadosAnimal($conexao, $metodo) {
    $dados = json_decode(file_get_contents('php://input'), true);

    if ($metodo === 'POST' && isset($dados['nome'], $dados['especie'], $dados['raca'], $dados['data_nascimento'], $dados['pelagem'], $dados['id_usuario'])) {
        $dados['nome'] = $conexao->real_escape_string($dados['nome']);
        $dados['especie'] = $conexao->real_escape_string($dados['especie']);
        $dados['raca'] = $conexao->real_escape_string($dados['raca']);
        $dados['data_nascimento'] = $conexao->real_escape_string($dados['data_nascimento']);
        $dados['pelagem'] = $conexao->real_escape_string($dados['pelagem']);
        $dados['id_usuario'] = $conexao->real_escape_string($dados['id_usuario']);
        return $dados;
    } elseif ($metodo === 'PUT' && isset($dados['nome'], $dados['especie'], $dados['raca'], $dados['data_nascimento'], $dados['pelagem'])) {
        $dados['nome'] = $conexao->real_escape_string($dados['nome']);
        $dados['especie'] = $conexao->real_escape_string($dados['especie']);
        $dados['raca'] = $conexao->real_escape_string($dados['raca']);
        $dados['data_nascimento'] = $conexao->real_escape_string($dados['data_nascimento']);
        $dados['pelagem'] = $conexao->real_escape_string($dados['pelagem']);
        return $dados;
    } else {
        http_response_code(400); // Bad Request
        echo json_encode(array('mensagem' => 'Dados incompletos para ' . $metodo));
        exit;
    }
}

// Função para listar os animais de um usuário
function listarAnimais($conexao, $id_usuario) {
    $sql = "SELECT * FROM Animal WHERE id_usuario = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $animais = array();
        while ($linha = $resultado->fetch_assoc()) {
            $animais[] = $linha;
        }
        return json_encode($animais);
    } else {
        return json_encode(array('mensagem' => 'Nenhum animal encontrado para este usuário.'));
    }
}

// Função para buscar um animal pelo ID
function buscarAnimal($conexao, $id_animal) {
    $sql = "SELECT * FROM Animal WHERE id_animal = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $id_animal);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $animal = $resultado->fetch_assoc();
        return json_encode($animal);
    } else {
        return json_encode(array('mensagem' => 'Animal não encontrado.'));
    }
}

// Função para criar um novo animal
function criarAnimal($conexao) {
    $dados = obterDadosAnimal($conexao, 'POST');

    $stmt = $conexao->prepare("INSERT INTO Animal (nome, especie, raca, data_nascimento, pelagem, id_usuario) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssi", $dados['nome'], $dados['especie'], $dados['raca'], $dados['data_nascimento'], $dados['pelagem'], $dados['id_usuario']);

    if ($stmt->execute()) {
        http_response_code(201); // Created
        $resposta = array('mensagem' => 'Animal cadastrado com sucesso!');
        echo json_encode($resposta);
    } else {
        http_response_code(500); // Internal Server Error
        $resposta = array('mensagem' => 'Erro ao cadastrar animal: ' . $stmt->error);
        echo json_encode($resposta);
    }

    $stmt->close();
}

// Função para atualizar os dados de um animal
function atualizarAnimal($conexao, $id_animal) {
    $dados = obterDadosAnimal($conexao, 'PUT');

    $stmt = $conexao->prepare("UPDATE Animal SET nome = ?, especie = ?, raca = ?, data_nascimento = ?, pelagem = ? WHERE id_animal = ?");
    $stmt->bind_param("sssssi", $dados['nome'], $dados['especie'], $dados['raca'], $dados['data_nascimento'], $dados['pelagem'], $id_animal);

    if ($stmt->execute()) {
        $resposta = array('mensagem' => 'Dados do animal atualizados com sucesso!');
        echo json_encode($resposta);
    } else {
        http_response_code(500); // Internal Server Error
        $resposta = array('mensagem' => 'Erro ao atualizar os dados do animal: ' . $stmt->error);
        echo json_encode($resposta);
    }

    $stmt->close();
}

// Função para deletar um animal pelo ID
function deletarAnimal($conexao, $id_animal) {
    $sql = "DELETE FROM Animal WHERE id_animal = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $id_animal);

    if ($stmt->execute()) {
        $resposta = array('mensagem' => 'Animal deletado com sucesso!');
        echo json_encode($resposta);
    } else {
        http_response_code(500); // Internal Server Error
        $resposta = array('mensagem' => 'Erro ao deletar animal: ' . $stmt->error);
        echo json_encode($resposta);
    }

    $stmt->close();
}

// Funções movidas para o início do arquivo
function listarAnimais($conexao, $id_usuario) {
    // ... (código da função listarAnimais)
}

function buscarAnimal($conexao, $id_animal) {
    // ... (código da função buscarAnimal)
}

// Roteamento das requisições
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    criarAnimal($conexao);
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['id_usuario'])) {
        $id_usuario = $conexao->real_escape_string($_GET['id_usuario']);
        echo listarAnimais($conexao, $id_usuario);
    } elseif (isset($_GET['id_animal'])) {
        $id_animal = $conexao->real_escape_string($_GET['id_animal']);
        echo buscarAnimal($conexao, $id_animal);
    } else {
        http_response_code(400); // Bad Request
        echo json_encode(array('mensagem' => 'Parâmetros inválidos.'));
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT' && isset($_GET['id_animal'])) {
    $id_animal = $conexao->real_escape_string($_GET['id_animal']);
    atualizarAnimal($conexao, $id_animal);
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['id_animal'])) {
    $id_animal = $conexao->real_escape_string($_GET['id_animal']);
    deletarAnimal($conexao, $id_animal);
} else {
    http_response_code(400); // Bad Request
    echo json_encode(array('mensagem' => 'Método inválido ou parâmetros ausentes.'));
}

$conexao->close(); // Fecha a conexão com o banco de dados

?>