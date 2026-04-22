export interface Balance {
  currency: string;
  amount: number;
}

export interface PaymentSystem {
  id: string;
  name: string;
  type: 'crypto' | 'fiat';
  logoUrl?: string;
  minAmount?: number;
  maxAmount?: number;
  currencies: string[];
}

export interface DepositRequest {
  paymentSystemId: string;
  amount: number;
  currency: string;
}

export interface PayoutRequest {
  paymentSystemId: string;
  amount: number;
  currency: string;
  accountDetails: string;
}
