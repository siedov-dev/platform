import '@testing-library/jest-dom';
import { vi } from 'vitest';

// Define jest global for compatibility with tests using jest.mock/jest.fn
(globalThis as any).jest = {
  fn: vi.fn,
  mock: vi.mock,
};
