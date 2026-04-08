import type { Dish } from "@/data/menuData";
import { Badge } from "@/components/ui/badge";
import { useCart } from "@/context/CartContext";
import { ShoppingCart } from "lucide-react";

interface DishCardProps {
  dish: Dish;
}

const DishCard = ({ dish }: DishCardProps) => {
  const { addToCart } = useCart();

  return (
    <div className="group bg-card rounded-2xl overflow-hidden border border-border shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
      <div className="relative overflow-hidden h-48">
        <img
          src={dish.image}
          alt={dish.name}
          loading="lazy"
          width={640}
          height={640}
          className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
        />
        {dish.popular && (
          <Badge className="absolute top-3 right-3 bg-secondary text-secondary-foreground font-display text-xs px-3 py-1">
            🔥 Popular
          </Badge>
        )}
      </div>
      <div className="p-5">
        <div className="flex items-start justify-between gap-2">
          <h3 className="font-display font-bold text-lg text-card-foreground leading-tight">{dish.name}</h3>
          <span className="font-display font-bold text-lg text-primary whitespace-nowrap">
            R$ {dish.price.toFixed(2).replace(".", ",")}
          </span>
        </div>
        <p className="font-body text-sm text-muted-foreground mt-2 line-clamp-2">{dish.description}</p>
        <button
          type="button"
          onClick={() => addToCart(dish)}
          className="mt-4 w-full flex items-center justify-center gap-2 bg-primary text-primary-foreground font-body font-semibold py-2.5 rounded-xl hover:opacity-90 transition-opacity"
        >
          <ShoppingCart size={16} />
          Adicionar ao Carrinho
        </button>
      </div>
    </div>
  );
};

export default DishCard;
