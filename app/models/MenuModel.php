<?php


require_once __DIR__ . '/../../config/database.php';

class ReservaModel
{

    public function criar($dados)
    {
        global $conn;

        $sql = "INSERT INTO reservas (nome_cliente, data_reserva, hora_reserva, quantidade_pessoas)
                VALUES (?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "sssi",
            $dados['nome'],
            $dados['data'],
            $dados['hora'],
            $dados['qtd']
        );

        return $stmt->execute();
    }

    public function listar()
    {
        global $conn;

        $result = $conn->query("SELECT * FROM reservas");

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function cancelar($id)
    {
        global $conn;

        $sql = "UPDATE reservas SET status='cancelada' WHERE id=?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }
}