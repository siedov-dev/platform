import { useState, useEffect } from 'react';
import { walletApi } from '../api/mock';
import { PaymentSystem, Balance } from '../types/wallet';
import PaymentSystemSelect from './PaymentSystemSelect';
import { CheckCircle2 } from 'lucide-react';

interface Props {
  balances: Balance[];
}

export default function PayoutForm({ balances }: Props) {
  const [systems, setSystems] = useState<PaymentSystem[]>([]);
  const [selectedSysId, setSelectedSysId] = useState<string | null>(null);
  const [amount, setAmount] = useState<string>('');
  const [currency, setCurrency] = useState<string>('USD');
  const [accountDetails, setAccountDetails] = useState<string>('');
  const [loading, setLoading] = useState(false);
  const [result, setResult] = useState<{ transactionId?: string } | null>(null);

  useEffect(() => {
    walletApi.getPaymentSystems().then(setSystems);
  }, []);

  const selectedSys = systems.find(s => s.id === selectedSysId);
  const availableBalance = balances.find(b => b.currency === currency)?.amount || 0;

  useEffect(() => {
    if (selectedSys && !selectedSys.currencies.includes(currency)) {
      setCurrency(selectedSys.currencies[0]);
    }
  }, [selectedSys, currency]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedSysId || !amount || isNaN(Number(amount)) || !accountDetails) return;

    if (Number(amount) > availableBalance) {
      alert("Insufficient balance");
      return;
    }

    setLoading(true);
    try {
      const res = await walletApi.payout({
        paymentSystemId: selectedSysId,
        amount: Number(amount),
        currency,
        accountDetails
      });
      setResult(res);
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  if (result) {
    return (
      <div className="text-center py-8">
        <CheckCircle2 className="w-16 h-16 text-green-500 mx-auto mb-4" />
        <h2 className="text-2xl font-bold text-gray-800 mb-2">Payout Requested</h2>
        <p className="text-gray-600 mb-2">Your withdrawal request is being processed.</p>
        <p className="text-sm text-gray-500 mb-6">Transaction ID: {result.transactionId}</p>

        <button
          onClick={() => { setResult(null); setAmount(''); setAccountDetails(''); }}
          className="text-blue-600 hover:underline"
        >
          Make another payout
        </button>
      </div>
    );
  }

  return (
    <form onSubmit={handleSubmit} className="space-y-6">
      <div>
        <h3 className="text-lg font-medium mb-3">1. Select Withdrawal Method</h3>
        <PaymentSystemSelect
          systems={systems}
          selectedId={selectedSysId}
          onSelect={setSelectedSysId}
        />
      </div>

      {selectedSys && (
        <div className="bg-gray-50 p-6 rounded-xl border">
          <h3 className="text-lg font-medium mb-4">2. Withdrawal Details</h3>

          <div className="flex gap-4 mb-4">
            <div className="flex-1">
              <label className="block text-sm font-medium text-gray-700 mb-1">Amount</label>
              <div className="relative">
                <input
                  type="number"
                  value={amount}
                  onChange={e => setAmount(e.target.value)}
                  min={selectedSys.minAmount || 0}
                  max={availableBalance}
                  step="any"
                  className="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                  placeholder="0.00"
                  required
                />
                <button
                  type="button"
                  onClick={() => setAmount(availableBalance.toString())}
                  className="absolute right-2 top-1/2 -translate-y-1/2 text-xs bg-gray-200 hover:bg-gray-300 px-2 py-1 rounded text-gray-700"
                >
                  MAX
                </button>
              </div>
            </div>
            <div className="w-1/3">
              <label className="block text-sm font-medium text-gray-700 mb-1">Currency</label>
              <select
                value={currency}
                onChange={e => setCurrency(e.target.value)}
                className="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white"
              >
                {selectedSys.currencies.map(c => (
                  <option key={c} value={c}>{c}</option>
                ))}
              </select>
            </div>
          </div>

          <div className="text-sm flex justify-between mb-4">
            <span className="text-gray-500">Available balance:</span>
            <span className={Number(amount) > availableBalance ? 'text-red-500 font-medium' : 'font-medium'}>
              {availableBalance.toFixed(2)} {currency}
            </span>
          </div>

          <div className="mb-6">
            <label className="block text-sm font-medium text-gray-700 mb-1">
              {selectedSys.type === 'crypto' ? 'Wallet Address' : 'Account Details / Card Number'}
            </label>
            <input
              type="text"
              value={accountDetails}
              onChange={e => setAccountDetails(e.target.value)}
              className="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
              placeholder={selectedSys.type === 'crypto' ? 'Enter crypto address' : 'Enter account details'}
              required
            />
          </div>

          <button
            type="submit"
            disabled={!amount || !accountDetails || Number(amount) > availableBalance || loading}
            className="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
          >
            {loading ? (
              <div className="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin" />
            ) : null}
            {loading ? 'Processing...' : 'Request Payout'}
          </button>
        </div>
      )}
    </form>
  );
}
