<?php

require_once "conexao.php";

header('Content-Type: application/json');

function agendarConsulta($conexao) {
    $dados = json_decode(file_get_contents('php://input'), true);

    if (isset($dados['id_animal'], $dados['id_veterinario'], $dados['data_hora'], $dados['tipo_consulta'])) {
        $id_animal = $conexao->real_escape_string($dados['id_animal']);
        $id_veterinario = $conexao->real_escape_string($dados['id_veterinario']);
        $data_hora = $conexao->real_escape_string($dados['data_hora']);
        $tipo_consulta = $conexao->real_escape_string($dados['tipo_consulta']);

        // Usando prepared statements para prevenir SQL injection
        $stmt = $conexao->prepare("INSERT INTO Consulta (id_animal, id_veterinario, data_hora, tipo_consulta) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $id_animal, $id_veterinario, $data_hora, $tipo_consulta);

        if ($stmt->execute()) {
            $resposta = array('mensagem' => 'Consulta agendada com sucesso!');
            http_response_code(201); // Created
            echo json_encode($resposta);
        } else {
            $resposta = array('mensagem' => 'Erro ao agendar consulta: ' . $stmt->error);
            http_response_code(500); // Internal Server Error
            echo json_encode($resposta);
        }

        $stmt->close();
    } else {
        $resposta = array('mensagem' => 'Dados incompletos para agendar consulta.');
        http_response_code(400); // Bad Request
        echo json_encode($resposta);
    }
}

function listarConsultas($conexao, $id_usuario) {
    // Consulta SQL para obter as consultas do usuário, incluindo dados do animal e veterinário
    $sql = "SELECT c.id_consulta, c.data_hora, c.tipo_consulta, a.nome AS nome_animal, v.nome AS nome_veterinario 
            FROM Consulta c
            JOIN Animal a ON c.id_animal = a.id_animal
            JOIN Veterinario v ON c.id_veterinario = v.id_veterinario
            WHERE a.id_usuario = ?"; 

    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $consultas = array();
        while ($linha = $resultado->fetch_assoc()) {
            $consultas[] = $linha;
        }
        return json_encode($consultas);
    } else {
        return json_encode(array('mensagem' => 'Nenhuma consulta encontrada para este usuário.'));
    }
}

// Roteamento das requisições (mantido como estava)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    agendarConsulta($conexao);
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id_usuario'])) {
    $id_usuario = $conexao->real_escape_string($_GET['id_usuario']);
    echo listarConsultas($conexao, $id_usuario);
} else {
    http_response_code(400); // Bad Request
    echo json_encode(array('mensagem' => 'Método inválido ou parâmetros ausentes.'));
}

$conexao->close();

?>