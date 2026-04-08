import { categories, type Category } from "@/data/menuData";

interface CategoryFilterProps {
  active: Category;
  onSelect: (cat: Category) => void;
}

const CategoryFilter = ({ active, onSelect }: CategoryFilterProps) => {
  const emojis: Record<Category, string> = {
    Todos: "🍴",
    Pizzas: "🍕",
    "Hambúrgueres": "🍔",
    Espetinhos: "🍢",
    Petiscos: "🍟",
    Sobremesas: "🍫",
    Bebidas: "🥤",
  };

  return (
    <div className="flex gap-2 overflow-x-auto pb-2 px-4 md:px-0 md:justify-center scrollbar-hide">
      {categories.map((cat) => (
        <button
          type="button"
          key={cat}
          onClick={() => onSelect(cat)}
          className={`flex items-center gap-2 px-5 py-2.5 rounded-full font-body font-semibold text-sm whitespace-nowrap transition-all duration-200 ${
            active === cat
              ? "bg-primary text-primary-foreground shadow-lg scale-105"
              : "bg-card text-foreground border border-border hover:bg-secondary hover:scale-[1.02]"
          }`}
        >
          <span className="text-lg">{emojis[cat]}</span>
          {cat}
        </button>
      ))}
    </div>
  );
};

export default CategoryFilter;
