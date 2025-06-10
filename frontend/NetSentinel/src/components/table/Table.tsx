import React from "react";
import ServerRow from "./ServerRow";

interface ServerData {
  [key: string]: {
    status: string;
    timestamp: string;
  };
}

interface TableProps {
  servers: ServerData;
}

const Table: React.FC<TableProps> = ({ servers }) => {
  return (
    <div className="max-w-4xl mx-auto mt-8 p-6 bg-white shadow-lg rounded-lg">
      <h2 className="text-2xl font-semibold mb-4 text-center">Sunucu Durumları</h2>
      <table className="w-full border-collapse text-left">
        <thead>
          <tr className="bg-gray-100">
            <th className="p-4 font-medium">Sunucu</th>
            <th className="p-4 font-medium">Durum</th>
            <th className="p-4 font-medium">Güncellenme Zamanı</th>
          </tr>
        </thead>
        <tbody>
          {Object.entries(servers).map(([ip, data]) => (
            <ServerRow key={ip} ip={ip} status={data.status} timestamp={data.timestamp} />
          ))}
        </tbody>
      </table>
    </div>
  );
};

export default Table;
