import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import DepositForm from './DepositForm';
import { walletApi } from '../api/mock';
import { vi } from 'vitest';

vi.mock('../api/mock', () => ({
  walletApi: {
    getPaymentSystems: vi.fn(),
    deposit: vi.fn()
  }
}));

const mockSystems = [
  { id: 'visa', name: 'Visa', type: 'fiat', currencies: ['USD'], minAmount: 10 },
  { id: 'btc', name: 'Bitcoin', type: 'crypto', currencies: ['BTC'] }
];

describe('DepositForm', () => {
  beforeEach(() => {
    vi.mocked(walletApi.getPaymentSystems).mockResolvedValue(mockSystems);
  });

  it('renders payment systems', async () => {
    render(<DepositForm />);

    await waitFor(() => {
      expect(screen.getByText('Visa')).toBeInTheDocument();
      expect(screen.getByText('Bitcoin')).toBeInTheDocument();
    });
  });

  it('submits fiat deposit successfully', async () => {
    vi.mocked(walletApi.deposit).mockResolvedValue({ success: true, transactionId: 'test-123' });

    render(<DepositForm />);

    // Wait for systems to load
    await waitFor(() => screen.getByText('Visa'));

    // Select Visa
    fireEvent.click(screen.getByTestId('ps-visa'));

    // Enter amount
    const input = screen.getByPlaceholderText('0.00');
    fireEvent.change(input, { target: { value: '50' } });

    // Submit
    const button = screen.getByRole('button', { name: /deposit/i });
    fireEvent.click(button);

    // Check API call
    await waitFor(() => {
      expect(walletApi.deposit).toHaveBeenCalledWith({
        paymentSystemId: 'visa',
        amount: 50,
        currency: 'USD'
      });
      expect(screen.getByText(/Transaction ID: test-123/i)).toBeInTheDocument();
    });
  });
});
