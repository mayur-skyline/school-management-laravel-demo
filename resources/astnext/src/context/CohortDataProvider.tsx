import React, { createContext, Dispatch, Reducer, useReducer } from 'react';

import { Action, PageParams } from '../@types';
import { SortParams } from '../common/models/CohortData';
import { PaginatedCohortDataV2 } from '../common/models/CohortDataV2';
import { FilterParams } from '../common/models/Filter';

export enum COHORT_DATA_ACTIONS {
  setFilters = 'SET_FILTERS',
  setCohortData = 'SET_COHORT_DATA',
}

export type CohortDataContext = {
  filters: FilterParams & PageParams & Partial<SortParams>;
  cohortData: PaginatedCohortDataV2;
};

const INITIAL_CONTEXT: CohortDataContext = {
  filters: {},
  cohortData: {
    data: [],
    pagination: {},
  },
};

export const CohortDataContext = createContext<
  [CohortDataContext, Dispatch<Action<COHORT_DATA_ACTIONS>>] | null
>(null);
CohortDataContext.displayName = 'StoreContext';

export const CohortDataCacheReducer = (
  state: CohortDataContext,
  { type, payload }: Action<COHORT_DATA_ACTIONS>
): CohortDataContext => {
  switch (type) {
    case COHORT_DATA_ACTIONS.setFilters: {
      return {
        ...state,
        filters: payload,
      };
    }
    case COHORT_DATA_ACTIONS.setCohortData: {
      return {
        ...state,
        cohortData: payload,
      };
    }
    default:
      throw new Error('Invalid store action ');
  }
};

const CohortDataProvider: React.FC<React.HTMLAttributes<HTMLElement>> = (props) => {
  const [state, dispatch] = useReducer<Reducer<CohortDataContext, Action<COHORT_DATA_ACTIONS>>>(
    CohortDataCacheReducer,
    INITIAL_CONTEXT
  );
  return <CohortDataContext.Provider value={[state, dispatch]} {...props} />;
};

export default CohortDataProvider;
