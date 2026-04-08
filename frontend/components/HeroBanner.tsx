const HERO_IMAGE =
  "https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=1920&q=80";

const HeroBanner = () => {
  return (
    <section className="relative w-full h-[340px] md:h-[420px] overflow-hidden">
      <img
        src={HERO_IMAGE}
        alt="Ingredientes frescos e coloridos"
        className="absolute inset-0 w-full h-full object-cover"
        width={1920}
        height={640}
      />
      <div className="absolute inset-0 bg-gradient-to-t from-foreground/80 via-foreground/40 to-transparent" />
      <div className="relative z-10 flex flex-col items-center justify-center h-full text-center px-4">
        <h1 className="font-display text-4xl md:text-6xl font-bold text-primary-foreground drop-shadow-lg">
          🍽️ Delicacy
        </h1>
        <p className="font-body text-lg md:text-xl text-primary-foreground/90 mt-3 max-w-lg drop-shadow">
          Descubra nossos pratos preparados com carinho e ingredientes frescos
        </p>
      </div>
    </section>
  );
};

export default HeroBanner;
