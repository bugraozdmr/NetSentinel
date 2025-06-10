interface LogoProps {
  text: string;
}

export default function Logo({ text }: LogoProps) {
  return (
    <a
      href="/"
      className="inline-flex items-center justify-center px-4 py-2 
                 text-xl sm:text-2xl md:text-3xl lg:text-4xl font-extrabold tracking-tight 
                 bg-black/80 text-white rounded-xl shadow-lg backdrop-blur-sm border border-white/10 
                 transition duration-300 hover:shadow-2xl hover:scale-105 hover:bg-black"
    >
      <span className="bg-gradient-to-r from-white via-gray-300 to-white bg-clip-text text-transparent">
        {text}
      </span>
    </a>
  );
}
