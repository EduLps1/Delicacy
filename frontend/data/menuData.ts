export type Category =
  | "Todos"
  | "Pizzas"
  | "Hambúrgueres"
  | "Espetinhos"
  | "Petiscos"
  | "Sobremesas"
  | "Bebidas";

export interface Dish {
  id: number;
  name: string;
  description: string;
  price: number;
  category: Exclude<Category, "Todos">;
  image: string;
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
  Pizzas: "Pizzas",
  "Hambúrgueres": "Hambúrgueres",
  Espetinhos: "Espetinhos",
  Petiscos: "Petiscos",
  Sobremesas: "Sobremesas",
  Bebidas: "Bebidas",
};

const img = (id: string) => `https://images.unsplash.com/${id}?w=640&q=80`;

export const dishes: Dish[] = [
  {
    id: 1,
    name: "Pizza Margherita",
    description: "Molho de tomate, mussarela de búfala, manjericão fresco e azeite extra virgem.",
    price: 42.9,
    category: "Pizzas",
    image: img("photo-1574071318508-1cdbab80d002"),
    popular: true,
  },
  {
    id: 2,
    name: "Pizza Calabresa",
    description: "Calabresa artesanal, cebola roxa e orégano.",
    price: 48.9,
    category: "Pizzas",
    image: img("photo-1513104890138-7c749659a591"),
  },
  {
    id: 3,
    name: "Burger Clássico",
    description: "Blend 180g, queijo cheddar, alface, tomate e molho da casa no pão brioche.",
    price: 32.9,
    category: "Hambúrgueres",
    image: img("photo-1568901346375-23c9450c58cd"),
    popular: true,
  },
  {
    id: 4,
    name: "Burger BBQ",
    description: "Blend 200g, bacon crocante, cebola caramelizada e barbecue defumado.",
    price: 38.9,
    category: "Hambúrgueres",
    image: img("photo-1553979459-b858f088f0b4"),
  },
  {
    id: 5,
    name: "Espeto de Carne",
    description: "Cubos de contra filé grelhados com farofa crocante.",
    price: 18.9,
    category: "Espetinhos",
    image: img("photo-1529692236671-f1f6cf9683ba"),
  },
  {
    id: 6,
    name: "Espeto de Frango",
    description: "Peito de frango temperado com ervas e limão.",
    price: 16.9,
    category: "Espetinhos",
    image: img("photo-1604908176997-125f25cc6f3d"),
  },
  {
    id: 7,
    name: "Batata Rústica",
    description: "Batatas assadas com alecrim, páprica defumada e maionese da casa.",
    price: 22.9,
    category: "Petiscos",
    image: img("photo-1573080496219-bb080dd4f877"),
  },
  {
    id: 8,
    name: "Anéis de Cebola",
    description: "Empanados crocantes servidos com molho barbecue.",
    price: 19.9,
    category: "Petiscos",
    image: img("photo-1639024471283-03518883512c"),
    popular: true,
  },
  {
    id: 9,
    name: "Brownie com Sorvete",
    description: "Brownie de chocolate belga com sorvete de creme e calda quente.",
    price: 24.9,
    category: "Sobremesas",
    image: img("photo-1606313564200-e75d5e30476c"),
  },
  {
    id: 10,
    name: "Cheesecake de Frutas Vermelhas",
    description: "Base crocante, creme suave e calda de frutas vermelhas.",
    price: 26.9,
    category: "Sobremesas",
    image: img("photo-1533134242443-e4d8540b37ca"),
  },
  {
    id: 11,
    name: "Limonada Siciliana",
    description: "Limões sicilianos, hortelã fresca e pouco açúcar.",
    price: 12.9,
    category: "Bebidas",
    image: img("photo-1621263764928-df1444c5e859"),
  },
  {
    id: 12,
    name: "Refrigerante Lata",
    description: "350ml — sabores tradicionais.",
    price: 6.9,
    category: "Bebidas",
    image: img("photo-1622483767028-3f23f4497317"),
  },
];
