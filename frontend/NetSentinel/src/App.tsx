import "./App.css";
import { BrowserRouter as Router, Routes, Route } from "react-router-dom";
import Layout from "./components/layout/Layout";
import TableData from "./components/table/TableData";
import AddServer from "./pages/server/addServer";
import EditServer from "./pages/server/editServer";

function App() {
  return (
    <Router>
      <Layout>
        <Routes>
          <Route path="/" element={<TableData />} />
          <Route path="/add-server" element={<AddServer />} />
          <Route path="/servers/edit/:id" element={<EditServer />} />
        </Routes>
      </Layout>
    </Router>
  );
}

export default App;
