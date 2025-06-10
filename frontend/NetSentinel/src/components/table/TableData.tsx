import React from "react";
import Table from "./Table";
import useFetch from "./useFetch";

const API_URL = import.meta.env.VITE_API_BASE_URL;

const TableData: React.FC = () => {
  const { data, loading } = useFetch(API_URL);

  return (
    <div className="p-6">
      {loading ? (
        <p className="text-lg font-semibold text-center">Yükleniyor...</p>
      ) : (
        <Table servers={data || {}} />
      )}
    </div>
  );
};

export default TableData;
