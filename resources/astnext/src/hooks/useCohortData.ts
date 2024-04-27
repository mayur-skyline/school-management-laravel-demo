import { useContext } from 'react';

import { CohortDataContext } from '../context/CohortDataProvider';

export function useCohortData() {
  const context = useContext(CohortDataContext);
  if (!context) {
    throw new Error('useCohortData must be used within a StoreProvider');
  }
  return context;
}
