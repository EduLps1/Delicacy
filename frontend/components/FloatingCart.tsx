import { useEffect, useState, useRef } from "react";
import { ShoppingCart, Plus } from "lucide-react";
import { useCart } from "@/context/CartContext";

interface FloatingCartProps {
  onViewCart: () => void;
}

const FloatingCart = ({ onViewCart }: FloatingCartProps) => {
  const { totalItems, totalPrice, lastAddedTimestamp } = useCart();
  const [shaking, setShaking] = useState(false);
  const [visible, setVisible] = useState(false);
  const prevTimestamp = useRef(0);

  useEffect(() => {
    setVisible(totalItems > 0);
  }, [totalItems]);

  useEffect(() => {
    if (lastAddedTimestamp > 0 && lastAddedTimestamp !== prevTimestamp.current) {
      prevTimestamp.current = lastAddedTimestamp;
      setShaking(true);
      const timeout = setTimeout(() => setShaking(false), 500);
      return () => clearTimeout(timeout);
    }
  }, [lastAddedTimestamp]);

  if (!visible) return null;

  return (
    <div
      className={`fixed bottom-20 right-4 z-40 bg-card border border-border rounded-2xl shadow-xl p-4 w-64 transition-all duration-300 animate-scale-in ${
        shaking ? "animate-shake" : ""
      }`}
    >
      <div className="flex items-center gap-3 mb-3">
        <div className={`text-primary transition-transform ${!shaking ? "animate-spin-slow" : ""}`}>
          <ShoppingCart size={28} />
        </div>
        <div>
          <p className="font-display font-bold text-card-foreground text-sm">
            {totalItems} {totalItems === 1 ? "item" : "itens"} no carrinho
          </p>
          <p className="font-display font-bold text-primary text-lg">R$ {totalPrice.toFixed(2).replace(".", ",")}</p>
        </div>
      </div>

      <div className="flex items-center gap-1.5 text-muted-foreground text-xs font-body mb-3">
        <Plus size={14} />
        <span>Adicione mais itens</span>
      </div>

      <button
        type="button"
        onClick={onViewCart}
        className="w-full bg-primary text-primary-foreground font-body font-semibold py-2.5 rounded-xl hover:opacity-90 transition-opacity text-sm"
      >
        Ver carrinho
      </button>
    </div>
  );
};

export default FloatingCart;
