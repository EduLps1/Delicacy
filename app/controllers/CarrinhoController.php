<?php

require_once '../models/Carrinho.php';

class CarrinhoController {
    private $carrinho;

    public function __construct() {
        $this->carrinho = new Carrinho();
    }

    public function index() {
        header('Content-Type: application/json');
        echo json_encode([
            'carrinho' => $this->carrinho->listar(),
            'total'    => $this->carrinho->total()
        ]);
    }

    public function store() {
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input['nome']) || empty($input['preco'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Nome e preço são obrigatórios']);
            return;
        }

        $item = $this->carrinho->adicionar($input);

        echo json_encode([
            'success' => true,
            'message' => 'Item adicionado ao carrinho!',
            'item'    => $item
        ]);
    }

    public function destroy($id) {
        header('Content-Type: application/json');
        $this->carrinho->remover($id);
        echo json_encode(['success' => true, 'message' => 'Item removido do carrinho']);
    }

    public function clear() {
        header('Content-Type: application/json');
        $this->carrinho->limpar();
        echo json_encode(['success' => true, 'message' => 'Carrinho limpo']);
    }
}