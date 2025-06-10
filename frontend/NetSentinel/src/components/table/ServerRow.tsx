import React from "react";

interface ServerRowProps {
  ip: string;
  status: string;
  timestamp: string;
}

const ServerRow: React.FC<ServerRowProps> = ({ ip, status, timestamp }) => {
  return (
    <tr className="border-b hover:bg-gray-50 transition-all">
      <td className="p-4">{ip}</td>
      <td className={`p-4 font-semibold ${status === "Active" ? "text-green-500" : "text-red-500"}`}>
        {status}
      </td>
      <td className="p-4 text-gray-500">{timestamp}</td>
    </tr>
  );
};

export default ServerRow;
