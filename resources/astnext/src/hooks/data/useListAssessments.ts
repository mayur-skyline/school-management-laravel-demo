import { useEffect } from 'react';

import { Pagination } from '../../@types';
import Assessment from '../../common/models/Assessment';
import { useErrorModal } from '../../context/modals/ModalProvider';
import { SorterMenu } from '../../views/app-dashboard/assessment-tracker/components/assessment-table/sorter-menu/SorterMenu';
import { TrackerPage } from '../../views/app-dashboard/assessment-tracker/components/progress-tracker-nav/ProgressTrackerNav';
import { useAsync } from '../useAsync';
import { useStore } from '../useStore';

export type PageMeta = {
  size: number;
  page: number;
  keyword?: string;
};

export function useListAssessments(type: TrackerPage | SorterMenu['value'], pageMeta: PageMeta) {
  const [{ currentFilters }] = useStore();

  const ingredients = Object.keys(pageMeta)
    .map((key) => pageMeta[key])
    .join('-');

  const {
    loading,
    execute,
    data: { assessments, pagination },
    error,
  } = useAsync<{ assessments: Assessment[]; pagination: Partial<Pagination> }>({
    asyncFunc: () =>
      Assessment.list(type, {
        ...currentFilters,
        ...pageMeta,
      }),
    initialData: {
      assessments: [],
      pagination: {},
    },
    immediate: false,
  });

  function reload() {
    execute();
  }

  useErrorModal(error);

  useEffect(() => {
    if (type === 'search' && !pageMeta.keyword.length) {
      return;
    }
    execute();
  }, [currentFilters, type, ingredients]);

  return { loading, assessments, pagination, reload };
}
