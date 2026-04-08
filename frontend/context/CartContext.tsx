import { createContext, useContext, useState, useCallback, type ReactNode } from "react";
import type { Dish } from "@/data/menuData";

export interface CartItem {
  dish: Dish;
  quantity: number;
}

interface CartContextType {
  items: CartItem[];
  addToCart: (dish: Dish) => void;
  updateQuantity: (dishId: number, quantity: number) => void;
  removeFromCart: (dishId: number) => void;
  totalItems: number;
  totalPrice: number;
  lastAddedTimestamp: number;
}

const CartContext = createContext<CartContextType | undefined>(undefined);

export const CartProvider = ({ children }: { children: ReactNode }) => {
  const [items, setItems] = useState<CartItem[]>([]);
  const [lastAddedTimestamp, setLastAddedTimestamp] = useState(0);

  const addToCart = useCallback((dish: Dish) => {
    setItems((prev) => {
      const existing = prev.find((item) => item.dish.id === dish.id);
      if (existing) {
        return prev.map((item) =>
          item.dish.id === dish.id ? { ...item, quantity: item.quantity + 1 } : item,
        );
      }
      return [...prev, { dish, quantity: 1 }];
    });
    setLastAddedTimestamp(Date.now());
  }, []);

  const updateQuantity = useCallback((dishId: number, quantity: number) => {
    if (quantity <= 0) return;
    setItems((prev) =>
      prev.map((item) =>
        item.dish.id === dishId ? { ...item, quantity: Math.min(quantity, 10) } : item,
      ),
    );
  }, []);

  const removeFromCart = useCallback((dishId: number) => {
    setItems((prev) => prev.filter((item) => item.dish.id !== dishId));
  }, []);

  const totalItems = items.reduce((sum, item) => sum + item.quantity, 0);
  const totalPrice = items.reduce((sum, item) => sum + item.dish.price * item.quantity, 0);

  return (
    <CartContext.Provider
      value={{
        items,
        addToCart,
        updateQuantity,
        removeFromCart,
        totalItems,
        totalPrice,
        lastAddedTimestamp,
      }}
    >
      {children}
    </CartContext.Provider>
  );
};

export const useCart = () => {
  const context = useContext(CartContext);
  if (!context) throw new Error("useCart must be used within CartProvider");
  return context;
};
