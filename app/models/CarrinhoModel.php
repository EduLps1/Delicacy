<?php

class Carrinho {
    private $dataFile = '../data/carrinho.json';

    private function lerCarrinho() {
        if (!file_exists($this->dataFile)) {
            return ['carrinho' => []];
        }
        $json = file_get_contents($this->dataFile);
        return json_decode($json, true) ?? ['carrinho' => []];
    }

    private function salvarCarrinho($data) {
        file_put_contents($this->dataFile, json_encode($data, JSON_PRETTY_PRINT));
    }

    // Adicionar item ao carrinho
    public function adicionar($dados) {
        $carrinho = $this->lerCarrinho();

        $item = [
            'id'          => uniqid('item_'),
            'produto_id'  => $dados['produto_id'] ?? null,
            'nome'        => $dados['nome'] ?? '',
            'preco'       => (float)($dados['preco'] ?? 0),
            'quantidade'  => (int)($dados['quantidade'] ?? 1),
            'observacao'  => $dados['observacao'] ?? '',
            'adicionado_em' => date('Y-m-d H:i:s')
        ];

        // Se o produto já existe, aumenta a quantidade
        foreach ($carrinho['carrinho'] as &$existing) {
            if ($existing['produto_id'] == $item['produto_id']) {
                $existing['quantidade'] += $item['quantidade'];
                $this->salvarCarrinho($carrinho);
                return $existing;
            }
        }

        $carrinho['carrinho'][] = $item;
        $this->salvarCarrinho($carrinho);

        return $item;
    }

    // Listar itens do carrinho
    public function listar() {
        return $this->lerCarrinho()['carrinho'];
    }

    // Remover um item
    public function remover($id) {
        $carrinho = $this->lerCarrinho();
        $carrinho['carrinho'] = array_filter($carrinho['carrinho'], fn($item) => $item['id'] !== $id);
        $this->salvarCarrinho($carrinho);
        return true;
    }

    // Limpar todo o carrinho
    public function limpar() {
        $this->salvarCarrinho(['carrinho' => []]);
        return true;
    }

    // Calcular total
    public function total() {
        $carrinho = $this->lerCarrinho()['carrinho'];
        return array_reduce($carrinho, fn($sum, $item) => $sum + ($item['preco'] * $item['quantidade']), 0);
    }
}