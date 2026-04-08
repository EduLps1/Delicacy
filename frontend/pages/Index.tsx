import { useState, useRef } from "react";
import Header from "@/components/Header";
import HeroBanner from "@/components/HeroBanner";
import CategoryFilter from "@/components/CategoryFilter";
import DishCard from "@/components/DishCard";
import BottomNav from "@/components/BottomNav";
import FloatingCart from "@/components/FloatingCart";
import { useCart } from "@/context/CartContext";
import { dishes, categories, categoryLabels, type Category } from "@/data/menuData";
import { ArrowUp, MessageCircle, UtensilsCrossed, Trash2, Minus, Plus } from "lucide-react";

const Index = () => {
  const [activeCategory, setActiveCategory] = useState<Category>("Todos");
  const [activeTab, setActiveTab] = useState<"cardapio" | "pedidos" | "carrinho">("cardapio");
  const topRef = useRef<HTMLDivElement>(null);

  const scrollToTop = () => {
    topRef.current?.scrollIntoView({ behavior: "smooth" });
  };

  const displayCategories = categories.filter((c) => c !== "Todos") as Exclude<Category, "Todos">[];

  return (
    <div className="min-h-screen bg-background font-body pb-20" ref={topRef}>
      <Header />
      <HeroBanner />

      {activeTab === "cardapio" && (
        <main className="max-w-6xl mx-auto py-8 px-4">
          <CategoryFilter active={activeCategory} onSelect={setActiveCategory} />

          {activeCategory === "Todos" ? (
            displayCategories.map((cat) => {
              const catDishes = dishes.filter((d) => d.category === cat);
              if (catDishes.length === 0) return null;
              return (
                <section key={cat} className="mt-10">
                  <h2 className="font-display text-2xl font-bold text-foreground mb-5">{categoryLabels[cat]}</h2>
                  <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    {catDishes.map((dish) => (
                      <DishCard key={dish.id} dish={dish} />
                    ))}
                  </div>
                </section>
              );
            })
          ) : (
            <section className="mt-10">
              <h2 className="font-display text-2xl font-bold text-foreground mb-5">
                {categoryLabels[activeCategory as Exclude<Category, "Todos">]}
              </h2>
              <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                {dishes
                  .filter((d) => d.category === activeCategory)
                  .map((dish) => (
                    <DishCard key={dish.id} dish={dish} />
                  ))}
              </div>
              {dishes.filter((d) => d.category === activeCategory).length === 0 && (
                <p className="text-center text-muted-foreground font-body mt-12 text-lg">
                  Nenhum item encontrado nessa categoria 😕
                </p>
              )}
            </section>
          )}
        </main>
      )}

      {activeTab === "pedidos" && (
        <main className="max-w-6xl mx-auto py-16 px-4 text-center">
          <ClipboardPlaceholder />
        </main>
      )}

      {activeTab === "carrinho" && (
        <main className="max-w-6xl mx-auto py-16 px-4 text-center">
          <CartView />
        </main>
      )}

      <footer className="max-w-6xl mx-auto px-4 py-10 mb-4 border-t border-border">
        <div className="flex flex-col items-center gap-4">
          <div className="flex items-center font-display text-lg font-bold text-foreground">
            <span>Desenvolvido por de</span>
            <UtensilsCrossed className="text-primary mx-0.5" size={16} strokeWidth={2.5} />
            <span>icacy</span>
          </div>

          <a
            href="https://wa.me/5500000000000"
            target="_blank"
            rel="noopener noreferrer"
            className="flex items-center gap-2 text-primary hover:underline font-body font-semibold"
          >
            <MessageCircle size={18} />
            Fale conosco
          </a>

          <button
            type="button"
            onClick={scrollToTop}
            className="flex items-center gap-2 px-5 py-2.5 rounded-full bg-primary text-primary-foreground font-body font-semibold text-sm hover:opacity-90 transition-opacity"
          >
            <ArrowUp size={16} />
            Voltar ao topo
          </button>
        </div>
      </footer>

      <FloatingCart onViewCart={() => setActiveTab("carrinho")} />
      <BottomNav active={activeTab} onSelect={setActiveTab} />
    </div>
  );
};

const ClipboardPlaceholder = () => (
  <div className="flex flex-col items-center gap-3 text-muted-foreground">
    <span className="text-5xl">📋</span>
    <h2 className="font-display text-xl font-bold text-foreground">Meus Pedidos</h2>
    <p className="font-body">Você ainda não fez nenhum pedido.</p>
  </div>
);

const CartView = () => {
  const { items, totalItems, totalPrice, updateQuantity, removeFromCart } = useCart();
  const [confirmRemove, setConfirmRemove] = useState<number | null>(null);

  const handleDecrease = (dishId: number, currentQty: number) => {
    if (currentQty <= 1) {
      setConfirmRemove(dishId);
    } else {
      updateQuantity(dishId, currentQty - 1);
    }
  };

  if (totalItems === 0) {
    return (
      <div className="flex flex-col items-center gap-3 text-muted-foreground">
        <span className="text-5xl">🛒</span>
        <h2 className="font-display text-xl font-bold text-foreground">Carrinho</h2>
        <p className="font-body">Seu carrinho está vazio.</p>
      </div>
    );
  }

  return (
    <div className="max-w-lg mx-auto relative text-left">
      <h2 className="font-display text-2xl font-bold text-foreground mb-6 text-center">🛒 Seu Carrinho</h2>
      <div className="flex flex-col gap-3">
        {items.map((item) => (
          <div
            key={item.dish.id}
            className="relative flex items-center gap-4 bg-card rounded-xl border border-border p-4"
          >
            <button
              type="button"
              onClick={() => setConfirmRemove(item.dish.id)}
              className="absolute top-2 right-2 text-muted-foreground hover:text-destructive transition-colors"
              aria-label="Remover item"
            >
              <Trash2 size={16} />
            </button>

            <img src={item.dish.image} alt={item.dish.name} className="w-16 h-16 rounded-lg object-cover" />
            <div className="flex-1 min-w-0">
              <p className="font-display font-bold text-card-foreground text-sm">{item.dish.name}</p>
              <div className="flex items-center gap-2 mt-1.5">
                <button
                  type="button"
                  onClick={() => handleDecrease(item.dish.id, item.quantity)}
                  className="w-7 h-7 flex items-center justify-center rounded-lg bg-muted text-foreground font-bold text-sm hover:bg-primary hover:text-primary-foreground transition-colors"
                >
                  <Minus size={14} />
                </button>
                <span className="font-display font-bold text-foreground w-6 text-center">{item.quantity}</span>
                <button
                  type="button"
                  onClick={() => updateQuantity(item.dish.id, item.quantity + 1)}
                  disabled={item.quantity >= 10}
                  className="w-7 h-7 flex items-center justify-center rounded-lg bg-muted text-foreground font-bold text-sm hover:bg-primary hover:text-primary-foreground transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                >
                  <Plus size={14} />
                </button>
              </div>
            </div>
            <span className="font-display font-bold text-primary text-sm whitespace-nowrap">
              R$ {(item.dish.price * item.quantity).toFixed(2).replace(".", ",")}
            </span>
          </div>
        ))}
      </div>
      <div className="mt-6 p-4 bg-card rounded-xl border border-border flex items-center justify-between">
        <span className="font-display font-bold text-foreground text-lg">Total</span>
        <span className="font-display font-bold text-primary text-xl">R$ {totalPrice.toFixed(2).replace(".", ",")}</span>
      </div>

      {confirmRemove !== null && (
        <div className="fixed inset-0 z-[60] flex items-center justify-center bg-foreground/40 backdrop-blur-sm">
          <div className="bg-card rounded-2xl border border-border shadow-2xl p-6 w-80 animate-scale-in">
            <div className="text-center">
              <span className="text-4xl">🗑️</span>
              <h3 className="font-display font-bold text-foreground text-lg mt-3">Remover item?</h3>
              <p className="font-body text-muted-foreground text-sm mt-1">
                Deseja remover <strong>{items.find((i) => i.dish.id === confirmRemove)?.dish.name}</strong> do carrinho?
              </p>
            </div>
            <div className="flex gap-3 mt-5">
              <button
                type="button"
                onClick={() => setConfirmRemove(null)}
                className="flex-1 py-2.5 rounded-xl border border-border font-body font-semibold text-sm text-foreground hover:bg-muted transition-colors"
              >
                Cancelar
              </button>
              <button
                type="button"
                onClick={() => {
                  removeFromCart(confirmRemove);
                  setConfirmRemove(null);
                }}
                className="flex-1 py-2.5 rounded-xl bg-destructive text-destructive-foreground font-body font-semibold text-sm hover:opacity-90 transition-opacity"
              >
                Remover
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default Index;
