<?php

declare(strict_types=1);

final class MenuModel
{
    public function getCategories(): array
    {
        return [
            'Todos',
            'Pizzas',
            'Hambúrgueres',
            'Espetinhos',
            'Petiscos',
            'Sobremesas',
            'Bebidas',
        ];
    }

    public function getCategoryLabels(): array
    {
        return [
            'Pizzas' => 'Pizzas',
            'Hambúrgueres' => ' Hambúrgueres',
            'Espetinhos' => ' Espetinhos',
            'Petiscos' => ' Petiscos',
            'Sobremesas' => ' Sobremesas',
            'Bebidas' => ' Bebidas',
        ];
    }

    public function getDishes(): array
    {
        return [
            [
                'id' => 1,
                'name' => 'Pizza Margherita',
                'description' => 'Molho de tomate, mussarela de búfala, manjericão fresco e azeite',
                'price' => 42.9,
                'imageKey' => 'dish-picanha',
                'category' => 'Pizzas',
                'popular' => true,
            ],
            [
                'id' => 2,
                'name' => 'Pizza Calabresa',
                'description' => 'Calabresa fatiada, cebola roxa, azeitonas e orégano',
                'price' => 38.9,
                'imageKey' => 'dish-picanha',
                'category' => 'Pizzas',
            ],
            [
                'id' => 3,
                'name' => 'Pizza Quatro Queijos',
                'description' => 'Mussarela, gorgonzola, parmesão e catupiry',
                'price' => 45.9,
                'imageKey' => 'dish-picanha',
                'category' => 'Pizzas',
            ],
            [
                'id' => 4,
                'name' => 'Smash Burger Clássico',
                'description' => 'Blend de carne, queijo cheddar, cebola caramelizada e molho especial',
                'price' => 32.9,
                'imageKey' => 'dish-salmon',
                'category' => 'Hambúrgueres',
                'popular' => true,
            ],
            [
                'id' => 5,
                'name' => 'Burger Bacon Supreme',
                'description' => 'Duplo blend, bacon crocante, queijo e barbecue defumado',
                'price' => 39.9,
                'imageKey' => 'dish-salmon',
                'category' => 'Hambúrgueres',
            ],
            [
                'id' => 6,
                'name' => 'Espetinho de Picanha',
                'description' => 'Picanha temperada na brasa com farofa e vinagrete',
                'price' => 14.9,
                'imageKey' => 'dish-picanha',
                'category' => 'Espetinhos',
                'popular' => true,
            ],
            [
                'id' => 7,
                'name' => 'Espetinho de Frango',
                'description' => 'Frango marinado com ervas e limão, grelhado na brasa',
                'price' => 10.9,
                'imageKey' => 'dish-picanha',
                'category' => 'Espetinhos',
            ],
            [
                'id' => 8,
                'name' => 'Espetinho Misto',
                'description' => 'Carne, frango e linguiça com molho chimichurri',
                'price' => 16.9,
                'imageKey' => 'dish-picanha',
                'category' => 'Espetinhos',
            ],
            [
                'id' => 9,
                'name' => 'Coxinha Cremosa',
                'description' => 'Coxinha crocante recheada com frango desfiado e catupiry',
                'price' => 8.9,
                'imageKey' => 'dish-appetizer',
                'category' => 'Petiscos',
                'popular' => true,
            ],
            [
                'id' => 10,
                'name' => 'Bolinho de Bacalhau',
                'description' => 'Bolinhos dourados de bacalhau com ervas finas',
                'price' => 12.9,
                'imageKey' => 'dish-appetizer',
                'category' => 'Petiscos',
            ],
            [
                'id' => 11,
                'name' => 'Porção de Batata Frita',
                'description' => 'Batatas fritas crocantes com cheddar e bacon',
                'price' => 24.9,
                'imageKey' => 'dish-appetizer',
                'category' => 'Petiscos',
            ],
            [
                'id' => 12,
                'name' => 'Brigadeiro Gourmet',
                'description' => 'Brigadeiros artesanais sortidos com cobertura premium',
                'price' => 6.9,
                'imageKey' => 'dish-dessert',
                'category' => 'Sobremesas',
                'popular' => true,
            ],
            [
                'id' => 13,
                'name' => 'Petit Gâteau',
                'description' => 'Bolo de chocolate quente com sorvete de creme e calda',
                'price' => 24.9,
                'imageKey' => 'dish-dessert',
                'category' => 'Sobremesas',
            ],
            [
                'id' => 14,
                'name' => 'Suco Tropical',
                'description' => 'Mix de frutas tropicais: manga, maracujá e morango',
                'price' => 14.9,
                'imageKey' => 'dish-drinks',
                'category' => 'Bebidas',
                'popular' => true,
            ],
            [
                'id' => 15,
                'name' => 'Limonada Suíça',
                'description' => 'Limonada cremosa com leite condensado e hortelã',
                'price' => 12.9,
                'imageKey' => 'dish-drinks',
                'category' => 'Bebidas',
            ],
            [
                'id' => 16,
                'name' => 'Refrigerante Artesanal',
                'description' => 'Refrigerante de gengibre com limão e especiarias',
                'price' => 9.9,
                'imageKey' => 'dish-drinks',
                'category' => 'Bebidas',
            ],
        ];
    }
}