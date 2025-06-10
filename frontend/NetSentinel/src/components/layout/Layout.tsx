import React, { ReactNode } from "react";
import Navbar from "../navbar/Navbar";


interface LayoutProps {
  children: ReactNode;
}

const Layout: React.FC<LayoutProps> = ({ children }) => {
  return (
    <div className="flex flex-col min-h-screen bg-gray-100">
      <Navbar />
      
      <main className="flex-1 p-6">
          {/* Table burada çağrılıyor */}
        {children}
      </main>
    </div>
  );
};

export default Layout;
