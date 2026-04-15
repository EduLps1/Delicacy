function adicionarItemCardapio($conn, $nome, $descricao, $preco, $categoria) {
    // Validação básica
    if (empty($nome) || empty($preco)) {
        return "Nome e preço são obrigatórios.";
    }

    if (!is_numeric($preco)) {
        return "Preço inválido.";
    }

    // Query preparada (segurança contra SQL Injection)
    $sql = "INSERT INTO cardapio (nome, descricao, preco, categoria) VALUES (?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssds", $nome, $descricao, $preco, $categoria);

    if ($stmt->execute()) {
        return "Item adicionado com sucesso!";
    } else {
        return "Erro ao adicionar item: " . $stmt->error;
    }
}