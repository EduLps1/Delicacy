<!DOCTYPE html>
<html>
<head>
    <title>Reservas</title>
</head>
<body>

<h1>Lista de Reservas</h1>

<table border="1">
    <tr>
        <th>Nome</th>
        <th>Data</th>
        <th>Hora</th>
        <th>Pessoas</th>
        <th>Status</th>
        <th>Ação</th>
    </tr>

    <?php foreach ($reservas as $r): ?>
        <tr>
            <td><?= $r['nome_cliente'] ?></td>
            <td><?= $r['data_reserva'] ?></td>
            <td><?= $r['hora_reserva'] ?></td>
            <td><?= $r['quantidade_pessoas'] ?></td>
            <td><?= $r['status'] ?></td>
            <td>
                <a href="?action=cancelar&id=<?= $r['id'] ?>">Cancelar</a>
            </td>
        </tr>
    <?php endforeach; ?>

</table>

<h2>Nova Reserva</h2>

<form method="POST" action="?action=criar">
    Nome: <input type="text" name="nome"><br>
    Data: <input type="date" name="data"><br>
    Hora: <input type="time" name="hora"><br>
    Pessoas: <input type="number" name="qtd"><br>
    <button type="submit">Reservar</button>
</form>

</body>
</html>
