import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import PayoutForm from './PayoutForm';
import { walletApi } from '../api/mock';
import { vi } from 'vitest';

vi.mock('../api/mock', () => ({
  walletApi: {
    getPaymentSystems: vi.fn(),
    payout: vi.fn()
  }
}));

const mockSystems = [
  { id: 'visa', name: 'Visa', type: 'fiat', currencies: ['USD'], minAmount: 10 }
];

const mockBalances = [
  { currency: 'USD', amount: 100 }
];

describe('PayoutForm', () => {
  let alertMock: any;

  beforeEach(() => {
    vi.mocked(walletApi.getPaymentSystems).mockResolvedValue(mockSystems);
    vi.mocked(walletApi.payout).mockReset(); // Important: reset between tests
    alertMock = vi.spyOn(window, 'alert').mockImplementation(() => {});
  });

  afterEach(() => {
    alertMock.mockRestore();
  });

  it('submits payout request successfully', async () => {
    vi.mocked(walletApi.payout).mockResolvedValue({ success: true, transactionId: 'test-payout-123' });

    render(<PayoutForm balances={mockBalances} />);

    await waitFor(() => screen.getByText('Visa'));

    // Select Visa
    fireEvent.click(screen.getByTestId('ps-visa'));

    // Fill out form
    const amountInput = screen.getByPlaceholderText('0.00');
    fireEvent.change(amountInput, { target: { value: '50' } });

    const detailsInput = screen.getByPlaceholderText(/Enter account details/i);
    fireEvent.change(detailsInput, { target: { value: '1234567890' } });

    // Submit
    const button = screen.getByRole('button', { name: /request payout/i });
    fireEvent.click(button);

    await waitFor(() => {
      expect(walletApi.payout).toHaveBeenCalledWith({
        paymentSystemId: 'visa',
        amount: 50,
        currency: 'USD',
        accountDetails: '1234567890'
      });
      expect(screen.getByText(/Transaction ID: test-payout-123/i)).toBeInTheDocument();
    });
  });

  it('prevents withdrawal greater than balance', async () => {
    render(<PayoutForm balances={mockBalances} />);

    await waitFor(() => screen.getByText('Visa'));

    fireEvent.click(screen.getByTestId('ps-visa'));

    // Enter amount > 100
    const amountInput = screen.getByPlaceholderText('0.00');
    fireEvent.change(amountInput, { target: { value: '150' } });

    const detailsInput = screen.getByPlaceholderText(/Enter account details/i);
    fireEvent.change(detailsInput, { target: { value: '1234' } });

    const button = screen.getByRole('button', { name: /request payout/i });
    expect(button).toBeDisabled();

    expect(walletApi.payout).not.toHaveBeenCalled();
  });
});
