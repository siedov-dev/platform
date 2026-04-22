import { useState, useEffect } from 'react';
import { walletApi } from '../api/mock';
import { PaymentSystem } from '../types/wallet';
import PaymentSystemSelect from './PaymentSystemSelect';
import { QRCodeSVG } from 'qrcode.react';
import { CheckCircle2, Copy } from 'lucide-react';

const QUICK_AMOUNTS = [10, 50, 100, 500, 1000];

export default function DepositForm() {
  const [systems, setSystems] = useState<PaymentSystem[]>([]);
  const [selectedSysId, setSelectedSysId] = useState<string | null>(null);
  const [amount, setAmount] = useState<string>('');
  const [currency, setCurrency] = useState<string>('USD');
  const [loading, setLoading] = useState(false);
  const [result, setResult] = useState<{ transactionId?: string; address?: string } | null>(null);

  useEffect(() => {
    walletApi.getPaymentSystems().then(setSystems);
  }, []);

  const selectedSys = systems.find(s => s.id === selectedSysId);

  // Reset currency if selected system doesn't support it
  useEffect(() => {
    if (selectedSys && !selectedSys.currencies.includes(currency)) {
      setCurrency(selectedSys.currencies[0]);
    }
  }, [selectedSys, currency]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedSysId || !amount || isNaN(Number(amount))) return;

    setLoading(true);
    try {
      const res = await walletApi.deposit({
        paymentSystemId: selectedSysId,
        amount: Number(amount),
        currency
      });
      setResult(res);
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const copyToClipboard = (text: string) => {
    navigator.clipboard.writeText(text);
    // Could add a toast here
  };

  if (result) {
    return (
      <div className="text-center py-8">
        <CheckCircle2 className="w-16 h-16 text-green-500 mx-auto mb-4" />
        <h2 className="text-2xl font-bold text-gray-800 mb-2">Request Created</h2>
        <p className="text-gray-600 mb-6">Transaction ID: {result.transactionId}</p>

        {result.address && (
          <div className="bg-gray-50 p-6 rounded-xl inline-block text-left border">
            <h3 className="font-semibold mb-4 text-center">Scan to Pay</h3>
            <div className="bg-white p-4 rounded-lg inline-block mb-4 border">
              <QRCodeSVG value={result.address} size={200} />
            </div>
            <div>
              <p className="text-sm text-gray-500 mb-1">Send exactly {amount} {currency} to:</p>
              <div className="flex items-center gap-2 bg-white border p-2 rounded">
                <code className="text-sm flex-1 break-all">{result.address}</code>
                <button
                  onClick={() => copyToClipboard(result.address!)}
                  className="p-2 hover:bg-gray-100 rounded text-gray-500"
                  title="Copy address"
                >
                  <Copy size={16} />
                </button>
              </div>
            </div>
          </div>
        )}

        {!result.address && (
          <p className="text-gray-600 mb-6">You will be redirected to the payment gateway...</p>
        )}

        <div className="mt-8">
          <button
            onClick={() => { setResult(null); setAmount(''); }}
            className="text-blue-600 hover:underline"
          >
            Make another deposit
          </button>
        </div>
      </div>
    );
  }

  return (
    <form onSubmit={handleSubmit} className="space-y-6">
      <div>
        <h3 className="text-lg font-medium mb-3">1. Select Payment Method</h3>
        <PaymentSystemSelect
          systems={systems}
          selectedId={selectedSysId}
          onSelect={setSelectedSysId}
        />
      </div>

      {selectedSys && (
        <div className="bg-gray-50 p-6 rounded-xl border">
          <h3 className="text-lg font-medium mb-4">2. Enter Deposit Amount</h3>

          <div className="flex gap-4 mb-4">
            <div className="flex-1">
              <label className="block text-sm font-medium text-gray-700 mb-1">Amount</label>
              <input
                type="number"
                value={amount}
                onChange={e => setAmount(e.target.value)}
                min={selectedSys.minAmount}
                max={selectedSys.maxAmount}
                step="any"
                className="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                placeholder="0.00"
                required
              />
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

          {selectedSys.type === 'fiat' && (
            <div className="mb-4">
              <p className="text-sm text-gray-500 mb-2">Quick amounts:</p>
              <div className="flex flex-wrap gap-2">
                {QUICK_AMOUNTS.map(amt => (
                  <button
                    key={amt}
                    type="button"
                    onClick={() => setAmount(amt.toString())}
                    className="px-3 py-1 bg-white border rounded-full text-sm hover:border-blue-500 hover:text-blue-600 transition-colors"
                  >
                    {amt} {currency}
                  </button>
                ))}
              </div>
            </div>
          )}

          <div className="text-sm text-gray-500 mb-6 flex gap-4">
            {selectedSys.minAmount && <span>Min: {selectedSys.minAmount} {currency}</span>}
            {selectedSys.maxAmount && <span>Max: {selectedSys.maxAmount} {currency}</span>}
          </div>

          <button
            type="submit"
            disabled={!amount || loading}
            className="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
          >
            {loading ? (
              <div className="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin" />
            ) : null}
            {loading ? 'Processing...' : `Deposit ${amount ? `${amount} ${currency}` : ''}`}
          </button>
        </div>
      )}
    </form>
  );
}
