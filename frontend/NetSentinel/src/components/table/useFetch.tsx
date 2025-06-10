import { useState, useEffect } from "react";

const useFetch = (url: string) => {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetch(url)
      .then((res) => res.json())
      .then((result) => {
        setData(result.servers);
        setLoading(false);
      })
      .catch((error) => console.error("API call failed:", error));
  }, [url]);

  return { data, loading };
};

export default useFetch;
