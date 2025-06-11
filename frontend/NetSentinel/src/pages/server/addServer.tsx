import React, { useState } from "react";

const API_URL = import.meta.env.VITE_API_BASE_URL;

const AddServer: React.FC = () => {
  const [formData, setFormData] = useState({
    ip: "",
    name: "",
    assigned_id: "",
    location: "", // ✅ yeni alan
  });

  const [loading, setLoading] = useState(false);
  const [feedback, setFeedback] = useState<{
    type: "success" | "error";
    message: string;
  } | null>(null);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value,
    });
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setFeedback(null);

    try {
      const response = await fetch(`${API_URL}/servers`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(formData),
      });

      const data = await response.json();

      if (!response.ok) {
        if (data.errors) {
          const firstErrorKey = Object.keys(data.errors)[0];
          setFeedback({ type: "error", message: data.errors[firstErrorKey] });
        } else if (data.error) {
          setFeedback({
            type: "error",
            message: "Bir şeyler ters gitti. Lütfen tekrar deneyin.",
          });
        } else {
          setFeedback({ type: "error", message: "Sunucu hatası." });
        }
      } else {
        setFeedback({ type: "success", message: "Sunucu başarıyla eklendi." });
        setFormData({
          ip: "",
          name: "",
          assigned_id: "",
          location: "", // ✅ temizle
        });
      }
    } catch {
      setFeedback({
        type: "error",
        message: "Bir şeyler ters gitti. Lütfen tekrar deneyin.",
      });
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="max-w-xl mx-auto mt-16 px-6 sm:px-8 py-10 bg-white rounded-2xl shadow-xl border border-gray-200 transition-all">
      <h2 className="text-3xl font-extrabold text-center text-gray-800 mb-8">
        🚀 Sunucu Ekle
      </h2>

      {feedback && (
        <div
          className={`mb-6 px-4 py-3 rounded-md text-sm font-medium ${
            feedback.type === "success"
              ? "bg-green-100 text-green-800 border border-green-300"
              : "bg-red-100 text-red-800 border border-red-300"
          }`}
        >
          {feedback.message}
        </div>
      )}

      <form onSubmit={handleSubmit} className="space-y-6">
        {[
          { label: "IP Adresi", name: "ip", placeholder: "192.168.1.1" },
          { label: "Sunucu Adı", name: "name", placeholder: "Ubuntu VM" },
          { label: "Atanmış ID", name: "assigned_id", placeholder: "ubuntuvm" },
          { label: "Konum", name: "location", placeholder: "İstanbul, TR" }, // ✅ eklendi
        ].map((field) => (
          <div key={field.name}>
            <label
              htmlFor={field.name}
              className="block text-sm font-medium text-gray-700 mb-1"
            >
              {field.label}
            </label>
            <input
              type="text"
              name={field.name}
              value={formData[field.name as keyof typeof formData]}
              onChange={handleChange}
              placeholder={field.placeholder}
              className="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none transition duration-200 placeholder-gray-400"
              required
            />
          </div>
        ))}

        <button
          type="submit"
          disabled={loading}
          className={`w-full py-3 px-6 text-white text-lg font-semibold rounded-xl shadow-lg transition-all duration-300 ${
            loading
              ? "bg-blue-400 cursor-not-allowed"
              : "bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600"
          }`}
        >
          {loading ? "Gönderiliyor..." : "+ Sunucu Ekle"}
        </button>
      </form>
    </div>
  );
};

export default AddServer;
