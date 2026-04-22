
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import WalletPage from './pages/WalletPage';

function App() {
  return (
    <BrowserRouter>
      <div className="min-h-screen bg-gray-100 p-4">
        <Routes>
          <Route path="/wallet" element={<WalletPage />} />
          <Route path="*" element={<Navigate to="/wallet" replace />} />
        </Routes>
      </div>
    </BrowserRouter>
  );
}

export default App;
