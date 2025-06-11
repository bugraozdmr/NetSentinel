import React, { useState, useEffect, useMemo } from "react";
import ServerRow from "./ServerRow";
import type { Server } from "../../interfaces/server";
import { useNavigate } from "react-router-dom";

const API_URL = import.meta.env.VITE_API_BASE_URL;

interface TableProps {
  servers: Server[];
}

const Table: React.FC<TableProps> = ({ servers }) => {
  const [searchTerm, setSearchTerm] = useState("");
  const [rows, setRows] = useState(servers);

  const [modalOpen, setModalOpen] = useState(false);
  const [selectedId, setSelectedId] = useState<number | null>(null);
  const [loading, setLoading] = useState(false);

  const navigate = useNavigate();

  useEffect(() => {
    setRows(servers);
  }, [servers]);

  const filteredServers = useMemo(() => {
    const lowerSearch = searchTerm.toLowerCase();
    return rows.filter(
      (server) =>
        server.ip.toLowerCase().includes(lowerSearch) ||
        server.name.toLowerCase().includes(lowerSearch) ||
        server.assigned_id.toLowerCase().includes(lowerSearch)
    );
  }, [searchTerm, rows]);

  const handleDeleteClick = (id: number) => {
    setSelectedId(id);
    setModalOpen(true);
  };

  const confirmDelete = async () => {
    if (selectedId === null) return;
    setLoading(true);

    try {
      const response = await fetch(`${API_URL}/servers/delete/${selectedId}`, {
        method: "DELETE",
      });

      if (!response.ok) {
        throw new Error(
          `Sunucu silinirken hata oluştu: ${response.statusText}`
        );
      }

      // Başarılı silme ise state güncelle
      setRows((prev) => prev.filter((row) => row.id !== selectedId));
      setModalOpen(false);
      setSelectedId(null);
    } catch (error: unknown) {
      if (error instanceof Error) {
        alert(error.message);
      }
    } finally {
      setLoading(false);
    }
  };

  const handleEdit = (id: number) => {
    navigate(`/servers/edit/${id}`);
  };

  return (
    <div className="max-w-4xl mx-auto mt-8 p-6 bg-white shadow-lg rounded-lg">
      {/* Arama & Başlık */}
      <div className="flex flex-col sm:flex-row justify-between items-center mb-4">
        <div>
          <h2 className="text-2xl font-semibold text-center sm:text-left mb-1">
            Sunucu Durumları
          </h2>
          <p className="text-slate-500 text-sm">
            Sunucu bilgilerini filtreleyin.
          </p>
        </div>

        <div className="mt-3 sm:mt-0 w-full sm:w-auto max-w-xs relative">
          <input
            type="text"
            className="w-full pl-3 pr-10 h-10 text-sm border border-slate-300 rounded-md
              placeholder:text-slate-400 text-slate-700
              focus:outline-none focus:ring-1 focus:ring-slate-400 focus:border-slate-400
              transition-shadow shadow-sm"
            placeholder="Ara"
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
          />
          <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 50 50"
            className="w-5 h-5 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"
          >
            <path d="M 21 3 C 11.601563 3 4 10.601563 4 20 C 4 29.398438 11.601563 37 21 37 C 24.355469 37 27.460938 36.015625 30.09375 34.34375 L 42.375 46.625 L 46.625 42.375 L 34.5 30.28125 C 36.679688 27.421875 38 23.878906 38 20 C 38 10.601563 30.398438 3 21 3 Z M 21 7 C 28.199219 7 34 12.800781 34 20 C 34 27.199219 28.199219 33 21 33 C 13.800781 33 8 27.199219 8 20 C 8 12.800781 13.800781 7 21 7 Z"></path>
          </svg>
        </div>
      </div>

      {/* Tablo */}
      <div className="overflow-x-auto">
        <table className="w-full text-left border-collapse min-w-[600px]">
          <thead className="bg-slate-100">
            <tr>
              <th className="p-4 text-left text-sm font-semibold text-slate-600">
                IP
              </th>
              <th className="p-4 text-left text-sm font-semibold text-slate-600">
                Name
              </th>
              <th className="p-4 text-left text-sm font-semibold text-slate-600">
                Assigned ID
              </th>
              <th className="p-4 text-left text-sm font-semibold text-slate-600">
                Location
              </th>
              <th className="p-4 text-left text-sm font-semibold text-slate-600">
                Status
              </th>
              <th className="p-4 text-left text-sm font-semibold text-slate-600">
                Last Check
              </th>
              <th className="p-4 text-left text-sm font-semibold text-slate-600">
                Last Checks
              </th>
              <th className="p-4 text-left text-sm font-semibold text-slate-600">
                İşlemler
              </th>
            </tr>
          </thead>

          <tbody>
            {filteredServers.length > 0 ? (
              filteredServers.map((server) => (
                <ServerRow
                  key={server.id}
                  id={server.id}
                  ip={server.ip}
                  name={server.name}
                  assignedId={server.assigned_id}
                  location={server.location}
                  status={server.is_active}
                  last_check={server.last_check_at}
                  lastChecks={server.last_checks}
                  onDelete={handleDeleteClick}
                  onEdit={handleEdit}
                />
              ))
            ) : (
              <tr>
                <td colSpan={6} className="text-center py-6 text-slate-400">
                  Arama kriterlerine uygun sunucu bulunamadı.
                </td>
              </tr>
            )}
          </tbody>
        </table>
      </div>

      {/* Modal */}
      {modalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
          <div className="bg-white rounded-lg shadow-lg p-6 max-w-sm w-full">
            <h3 className="text-lg font-semibold mb-4">
              Sunucuyu silmek istediğinize emin misiniz?
            </h3>
            <div className="flex justify-end gap-4">
              <button
                onClick={() => setModalOpen(false)}
                disabled={loading}
                className="px-4 py-2 border rounded-md hover:bg-gray-100 transition disabled:opacity-50"
              >
                Vazgeç
              </button>
              <button
                onClick={confirmDelete}
                disabled={loading}
                className="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition disabled:opacity-50"
              >
                {loading ? "Siliniyor..." : "Sil"}
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default Table;
