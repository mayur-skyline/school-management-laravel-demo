import { useEffect } from 'react';

import PSHE, { Footprint } from '../../common/models/PSHE';
import { useErrorModal } from '../../context/modals/ModalProvider';
import { useAsync } from '../useAsync';

const FOOTPRINT = {
  footprints: [],
  videos: [],
};

export function useGetStudentCurriculum(url: string) {
  const {
    loading,
    execute,
    data: stages,
    error,
  } = useAsync<Footprint>({
    asyncFunc: () => PSHE.getCurriculum(url),
    initialData: FOOTPRINT,
    immediate: false,
  });

  useErrorModal(error);

  useEffect(() => {
    execute();
  }, [url]);

  return { loading, stages };
}
