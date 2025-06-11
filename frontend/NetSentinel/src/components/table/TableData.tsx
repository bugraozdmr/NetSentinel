import React from "react";
import Table from "./Table";
import useFetch from "./useFetch";
import type { Server } from "../../interfaces/server";

interface ServerResponse {
  servers: Server[];
}

const API_URL = import.meta.env.VITE_API_BASE_URL;

const TableData: React.FC = () => {
  const { data, loading, error } = useFetch<ServerResponse>(`${API_URL}/servers`);

  if (loading) {
    return <p className="text-lg font-semibold text-center">Yükleniyor...</p>;
  }

  if (error) {
    return <p className="text-lg font-semibold text-center text-red-600">Hata: {error.message}</p>;
  }

  return (
    <div className="p-6">
      <Table servers={data?.servers ?? []} />
    </div>
  );
};

export default TableData;
