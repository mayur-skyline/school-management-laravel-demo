import { useEffect } from 'react';

import Training, { ResouceData } from '../../common/models/Training';
import { useErrorModal } from '../../context/modals/ModalProvider';
import { useAsync } from '../useAsync';

const Data = {
  send: [],
  mental_health_and_wellbeing: [],
  introducing_steer_to_your_school: [],
  leading_assessments: [],
  inspection: [],
};

export function useGetResouceData() {
  const {
    loading,
    execute,
    data: resource,
    error,
  } = useAsync<ResouceData>({
    asyncFunc: () => Training.getResourceData(),
    initialData: Data,
    immediate: false,
  });

  useErrorModal(error);

  useEffect(() => {
    execute();
  }, []);

  return { loading, resource };
}
