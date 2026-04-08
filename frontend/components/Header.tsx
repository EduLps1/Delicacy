import { Search, Share2, Instagram, UtensilsCrossed } from "lucide-react";

const Header = () => {
  return (
    <header className="sticky top-0 z-50 w-full bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/80 border-b border-border shadow-sm">
      <div className="max-w-6xl mx-auto flex items-center justify-between px-4 h-14">
        <div className="w-28" />

        <div className="flex items-center font-display text-2xl font-bold text-foreground tracking-tight select-none">
          <span>de</span>
          <UtensilsCrossed className="text-primary rotate-0" size={20} strokeWidth={2.5} />
          <span>icacy</span>
        </div>

        <div className="flex items-center gap-3 w-28 justify-end">
          <a
            href="https://instagram.com"
            target="_blank"
            rel="noopener noreferrer"
            aria-label="Instagram"
            className="text-muted-foreground hover:text-primary transition-colors"
          >
            <Instagram size={20} />
          </a>
          <button
            type="button"
            aria-label="Buscar"
            className="text-muted-foreground hover:text-primary transition-colors"
          >
            <Search size={20} />
          </button>
          <button
            type="button"
            aria-label="Compartilhar"
            className="text-muted-foreground hover:text-primary transition-colors"
          >
            <Share2 size={20} />
          </button>
        </div>
      </div>
    </header>
  );
};

export default Header;
