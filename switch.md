# Cashier Module (Wallet & Payments)

## Overview
This module implements the user interface for handling deposits and payouts in a simulated environment, serving as the cashier and wallet page for users. The front-end has been moved to React utilizing modern architecture (Tailwind CSS, Vite).

## Features
- **Wallet Balances:** Displays current balances in multiple currencies (USD, EUR, USDT).
- **Deposit Section:**
  - Select between different payment systems (Fiat like Visa/Mastercard, Skrill and Crypto like Bitcoin, USDT TRC20).
  - Quick amount buttons for fiat deposits.
  - Generates an interactive QR Code and copyable addresses for crypto deposits.
- **Payout Section:**
  - Displays available balances before requesting payouts.
  - "MAX" button to quickly withdraw all available funds.
  - Collects wallet addresses or account details depending on the chosen payment system.
- **Mock API:** Simulates network delays for balance fetching and transaction processing without backend requirements.

## Architecture & Technology
- Built with React (TypeScript) and Vite.
- Styling implemented via Tailwind CSS.
- Component-based architecture for forms and payment system selection.
- Full test coverage for essential flows using Vitest and React Testing Library.
