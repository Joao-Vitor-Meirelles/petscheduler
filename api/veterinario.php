<?php

require_once "conexao.php";

header('Content-Type: application/json');

function listarVeterinarios($conexao) {
    $sql = "SELECT * FROM Veterinario";
    $resultado = $conexao->query($sql);

    if ($resultado->num_rows > 0) {
        $veterinarios = array();
        while ($linha = $resultado->fetch_assoc()) {
            $veterinarios[] = $linha;
        }
        return json_encode($veterinarios);
    } else {
        return json_encode(array('mensagem' => 'Nenhum veterinário encontrado.'));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo listarVeterinarios($conexao);
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(array('mensagem' => 'Método não permitido.'));
}

$conexao->close();

?>