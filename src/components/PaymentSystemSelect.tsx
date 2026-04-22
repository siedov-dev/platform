
import { PaymentSystem } from '../types/wallet';
import { CreditCard, Bitcoin, Wallet } from 'lucide-react';
import clsx from 'clsx';

interface Props {
  systems: PaymentSystem[];
  selectedId: string | null;
  onSelect: (id: string) => void;
}

export default function PaymentSystemSelect({ systems, selectedId, onSelect }: Props) {
  const getIcon = (type: string, id: string) => {
    if (type === 'crypto' || id === 'btc') return <Bitcoin className="w-8 h-8" />;
    if (id === 'visa') return <CreditCard className="w-8 h-8" />;
    return <Wallet className="w-8 h-8" />;
  };

  return (
    <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
      {systems.map((sys) => (
        <div
          key={sys.id}
          data-testid={`ps-${sys.id}`}
          onClick={() => onSelect(sys.id)}
          className={clsx(
            'cursor-pointer border rounded-xl p-4 flex flex-col items-center justify-center gap-2 transition-all hover:shadow-md',
            selectedId === sys.id ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-200' : 'border-gray-200 hover:border-blue-300'
          )}
        >
          <div className={selectedId === sys.id ? 'text-blue-600' : 'text-gray-600'}>
            {getIcon(sys.type, sys.id)}
          </div>
          <div className="text-sm font-medium text-center">{sys.name}</div>
          <div className="text-xs text-gray-500">
            {sys.currencies.join(', ')}
          </div>
        </div>
      ))}
    </div>
  );
}
