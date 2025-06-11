import React from "react";
import { Trash2, Edit2 } from "lucide-react";

interface ServerRowProps {
  id: number;
  ip: string;
  name: string;
  location: string;
  assignedId: string;
  status: number;
  last_check: string;
  lastChecks: string;
  onDelete: (id: number) => void;
  onEdit: (id: number) => void;
}

const ServerRow: React.FC<ServerRowProps> = ({
  id,
  ip,
  name,
  assignedId,
  location,
  status,
  last_check,
  lastChecks,
  onDelete,
  onEdit,
}) => {
  return (
    <tr className="hover:bg-slate-50 border-b border-slate-200 transition-colors">
      <td className="p-4 py-5 text-sm font-semibold text-slate-800">{ip}</td>
      <td className="p-4 py-5 text-sm text-slate-500">{name}</td>
      <td className="p-4 py-5 text-sm text-slate-500">{assignedId}</td>
      <td className="p-4 py-5 text-sm text-slate-500">{location}</td>
      <td
        className={`p-4 py-5 text-sm font-semibold ${
          status === 1 ? "text-green-600" : "text-red-600"
        }`}
      >
        {status === 1 ? "Running" : "Passive"}
      </td>
      <td className="p-4 py-5 text-sm text-slate-500">
        {last_check
          ? new Date(last_check).toLocaleString()
          : "Haven't checked yet"}
      </td>
      <td className="p-4 py-5 text-sm">
        <div className="flex gap-[2px]">
          {(() => {
            let parsed: unknown;

            try {
              parsed = JSON.parse(lastChecks);
            } catch {
              parsed = [];
            }

            const checks = Array.isArray(parsed) ? parsed.slice(0, 10) : [];
            const normalized = Array.from(
              { length: 10 },
              (_, i) => checks[i] ?? null
            );

            return normalized.map((check, index) => {
              let colorClass = "bg-gray-400";

              if (check === 1) colorClass = "bg-green-500";
              else if (check === 0) colorClass = "bg-red-500";

              return (
                <div
                  key={index}
                  className={`w-2 h-4 rounded-sm ${colorClass}`}
                />
              );
            });
          })()}
        </div>
      </td>

      <td className="p-4 py-5 text-sm text-slate-600 flex gap-4">
        <button
          onClick={() => onEdit(id)}
          aria-label="Düzenle"
          className="hover:text-blue-600 transition-colors"
          type="button"
        >
          <Edit2 size={18} />
        </button>
        <button
          onClick={() => onDelete(id)}
          aria-label="Sil"
          className="hover:text-red-600 transition-colors"
          type="button"
        >
          <Trash2 size={18} />
        </button>
      </td>
    </tr>
  );
};

export default ServerRow;
