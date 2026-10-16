import { Balance, PaymentSystem, DepositRequest, PayoutRequest } from '../types/wallet';

const mockBalances: Balance[] = [
  { currency: 'USD', amount: 1250.50 },
  { currency: 'EUR', amount: 300.00 },
  { currency: 'USDT', amount: 5000.00 },
];

const mockPaymentSystems: PaymentSystem[] = [
  { id: 'visa', name: 'Visa / Mastercard', type: 'fiat', currencies: ['USD', 'EUR'], minAmount: 10, maxAmount: 5000 },
  { id: 'skrill', name: 'Skrill', type: 'fiat', currencies: ['USD', 'EUR'], minAmount: 20 },
  { id: 'usdt_trc20', name: 'Tether (USDT TRC20)', type: 'crypto', currencies: ['USDT'], minAmount: 10 },
  { id: 'btc', name: 'Bitcoin', type: 'crypto', currencies: ['BTC'], minAmount: 0.001 },
];

export const walletApi = {
  getBalance: async (): Promise<Balance[]> => {
    return new Promise((resolve) => setTimeout(() => resolve(mockBalances), 500));
  },

  getPaymentSystems: async (): Promise<PaymentSystem[]> => {
    return new Promise((resolve) => setTimeout(() => resolve(mockPaymentSystems), 500));
  },

  deposit: async (data: DepositRequest): Promise<{ success: boolean; transactionId: string; address?: string }> => {
    return new Promise((resolve) => {
      setTimeout(() => {
        const sys = mockPaymentSystems.find(s => s.id === data.paymentSystemId);
        if (sys?.type === 'crypto') {
          resolve({
            success: true,
            transactionId: Math.random().toString(36).substring(7),
            address: 'T9yD14Nj9j7xAB4dbGeiX9h8unkKAzX3Bq' // Mock TRC20 address
          });
        } else {
          resolve({
            success: true,
            transactionId: Math.random().toString(36).substring(7)
          });
        }
      }, 800);
    });
  },

  payout: async (_data: PayoutRequest): Promise<{ success: boolean; transactionId: string }> => {
    return new Promise((resolve) => {
      setTimeout(() => resolve({
        success: true,
        transactionId: Math.random().toString(36).substring(7)
      }), 800);
    });
  }
};
