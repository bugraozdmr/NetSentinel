import React, { useState, useEffect } from "react";
import { useParams, useNavigate } from "react-router-dom";

const API_URL = import.meta.env.VITE_API_BASE_URL;

const EditServer: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();

  const [formData, setFormData] = useState({
    ip: "",
    name: "",
    assigned_id: "",
    location: "", // ✅ eklendi
  });

  const [loading, setLoading] = useState(true);
  const [feedback, setFeedback] = useState<{
    type: "success" | "error";
    message: string;
  } | null>(null);

  useEffect(() => {
    if (!id) return;

    setLoading(true);
    fetch(`${API_URL}/server/${id}`)
      .then((res) => {
        if (!res.ok) throw new Error("Sunucu verisi alınamadı");
        return res.json();
      })
      .then((data) => {
        if (!data.server) throw new Error("Geçersiz veri yapısı");
        setFormData({
          ip: data.server.ip,
          name: data.server.name,
          assigned_id: data.server.assigned_id,
          location: data.server.location ?? "", // ✅ location
        });
        setFeedback(null);
      })
      .catch(() => {
        setFeedback({
          type: "error",
          message: "Sunucu verisi yüklenirken hata oluştu.",
        });
      })
      .finally(() => {
        setLoading(false);
      });
  }, [id]);

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
      const response = await fetch(`${API_URL}/servers/edit/${id}`, {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(formData),
      });

      if (response.ok) {
        setFeedback({
          type: "success",
          message: "Sunucu başarıyla güncellendi.",
        });
        navigate("/");
      } else {
        setFeedback({ type: "error", message: "Güncelleme başarısız oldu." });
      }
    } catch {
      setFeedback({
        type: "error",
        message: "Sunucuya bağlanırken hata oluştu.",
      });
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return (
      <div className="max-w-xl mx-auto mt-16 px-6 py-10 bg-white rounded-2xl shadow-xl border border-gray-200 text-center text-gray-600 font-semibold text-xl">
        Yükleniyor...
      </div>
    );
  }

  return (
    <div className="max-w-xl mx-auto mt-16 px-6 py-10 bg-white rounded-2xl shadow-xl border border-gray-200">
      <h2 className="text-3xl font-extrabold text-center text-gray-800 mb-8">
        🛠️ Sunucu Düzenle
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
          { name: "ip", label: "IP Adresi" },
          { name: "name", label: "Sunucu Adı" },
          { name: "assigned_id", label: "Atanmış ID" },
          { name: "location", label: "Konum" }, // ✅ yeni alan
        ].map((field) => (
          <div key={field.name}>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              {field.label}
            </label>
            <input
              type="text"
              name={field.name}
              value={formData[field.name as keyof typeof formData]}
              onChange={handleChange}
              className="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
              required
            />
          </div>
        ))}

        <button
          type="submit"
          disabled={loading}
          className={`w-full py-3 px-6 text-white text-lg font-semibold rounded-xl shadow-lg ${
            loading
              ? "bg-blue-400 cursor-not-allowed"
              : "bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600"
          }`}
        >
          {loading ? "Güncelleniyor..." : "Güncelle"}
        </button>
      </form>
    </div>
  );
};

export default EditServer;
