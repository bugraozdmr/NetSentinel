import Logo from "./Logo";
import { Link } from "react-router-dom";
import RefreshStatusButton from "./RefreshStatusButton";

export default function Navbar() {
  return (
    <nav className="bg-white w-full flex items-center justify-between px-6 py-4 shadow-sm h-20">
      <Logo text="NetSentinel" />

      <div className="flex items-center space-x-4">
        <Link
          to="/add-server"
          className="py-2 px-4 bg-gray-100 hover:bg-gray-200 text-sm font-medium rounded-full transition"
        >
          Add Server
        </Link>

        <RefreshStatusButton />
      </div>
    </nav>
  );
}
