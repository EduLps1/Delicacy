import dishPicanha from "@/assets/dish-picanha.jpg";
import dishDrinks from "@/assets/dish-drinks.jpg";
import dishDessert from "@/assets/dish-dessert.jpg";
import dishAppetizer from "@/assets/dish-appetizer.jpg";
import dishSalmon from "@/assets/dish-salmon.jpg";

export type Category = "Todos" | "Pizzas" | "Hambúrgueres" | "Espetinhos" | "Petiscos" | "Sobremesas" | "Bebidas";

export interface Dish {
  id: number;
  name: string;
  description: string;
  price: number;
  image: string;
  category: Exclude<Category, "Todos">;
  popular?: boolean;
}

export const categories: Category[] = [
  "Todos",
  "Pizzas",
  "Hambúrgueres",
  "Espetinhos",
  "Petiscos",
  "Sobremesas",
  "Bebidas",
];

export const categoryLabels: Record<Exclude<Category, "Todos">, string> = {
  Pizzas: "🍕 Nossas Pizzas",
  "Hambúrgueres": "🍔 Hambúrgueres Artesanais",
  Espetinhos: "🍢 Espetinhos na Brasa",
  Petiscos: "🍟 Petiscos para Compartilhar",
  Sobremesas: "🍫 Sobremesas Irresistíveis",
  Bebidas: "🥤 Bebidas Refrescantes",
};

export const dishes: Dish[] = [
  {
    id: 1,
    name: "Pizza Margherita",
    description: "Molho de tomate, mussarela de búfala, manjericão fresco e azeite",
    price: 42.9,
    image: dishPicanha,
    category: "Pizzas",
    popular: true,
  },
  {
    id: 2,
    name: "Pizza Calabresa",
    description: "Calabresa fatiada, cebola roxa, azeitonas e orégano",
    price: 38.9,
    image: dishPicanha,
    category: "Pizzas",
  },
  {
    id: 3,
    name: "Pizza Quatro Queijos",
    description: "Mussarela, gorgonzola, parmesão e catupiry",
    price: 45.9,
    image: dishPicanha,
    category: "Pizzas",
  },
  {
    id: 4,
    name: "Smash Burger Clássico",
    description: "Blend de carne, queijo cheddar, cebola caramelizada e molho especial",
    price: 32.9,
    image: dishSalmon,
    category: "Hambúrgueres",
    popular: true,
  },
  {
    id: 5,
    name: "Burger Bacon Supreme",
    description: "Duplo blend, bacon crocante, queijo e barbecue defumado",
    price: 39.9,
    image: dishSalmon,
    category: "Hambúrgueres",
  },
  {
    id: 6,
    name: "Espetinho de Picanha",
    description: "Picanha temperada na brasa com farofa e vinagrete",
    price: 14.9,
    image: dishPicanha,
    category: "Espetinhos",
    popular: true,
  },
  {
    id: 7,
    name: "Espetinho de Frango",
    description: "Frango marinado com ervas e limão, grelhado na brasa",
    price: 10.9,
    image: dishPicanha,
    category: "Espetinhos",
  },
  {
    id: 8,
    name: "Espetinho Misto",
    description: "Carne, frango e linguiça com molho chimichurri",
    price: 16.9,
    image: dishPicanha,
    category: "Espetinhos",
  },
  {
    id: 9,
    name: "Coxinha Cremosa",
    description: "Coxinha crocante recheada com frango desfiado e catupiry",
    price: 8.9,
    image: dishAppetizer,
    category: "Petiscos",
    popular: true,
  },
  {
    id: 10,
    name: "Bolinho de Bacalhau",
    description: "Bolinhos dourados de bacalhau com ervas finas",
    price: 12.9,
    image: dishAppetizer,
    category: "Petiscos",
  },
  {
    id: 11,
    name: "Porção de Batata Frita",
    description: "Batatas fritas crocantes com cheddar e bacon",
    price: 24.9,
    image: dishAppetizer,
    category: "Petiscos",
  },
  {
    id: 12,
    name: "Brigadeiro Gourmet",
    description: "Brigadeiros artesanais sortidos com cobertura premium",
    price: 6.9,
    image: dishDessert,
    category: "Sobremesas",
    popular: true,
  },
  {
    id: 13,
    name: "Petit Gâteau",
    description: "Bolo de chocolate quente com sorvete de creme e calda",
    price: 24.9,
    image: dishDessert,
    category: "Sobremesas",
  },
  {
    id: 14,
    name: "Suco Tropical",
    description: "Mix de frutas tropicais: manga, maracujá e morango",
    price: 14.9,
    image: dishDrinks,
    category: "Bebidas",
    popular: true,
  },
  {
    id: 15,
    name: "Limonada Suíça",
    description: "Limonada cremosa com leite condensado e hortelã",
    price: 12.9,
    image: dishDrinks,
    category: "Bebidas",
  },
  {
    id: 16,
    name: "Refrigerante Artesanal",
    description: "Refrigerante de gengibre com limão e especiarias",
    price: 9.9,
    image: dishDrinks,
    category: "Bebidas",
  },
];
