import type { ReactNode } from "react";
import { BookOpen, ClipboardList, ShoppingCart } from "lucide-react";

type Tab = "cardapio" | "pedidos" | "carrinho";

interface BottomNavProps {
  active: Tab;
  onSelect: (tab: Tab) => void;
}

const BottomNav = ({ active, onSelect }: BottomNavProps) => {
  const tabs: { id: Tab; label: string; icon: ReactNode }[] = [
    { id: "cardapio", label: "Cardápio", icon: <BookOpen size={22} /> },
    { id: "pedidos", label: "Meus Pedidos", icon: <ClipboardList size={22} /> },
    { id: "carrinho", label: "Carrinho", icon: <ShoppingCart size={22} /> },
  ];

  return (
    <nav className="fixed bottom-0 left-0 right-0 z-50 bg-card border-t border-border shadow-[0_-2px_10px_rgba(0,0,0,0.08)]">
      <div className="max-w-6xl mx-auto flex items-center justify-around h-16">
        {tabs.map((tab) => (
          <button
            type="button"
            key={tab.id}
            onClick={() => onSelect(tab.id)}
            className={`flex flex-col items-center gap-1 px-4 py-1.5 rounded-lg transition-colors ${
              active === tab.id ? "text-primary" : "text-muted-foreground hover:text-foreground"
            }`}
          >
            {tab.icon}
            <span className="text-xs font-body font-semibold">{tab.label}</span>
          </button>
        ))}
      </div>
    </nav>
  );
};

export default BottomNav;
