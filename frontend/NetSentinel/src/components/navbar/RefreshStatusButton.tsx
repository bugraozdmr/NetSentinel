import { useState } from "react";

const API_URL = import.meta.env.VITE_API_BASE_URL;

function RefreshStatusButton() {
  const [loading, setLoading] = useState(false);

  const handleClick = async () => {
    setLoading(true);
    try {
      const response = await fetch(`${API_URL}/check`, {
        method: "GET",
      });
      if (response.ok) {
        window.location.reload();
      } else {
        console.error("İstek başarısız:", response.status);
      }
    } catch (error) {
      console.error("İstek hatası:", error);
    } finally {
      setLoading(false);
    }
  };

  return (
    <button
      type="button"
      onClick={handleClick}
      className="p-2 hover:bg-gray-200 rounded-full transition relative"
      disabled={loading}
    >
      {loading ? (
        <svg
          className="animate-spin h-5 w-5 text-gray-700"
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 24 24"
        >
          <circle
            className="opacity-25"
            cx="12"
            cy="12"
            r="10"
            stroke="currentColor"
            strokeWidth="4"
          ></circle>
          <path
            className="opacity-75"
            fill="currentColor"
            d="M4 12a8 8 0 018-8v8z"
          ></path>
        </svg>
      ) : (
        <svg
          xmlns="http://www.w3.org/2000/svg"
          width="22"
          height="22"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          strokeWidth="2"
          strokeLinecap="round"
          strokeLinejoin="round"
          className="text-gray-700"
        >
          <path stroke="none" d="M0 0h24v24H0z" fill="none" />
          <path d="M20 11a8.1 8.1 0 0 0 -15.5 -2M4 5v4h4" />
          <path d="M4 13a8.1 8.1 0 0 0 15.5 2M20 19v-4h-4" />
        </svg>
      )}
    </button>
  );
}

export default RefreshStatusButton;
