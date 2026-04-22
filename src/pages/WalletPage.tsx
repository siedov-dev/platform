import { useState, useEffect } from 'react';
import { walletApi } from '../api/mock';
import { Balance } from '../types/wallet';
import DepositForm from '../components/DepositForm';
import PayoutForm from '../components/PayoutForm';

export default function WalletPage() {
  const [balances, setBalances] = useState<Balance[]>([]);
  const [loading, setLoading] = useState(true);
  const [activeTab, setActiveTab] = useState<'deposit' | 'payout'>('deposit');

  useEffect(() => {
    const fetchBalance = async () => {
      try {
        const data = await walletApi.getBalance();
        setBalances(data);
      } catch (err) {
        console.error('Failed to fetch balance', err);
      } finally {
        setLoading(false);
      }
    };

    fetchBalance();
  }, []);

  return (
    <div className="max-w-4xl mx-auto bg-white rounded-lg shadow-md overflow-hidden">
      <div className="bg-blue-600 p-6 text-white">
        <h1 className="text-2xl font-bold mb-4">Cashier / Wallet</h1>

        {loading ? (
          <div>Loading balances...</div>
        ) : (
          <div className="flex gap-4 overflow-x-auto">
            {balances.map(b => (
              <div key={b.currency} className="bg-white/20 px-4 py-2 rounded-lg">
                <span className="text-sm opacity-80">{b.currency}</span>
                <div className="text-xl font-bold">{b.amount.toFixed(2)}</div>
              </div>
            ))}
          </div>
        )}
      </div>

      <div className="border-b">
        <div className="flex">
          <button
            className={`flex-1 py-4 text-center font-medium ${activeTab === 'deposit' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-500 hover:text-gray-700'}`}
            onClick={() => setActiveTab('deposit')}
          >
            Deposit
          </button>
          <button
            className={`flex-1 py-4 text-center font-medium ${activeTab === 'payout' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-500 hover:text-gray-700'}`}
            onClick={() => setActiveTab('payout')}
          >
            Payout
          </button>
        </div>
      </div>

      <div className="p-6">
        {activeTab === 'deposit' ? <DepositForm /> : <PayoutForm balances={balances} />}
      </div>
    </div>
  );
}
